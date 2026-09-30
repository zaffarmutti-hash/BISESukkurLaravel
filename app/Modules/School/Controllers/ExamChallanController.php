<?php

namespace App\Modules\School\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesSchoolScope;
use App\Models\Invoice;
use App\Models\InvoiceStudent;
use App\Models\ExamForm;
use App\Models\AcademicYear;
use App\Models\School;
use App\Services\InvoiceNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamChallanController extends Controller
{
    use ResolvesSchoolScope;

    private InvoiceNumberService $invoiceNumberService;

    public function __construct()
    {
        $this->invoiceNumberService = new InvoiceNumberService();
    }

    public function index(Request $request): Response
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();

        $query = Invoice::where('school_id', $schoolId)->where('invoice_type', 'examination');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        $invoices->through(function ($invoice) {
            return [
                'id'             => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount'         => $invoice->total_amount_paisas / 100,
                'status'         => $invoice->status,
                'created_at'     => $invoice->created_at->toIso8601String(),
                'challan_id'     => $invoice->id,
                'fee_phase'      => $invoice->fee_phase,
                'late_fee_surcharge_paisas' => $invoice->late_fee_surcharge_paisas,
                'challan'        => [
                    'id'             => $invoice->id,
                    'challan_number' => $invoice->invoice_number,
                    'type'           => $invoice->invoice_type,
                    'total_students' => $invoice->student_count,
                    'amount'         => $invoice->total_amount_paisas / 100,
                    'status'         => $invoice->status,
                    'fee_phase'      => $invoice->fee_phase,
                    'late_fee_surcharge_paisas' => $invoice->late_fee_surcharge_paisas,
                ]
            ];
        });

        return redirect()->route('school.invoices', array_merge(['type' => 'examination'], $request->all()));
    }

    public function create()
    {
        return redirect()->route('school.invoices', ['type' => 'examination']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_level'     => 'required|string',
            'group'           => 'required|string',
            'student_type'    => 'required|string',
            'student_ids'     => 'required|array',
        ]);

        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();

        if (! $activeYear) {
            return response()->json(['message' => 'No active academic year configured.'], 422);
        }

        $school = School::findOrFail($schoolId);
        $boardPolicyService = new \App\Services\BoardPolicyService();

        // Read the phase injected by the ExaminationWindowOpen middleware
        $phase = $request->attributes->get('examination_phase', 'normal');
        $isGracePhase = ($phase === 'grace');

        try {
            $invoice = DB::transaction(function () use ($request, $school, $activeYear, $boardPolicyService, $isGracePhase) {
                $studentIds = $request->input('student_ids');

                // Get matching student academic records that have final exam forms and no existing exam challans
                $classLevel = $request->input('class_level');
                $group = $request->input('group');

                // Determine internal class level if matric/intermediate labels are used
                $levels = [];
                if ($classLevel === 'matric') {
                    $levels = ['ssc_part1', 'ssc_part2'];
                } elseif ($classLevel === 'intermediate') {
                    $levels = ['hsc_part1', 'hsc_part2'];
                } else {
                    $levels = [$classLevel];
                }

                $examForms = ExamForm::query()
                    ->where('academic_year_id', $activeYear->id)
                    ->where('status', 'final')
                    ->whereIn('student_id', $studentIds)
                    ->whereHas('student', fn ($q) => $q->where('school_id', $school->id))
                    ->whereHas('studentAcademicRecord', fn ($q) => 
                        $q->whereIn('class_level', $levels)->where('subject_group', $group)
                    )
                    ->get();

                if ($examForms->isEmpty()) {
                    throw new \Exception('No eligible candidates found with finalized exam forms for the selected criteria.');
                }

                // Resolve fees and construct records
                $invoiceStudentsData = [];
                $totalAmountPaisas = 0;
                $totalSurchargePaisas = 0;

                foreach ($examForms as $examForm) {
                    $record = $examForm->studentAcademicRecord;
                    $feeStructure = $boardPolicyService->resolveFee(
                        $record->class_level,
                        $record->student_type,
                        'examination',
                        $activeYear->id
                    );

                    $basePaisas = $feeStructure->amount_paisas;
                    $surchargePaisas = $isGracePhase ? ($feeStructure->late_fee_surcharge_paisas ?? 0) : 0;
                    $amountPaisas = $basePaisas + $surchargePaisas;

                    $totalAmountPaisas += $amountPaisas;
                    $totalSurchargePaisas += $surchargePaisas;

                    $invoiceStudentsData[] = [
                        'exam_form'                  => $examForm,
                        'student_id'                  => $examForm->student_id,
                        'student_academic_record_id'  => $examForm->student_academic_record_id,
                        'fee_structure_id'           => $feeStructure->id,
                        'amount_paisas'               => $amountPaisas,
                        'late_fee_surcharge_paisas'   => $surchargePaisas,
                    ];
                }

                // Generate invoice number sequentially under lock
                $invoiceNumber = $this->invoiceNumberService->generateInvoiceNumber($school);

                // Create the invoice with phase tagging
                $invoice = Invoice::create([
                    'school_id'                 => $school->id,
                    'academic_year_id'          => $activeYear->id,
                    'invoice_number'            => $invoiceNumber,
                    'invoice_type'              => 'examination',
                    'student_count'             => $examForms->count(),
                    'total_amount_paisas'       => $totalAmountPaisas,
                    'late_fee_surcharge_paisas' => $totalSurchargePaisas,
                    'fee_phase'                 => $isGracePhase ? 'grace' : 'normal',
                    'status'                    => 'draft',
                ]);

                // Attach students and transition their ExamForms to submitted status
                foreach ($invoiceStudentsData as $data) {
                    InvoiceStudent::create([
                        'invoice_id'                  => $invoice->id,
                        'student_id'                  => $data['student_id'],
                        'student_academic_record_id'  => $data['student_academic_record_id'],
                        'fee_structure_id'           => $data['fee_structure_id'],
                        'amount_paisas'               => $data['amount_paisas'],
                        'late_fee_surcharge_paisas'   => $data['late_fee_surcharge_paisas'],
                    ]);

                    // Update ExamForm status
                    $data['exam_form']->update(['status' => 'submitted', 'submitted_at' => now()]);
                }

                return $invoice;
            });

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'    => true,
                    'id'         => $invoice->id,
                    'challan_no' => $invoice->invoice_number,
                    'fee_phase'  => $invoice->fee_phase,
                ]);
            }

            $message = 'Examination challan generated successfully.';
            if ($invoice->fee_phase === 'grace') {
                $message .= ' Late fee surcharge has been applied (grace period).';
            }

            return redirect()->route('school.enrollment.challans')->with('success', $message);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
