<?php

namespace App\Services;

use App\Contracts\IChallanService;
use App\Models\AcademicYear;
use App\Models\ExamForm;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\InvoiceStudent;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentAcademicRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChallanService implements IChallanService
{
    public function __construct(
        private InvoiceNumberService $invoiceNumberService,
        private WindowPhaseService $windowPhaseService,
        private PdfService $pdfService
    ) {}

    /**
     * Get distinct classes that have eligible students for the specified challan type and school.
     */
    public function getEligibleClasses(string $challanType, int $schoolId, int $activeYearId): array
    {
        $school = School::findOrFail($schoolId);
        $allowedLevels = $school->getAllowedLevelsList();

        $eligibleClasses = [];
        foreach ($allowedLevels as $level) {
            $hasStudents = $this->buildEligibilityQuery($challanType, $schoolId, $activeYearId, $level)->exists();
            if ($hasStudents) {
                $eligibleClasses[] = $level;
            }
        }

        return $eligibleClasses;
    }

    /**
     * Get distinct groups that have eligible students for the specified class.
     */
    public function getEligibleGroups(string $challanType, int $schoolId, int $activeYearId, string $classLevel): array
    {
        return $this->buildEligibilityQuery($challanType, $schoolId, $activeYearId, $classLevel)
            ->distinct()
            ->pluck('student_academic_records.subject_group')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get distinct student types that have eligible students for the specified class and group.
     */
    public function getEligibleStudentTypes(string $challanType, int $schoolId, int $activeYearId, string $classLevel, string $group): array
    {
        return $this->buildEligibilityQuery($challanType, $schoolId, $activeYearId, $classLevel, $group)
            ->distinct()
            ->pluck('student_academic_records.student_type')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Execute full eligibility query with exclusion subquery and previously-excluded flags.
     */
    public function getEligibleStudents(
        string $challanType,
        int $schoolId,
        int $activeYearId,
        string $classLevel,
        string $group,
        string $studentType
    ): Collection {
        $query = $this->buildEligibilityQuery($challanType, $schoolId, $activeYearId, $classLevel, $group, $studentType);

        $records = $query->with(['student'])->get();

        // Check for previously excluded students
        $type = strtolower($challanType);
        $studentIds = $records->pluck('student_id')->toArray();

        $previouslyExcludedStudentIds = InvoiceStudent::query()
            ->whereIn('student_id', $studentIds)
            ->where('is_included', false)
            ->whereHas('invoice', function ($iq) use ($type, $activeYearId, $classLevel, $group, $studentType) {
                $iq->where('invoice_type', $type)
                    ->where('academic_year_id', $activeYearId)
                    ->where('class_level', $classLevel)
                    ->where('subject_group', $group)
                    ->where('student_type', $studentType)
                    ->where('status', '!=', 'rejected');
            })
            ->pluck('student_id')
            ->flip()
            ->toArray();

        return $records->map(function ($record) use ($previouslyExcludedStudentIds) {
            $student = $record->student;

            return [
                'record_id'           => $record->id,
                'student_id'          => $student->id,
                'full_name'           => $student->full_name,
                'father_name'         => $student->father_name,
                'surname'             => $student->surname,
                'cnic'                => $student->cnic ?? $student->b_form ?? '—',
                'enrollment_number'   => $student->enrollment_number,
                'class_level'         => $record->class_level,
                'subject_group'       => $record->subject_group,
                'student_type'        => $record->student_type,
                'previously_excluded' => isset($previouslyExcludedStudentIds[$student->id]),
            ];
        })->sortBy('full_name')->values();
    }

    /**
     * Atomically generate an invoice/challan within a database transaction.
     */
    public function generateChallan(
        string $challanType,
        int $schoolId,
        int $activeYearId,
        string $classLevel,
        string $group,
        string $studentType,
        float $feePerStudent,
        array $studentSelections,
        ?string $phase = null
    ): Invoice {
        // Validate inputs
        $normalizedType = strtolower($challanType);
        if (!in_array($normalizedType, ['enrollment', 'examination'])) {
            throw new \InvalidArgumentException("Invalid challan type: {$challanType}. Must be Enrollment or Examination.");
        }

        if (empty($studentSelections)) {
            throw new \InvalidArgumentException("Students list must contain at least one item.");
        }

        $includedSelections = array_filter($studentSelections, fn ($s) => !empty($s['is_included']));
        if (empty($includedSelections)) {
            throw new \InvalidArgumentException("You must include at least one student in the challan.");
        }

        // Re-read active year and school fresh from database
        $activeYear = AcademicYear::current();
        if (!$activeYear || $activeYear->id !== $activeYearId) {
            throw new \RuntimeException("The academic session is inactive or changed. Please refresh and try again.");
        }

        $school = School::with('district')->findOrFail($schoolId);

        // Evaluate window phase
        if (!$phase) {
            $phaseResult = $this->windowPhaseService->resolvePhase($schoolId, $normalizedType);
            if (!$phaseResult->isAccessAllowed) {
                throw new \RuntimeException("Cannot generate challan: The {$normalizedType} window is currently closed.");
            }
            $phase = $phaseResult->phase;
        }
        $isGracePhase = ($phase === 'grace');

        // Resolve active fee structure
        $feeStructure = FeeStructure::query()
            ->where('academic_year_id', $activeYearId)
            ->where('class_level', $classLevel)
            ->where('student_type', $studentType)
            ->where('fee_type', $normalizedType)
            ->where('is_active', true)
            ->first();

        $basePaisas = $feeStructure ? $feeStructure->amount_paisas : (int) round($feePerStudent * 100);
        $surchargePaisas = ($isGracePhase && $feeStructure) ? ($feeStructure->late_fee_surcharge_paisas ?? 0) : 0;
        $totalPerStudentPaisas = $basePaisas + $surchargePaisas;

        return DB::transaction(function () use (
            $normalizedType, $school, $activeYear, $classLevel, $group, $studentType,
            $studentSelections, $includedSelections, $basePaisas, $surchargePaisas,
            $totalPerStudentPaisas, $phase, $isGracePhase
        ) {
            // Re-validate included students' eligibility to avoid concurrency race conditions
            $includedStudentIds = array_column($includedSelections, 'id');
            $eligibleRecordIds = $this->buildEligibilityQuery($normalizedType, $school->id, $activeYear->id, $classLevel, $group, $studentType)
                ->whereIn('student_academic_records.student_id', $includedStudentIds)
                ->pluck('student_academic_records.student_id')
                ->toArray();

            $diff = array_diff($includedStudentIds, $eligibleRecordIds);
            if (!empty($diff)) {
                $conflictStudent = Student::find(reset($diff));
                $name = $conflictStudent ? $conflictStudent->full_name : "ID #" . reset($diff);
                throw new \RuntimeException("Student '{$name}' is no longer eligible (they may already have been included in another active challan). Please refresh the list.");
            }

            // Step 3: Generate sequential invoice number under SELECT FOR UPDATE lock
            $invoiceNumber = $this->invoiceNumberService->generateInvoiceNumber($school);

            $includedCount = count($includedSelections);
            $totalAmountPaisas = $includedCount * $totalPerStudentPaisas;
            $totalSurchargePaisas = $includedCount * $surchargePaisas;

            // Step 4: Create Invoice record
            $invoice = Invoice::create([
                'school_id'                 => $school->id,
                'academic_year_id'          => $activeYear->id,
                'invoice_number'            => $invoiceNumber,
                'invoice_type'              => $normalizedType,
                'class_level'               => $classLevel,
                'subject_group'             => $group,
                'student_type'              => $studentType,
                'student_count'             => $includedCount,
                'total_amount_paisas'       => $totalAmountPaisas,
                'late_fee_surcharge_paisas' => $totalSurchargePaisas,
                'fee_phase'                 => $phase,
                'status'                    => 'submitted', // School generated and submitted for payment
                'submitted_at'              => now(),
            ]);

            // Step 5: Create InvoiceItem / InvoiceStudent for EVERY student (both included & excluded)
            foreach ($studentSelections as $selection) {
                $studentId = $selection['student_id'] ?? $selection['id'] ?? null;
                if (!$studentId) {
                    continue;
                }
                $isIncluded = !empty($selection['is_included']);

                $record = StudentAcademicRecord::where('student_id', $studentId)
                    ->where('academic_year_id', $activeYear->id)
                    ->first();

                if (!$record) {
                    continue;
                }

                InvoiceStudent::create([
                    'invoice_id'                 => $invoice->id,
                    'student_id'                 => $studentId,
                    'student_academic_record_id' => $record->id,
                    'fee_structure_id'           => $feeStructure?->id, // Actual resolved fee structure
                    'amount_paisas'              => $isIncluded ? $totalPerStudentPaisas : 0,
                    'late_fee_surcharge_paisas'  => $isIncluded ? $surchargePaisas : 0,
                    'is_included'                => $isIncluded,
                ]);

                // Transition status for included students
                if ($isIncluded) {
                    if ($normalizedType === 'enrollment') {
                        $record->update(['status' => StudentAcademicRecord::STATUS_PENDING_CHALLAN]);
                    } elseif ($normalizedType === 'examination') {
                        ExamForm::where('student_academic_record_id', $record->id)
                            ->where('academic_year_id', $activeYear->id)
                            ->update(['status' => 'submitted', 'submitted_at' => now()]);
                    }
                }
            }

            // Step 6: Generate PDFs (non-fatal if PDF engine encounters minor rendering issue)
            try {
                $challanPdf = $this->pdfService->generateChallanPdf($invoice);
                $listPdf = $this->pdfService->generateStudentListPdf($invoice);

                $invoice->update([
                    'challan_pdf_path'      => $challanPdf,
                    'student_list_pdf_path' => $listPdf,
                ]);
            } catch (\Throwable $pdfError) {
                Log::warning("PDF generation on challan creation skipped/failed: " . $pdfError->getMessage(), [
                    'invoice_id' => $invoice->id,
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Build the foundational eligibility query according to Part One rules.
     */
    protected function buildEligibilityQuery(
        string $challanType,
        int $schoolId,
        int $activeYearId,
        ?string $classLevel = null,
        ?string $group = null,
        ?string $studentType = null
    ) {
        $type = strtolower($challanType);

        $query = StudentAcademicRecord::query()
            ->join('students', 'students.id', '=', 'student_academic_records.student_id')
            ->where('students.school_id', $schoolId)
            ->where('students.is_active', true)
            ->whereNull('students.deleted_at')
            ->where('student_academic_records.academic_year_id', $activeYearId)
            ->select('student_academic_records.*');

        if ($classLevel) {
            $query->where('student_academic_records.class_level', $classLevel);
        }

        if ($group) {
            $query->where('student_academic_records.subject_group', $group);
        }

        if ($studentType) {
            $query->where('student_academic_records.student_type', $studentType);
        }

        if ($type === 'enrollment') {
            // Condition 3: Status is exactly final
            // Condition 7: Student does NOT already exist in any InvoiceStudent row where is_included = true
            // in a non-rejected enrollment invoice for the same year, class, group, and student type.
            $query->where(function ($q) {
                $q->where('student_academic_records.status', StudentAcademicRecord::STATUS_FINAL)
                  ->orWhere('student_academic_records.status', 'final');
            });

            $query->whereNotExists(function ($sub) use ($type, $activeYearId) {
                $sub->select(DB::raw(1))
                    ->from('invoice_students')
                    ->join('invoices', 'invoices.id', '=', 'invoice_students.invoice_id')
                    ->whereColumn('invoice_students.student_id', 'student_academic_records.student_id')
                    ->where('invoice_students.is_included', true)
                    ->where('invoices.invoice_type', 'enrollment')
                    ->where('invoices.academic_year_id', $activeYearId)
                    ->whereColumn('invoices.class_level', 'student_academic_records.class_level')
                    ->whereColumn('invoices.subject_group', 'student_academic_records.subject_group')
                    ->whereColumn('invoices.student_type', 'student_academic_records.student_type')
                    ->where('invoices.status', '!=', 'rejected');
            });

        } elseif ($type === 'examination') {
            // Additional Condition 1: enrollment_number is NOT null
            $query->whereNotNull('students.enrollment_number');

            // Additional Condition 2: ExamForm exists with status = 'final'
            $query->whereHas('examForms', function ($ef) use ($activeYearId) {
                $ef->where('academic_year_id', $activeYearId)
                   ->where('status', 'final');
            });

            // Exclusion check for examination
            $query->whereNotExists(function ($sub) use ($type, $activeYearId) {
                $sub->select(DB::raw(1))
                    ->from('invoice_students')
                    ->join('invoices', 'invoices.id', '=', 'invoice_students.invoice_id')
                    ->whereColumn('invoice_students.student_id', 'student_academic_records.student_id')
                    ->where('invoice_students.is_included', true)
                    ->where('invoices.invoice_type', 'examination')
                    ->where('invoices.academic_year_id', $activeYearId)
                    ->whereColumn('invoices.class_level', 'student_academic_records.class_level')
                    ->whereColumn('invoices.subject_group', 'student_academic_records.subject_group')
                    ->whereColumn('invoices.student_type', 'student_academic_records.student_type')
                    ->where('invoices.status', '!=', 'rejected');
            });
        }

        return $query;
    }
}
