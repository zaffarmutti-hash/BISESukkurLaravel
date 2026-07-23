<?php

namespace App\Modules\School\Services;

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\InvoiceStudent;
use App\Models\StudentAcademicRecord;
use App\Models\School;
use App\Services\ChallanService;
use App\Services\InvoiceNumberService;
use App\Services\BoardPolicyService;
use Illuminate\Support\Facades\DB;

class ChallanGenerationService
{
    private ChallanService $challanService;
    private InvoiceNumberService $invoiceNumberService;
    private BoardPolicyService $boardPolicyService;

    public function __construct(ChallanService $challanService)
    {
        $this->challanService = $challanService;
        $this->invoiceNumberService = new InvoiceNumberService();
        $this->boardPolicyService = new BoardPolicyService();
    }

    /**
     * Generate a new enrollment invoice for a school.
     *
     * Only students with status='final' are eligible (enforced by ChallanService).
     * Transitions included students to 'pending_challan' status.
     *
     * When the enrollment window is in the grace phase, the late fee surcharge
     * is automatically applied on top of the standard fee, and the invoice is
     * permanently tagged as a grace-phase invoice for audit purposes.
     */
    public function generate(
        int $schoolId,
        string $classLevel,
        string $group,
        string $studentType,
        int $feePerStudent = 0, // Ignored, backend resolves dynamically
        array $studentIds = [],
        ?string $phase = null   // Injected from middleware, null defaults to 'normal'
    ): Invoice {
        $activeYear = AcademicYear::current();

        if (! $activeYear) {
            throw new \RuntimeException('No active academic year configured.');
        }

        $school = School::findOrFail($schoolId);

        // Resolve the current phase if not explicitly provided
        if ($phase === null) {
            $phaseResult = $this->boardPolicyService->enrollmentPhaseForSchool($schoolId);
            $phase = $phaseResult->phase;
        }

        $isGracePhase = ($phase === 'grace');

        // Get eligible students (already filtered to status='final')
        $eligibleRecords = $this->challanService->getEligibleStudents(
            $schoolId,
            $activeYear->id,
            $classLevel,
            $group,
        );

        // If specific student IDs were provided, further filter
        if (! empty($studentIds)) {
            $eligibleRecords = $eligibleRecords->filter(
                fn ($record) => in_array($record->student_id, $studentIds)
            );
        }

        if ($eligibleRecords->isEmpty()) {
            throw new \RuntimeException('No eligible students found for challan generation.');
        }

        return DB::transaction(function () use ($eligibleRecords, $school, $activeYear, $isGracePhase) {
            
            // Generate invoice number sequentially under a transaction lock
            $invoiceNumber = $this->invoiceNumberService->generateInvoiceNumber($school);

            // Resolve dynamic board fees for each student and accumulate
            $invoiceStudentsData = [];
            $totalPaisas = 0;
            $totalSurchargePaisas = 0;

            foreach ($eligibleRecords as $record) {
                $feeStructure = $this->boardPolicyService->resolveFee(
                    $record->class_level,
                    $record->student_type,
                    'enrollment',
                    $activeYear->id
                );

                $basePaisas = $feeStructure->amount_paisas;
                $surchargePaisas = $isGracePhase ? ($feeStructure->late_fee_surcharge_paisas ?? 0) : 0;
                $amountPaisas = $basePaisas + $surchargePaisas;

                $totalPaisas += $amountPaisas;
                $totalSurchargePaisas += $surchargePaisas;

                $invoiceStudentsData[] = [
                    'student_id'                 => $record->student_id,
                    'student_academic_record_id' => $record->id,
                    'fee_structure_id'           => $feeStructure->id,
                    'amount_paisas'              => $amountPaisas,
                    'late_fee_surcharge_paisas'  => $surchargePaisas,
                ];
            }

            // Create invoice record with phase tagging
            $invoice = Invoice::create([
                'school_id'                 => $school->id,
                'academic_year_id'          => $activeYear->id,
                'invoice_number'            => $invoiceNumber,
                'invoice_type'              => 'enrollment',
                'student_count'             => $eligibleRecords->count(),
                'total_amount_paisas'       => $totalPaisas,
                'late_fee_surcharge_paisas' => $totalSurchargePaisas,
                'fee_phase'                 => $isGracePhase ? 'grace' : 'normal',
                'status'                    => 'draft',
            ]);

            // Attach students to invoice
            foreach ($invoiceStudentsData as $item) {
                InvoiceStudent::create(array_merge($item, [
                    'invoice_id' => $invoice->id,
                ]));
            }

            // Transition student status from 'final' → 'pending_challan'
            foreach ($eligibleRecords as $record) {
                $record->update(['status' => StudentAcademicRecord::STATUS_PENDING_CHALLAN]);
            }

            return $invoice;
        });
    }
}
