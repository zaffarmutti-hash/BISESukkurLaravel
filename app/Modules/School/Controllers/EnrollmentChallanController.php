<?php

namespace App\Modules\School\Controllers;

use App\Contracts\IChallanService;
use App\Contracts\IPdfService;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesSchoolScope;
use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\School;
use App\Services\WindowPhaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollmentChallanController extends Controller
{
    use ResolvesSchoolScope;

    public function __construct(
        private IChallanService $challanService,
        private IPdfService $pdfService,
        private WindowPhaseService $windowPhaseService
    ) {}

    /**
     * Display the school admin invoice list with KPIs, filters, and paginated table.
     */
    public function index(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();
        $yearId = $activeYear?->id;

        $school = School::with('district')->findOrFail($schoolId);

        // Compute KPIs scoped to active academic year and authenticated school
        $kpiBaseQuery = Invoice::where('school_id', $schoolId);
        if ($yearId) {
            $kpiBaseQuery->where('academic_year_id', $yearId);
        }

        $totalInvoices = (clone $kpiBaseQuery)->count();
        $pendingCount = (clone $kpiBaseQuery)->whereIn('status', ['submitted', 'pending', 'draft'])->count();
        $verifiedCount = (clone $kpiBaseQuery)->whereIn('status', ['confirmed', 'verified'])->count();
        $totalCollectedPaisas = (clone $kpiBaseQuery)->whereIn('status', ['confirmed', 'verified'])->sum('total_amount_paisas');
        $totalBilledPaisas = (clone $kpiBaseQuery)->sum('total_amount_paisas');

        $kpis = [
            'total_invoices'   => $totalInvoices,
            'pending_payment'  => $pendingCount,
            'verified'         => $verifiedCount,
            'total_collected'  => $totalCollectedPaisas / 100,
            'total_billed'     => $totalBilledPaisas / 100,
        ];

        // Build invoice table query
        $query = Invoice::where('school_id', $schoolId)->with(['school.district', 'academicYear']);

        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        }

        // Filter: Type
        if ($request->filled('type') && in_array(strtolower($request->type), ['enrollment', 'examination'])) {
            $query->where('invoice_type', strtolower($request->type));
        }

        // Filter: Status
        if ($request->filled('status')) {
            $status = strtolower($request->status);
            if ($status === 'pending') {
                $query->whereIn('status', ['submitted', 'pending', 'draft']);
            } elseif ($status === 'verified') {
                $query->whereIn('status', ['confirmed', 'verified']);
            } else {
                $query->where('status', $status);
            }
        }

        // Filter: Class
        if ($request->filled('class_level')) {
            $query->where('class_level', $request->class_level);
        }

        // Filter: Search by invoice number
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('bank_reference', 'like', "%{$search}%");
            });
        }

        // Filter: Date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->latest('id')->paginate(15)->withQueryString();

        // Allowed levels for filter
        $allowedLevels = $school->getAllowedLevelsList();

        // Check window phases for quick badge
        $enrollmentPhase = $this->windowPhaseService->resolvePhase($schoolId, 'enrollment');
        $examinationPhase = $this->windowPhaseService->resolvePhase($schoolId, 'examination');

        return view('school.challans.index', [
            'invoices'          => $invoices,
            'kpis'              => $kpis,
            'activeYear'        => $activeYear,
            'school'            => $school,
            'allowedLevels'     => $allowedLevels,
            'enrollmentPhase'   => $enrollmentPhase,
            'examinationPhase'  => $examinationPhase,
            'filters'           => $request->only(['type', 'status', 'class_level', 'search', 'date_from', 'date_to']),
        ]);
    }

    /**
     * AJAX: Get eligible classes for challan generation dialog.
     */
    public function getEligibleClasses(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();
        if (!$activeYear) {
            return response()->json(['classes' => [], 'error' => 'No active academic year configured.'], 400);
        }

        $type = $request->get('type', 'Enrollment');
        $classes = $this->challanService->getEligibleClasses($type, $schoolId, $activeYear->id);

        return response()->json([
            'classes' => $classes,
        ]);
    }

    /**
     * AJAX: Get eligible groups for selected class.
     */
    public function getEligibleGroups(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();
        $type = $request->get('type', 'Enrollment');
        $classLevel = $request->get('class_level');

        if (!$activeYear || !$classLevel) {
            return response()->json(['groups' => []]);
        }

        $groups = $this->challanService->getEligibleGroups($type, $schoolId, $activeYear->id, $classLevel);

        return response()->json([
            'groups' => $groups,
        ]);
    }

    /**
     * AJAX: Get eligible student types for selected class and group.
     */
    public function getEligibleStudentTypes(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();
        $type = $request->get('type', 'Enrollment');
        $classLevel = $request->get('class_level');
        $group = $request->get('group');

        if (!$activeYear || !$classLevel || !$group) {
            return response()->json(['student_types' => []]);
        }

        $studentTypes = $this->challanService->getEligibleStudentTypes($type, $schoolId, $activeYear->id, $classLevel, $group);

        return response()->json([
            'student_types' => $studentTypes,
        ]);
    }

    /**
     * AJAX: Get applicable fee rate and window phase details.
     */
    public function getFeeRate(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();
        $type = strtolower($request->get('type', 'enrollment'));
        $classLevel = $request->get('class_level');
        $studentType = $request->get('student_type', 'fresh');

        if (!$activeYear || !$classLevel) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        $phaseResult = $this->windowPhaseService->resolvePhase($schoolId, $type);

        $feeStructure = FeeStructure::query()
            ->where('academic_year_id', $activeYear->id)
            ->where('class_level', $classLevel)
            ->where('student_type', $studentType)
            ->where('fee_type', $type)
            ->where('is_active', true)
            ->first();

        if (!$feeStructure) {
            return response()->json([
                'configured'    => false,
                'message'       => 'Fee not configured for this combination. Contact Super Admin before generating challan.',
                'phase'         => $phaseResult->phase,
                'is_allowed'    => $phaseResult->isAccessAllowed,
            ]);
        }

        $baseFee = $feeStructure->amount_paisas / 100;
        $lateSurcharge = ($feeStructure->late_fee_surcharge_paisas ?? 0) / 100;
        $isGrace = ($phaseResult->phase === 'grace');
        $applicableFee = $isGrace ? ($baseFee + $lateSurcharge) : $baseFee;

        return response()->json([
            'configured'         => true,
            'base_fee'           => $baseFee,
            'late_surcharge'     => $lateSurcharge,
            'applicable_fee'     => $applicableFee,
            'phase'              => $phaseResult->phase,
            'is_allowed'         => $phaseResult->isAccessAllowed,
            'next_label'         => $phaseResult->nextTransitionLabel,
            'normal_end'         => $phaseResult->normalEnd?->format('d-M-Y'),
            'grace_end'          => $phaseResult->graceEnd?->format('d-M-Y'),
        ]);
    }

    /**
     * AJAX: Fetch eligible students matching selected parameters.
     */
    public function getEligibleStudents(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();
        if (!$activeYear) {
            return response()->json(['error' => 'No active academic year configured.'], 400);
        }

        $validated = $request->validate([
            'type'         => 'required|string|in:Enrollment,Examination,enrollment,examination',
            'class_level'  => 'required|string',
            'group'        => 'required|string',
            'student_type' => 'required|string',
        ]);

        $students = $this->challanService->getEligibleStudents(
            $validated['type'],
            $schoolId,
            $activeYear->id,
            $validated['class_level'],
            $validated['group'],
            $validated['student_type']
        );

        return response()->json([
            'students' => $students,
            'count'    => $students->count(),
        ]);
    }

    /**
     * AJAX: Atomically generate challan.
     */
    public function generate(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();
        if (!$activeYear) {
            return response()->json(['error' => 'No active academic year configured.'], 400);
        }

        $validated = $request->validate([
            'type'               => 'required|string|in:Enrollment,Examination,enrollment,examination',
            'class_level'        => 'required|string',
            'group'              => 'required|string',
            'student_type'       => 'required|string',
            'fee_per_student'    => 'required|numeric|min:1',
            'student_selections' => 'required|array|min:1',
            'student_selections.*.id' => 'required|integer',
            'student_selections.*.is_included' => 'required|boolean',
        ]);

        try {
            $invoice = $this->challanService->generateChallan(
                challanType: $validated['type'],
                schoolId: $schoolId,
                activeYearId: $activeYear->id,
                classLevel: $validated['class_level'],
                group: $validated['group'],
                studentType: $validated['student_type'],
                feePerStudent: (float) $validated['fee_per_student'],
                studentSelections: $validated['student_selections']
            );

            return response()->json([
                'success'               => true,
                'invoice_id'            => $invoice->id,
                'invoice_number'        => $invoice->invoice_number,
                'student_count'         => $invoice->student_count,
                'total_amount'          => $invoice->total_amount_paisas / 100,
                'fee_phase'             => $invoice->fee_phase,
                'download_challan_url'  => route('school.challan.download-pdf', $invoice->id),
                'download_list_url'     => route('school.challan.download-list', $invoice->id),
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to generate challan: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * AJAX: Get full invoice detail for modal inspection.
     */
    public function getInvoiceDetail(Invoice $challan)
    {
        $this->ensureSchoolOwnsChallan($challan);

        $challan->load([
            'school.district',
            'academicYear',
            'approvedBy',
            'invoiceStudents.student',
            'invoiceStudents.studentAcademicRecord',
        ]);

        $includedStudents = [];
        $excludedStudents = [];

        foreach ($challan->invoiceStudents as $item) {
            $student = $item->student;
            $record = $item->studentAcademicRecord;

            $data = [
                'id'                => $student?->id,
                'full_name'         => $student?->full_name ?? 'Unknown',
                'father_name'       => $student?->father_name ?? '—',
                'cnic'              => $student?->cnic ?? $student?->b_form ?? '—',
                'enrollment_number' => $student?->enrollment_number ?? 'PENDING',
                'amount'            => $item->amount_paisas / 100,
                'is_included'       => (bool) $item->is_included,
            ];

            if ($item->is_included) {
                $includedStudents[] = $data;
            } else {
                $excludedStudents[] = $data;
            }
        }

        $allottedCount = 0;
        if (in_array($challan->status, ['confirmed', 'verified']) && $challan->invoice_type === 'enrollment') {
            $allottedCount = $challan->invoiceStudents
                ->where('is_included', true)
                ->filter(fn ($item) => !empty($item->student?->enrollment_number))
                ->count();
        }

        return response()->json([
            'id'                     => $challan->id,
            'invoice_number'         => $challan->invoice_number,
            'invoice_type'           => $challan->invoice_type,
            'class_level'            => $challan->class_level ?? '—',
            'subject_group'          => $challan->subject_group ?? '—',
            'student_type'           => $challan->student_type ?? '—',
            'student_count'          => $challan->student_count,
            'total_amount'           => $challan->total_amount_paisas / 100,
            'status'                 => $challan->status,
            'fee_phase'              => $challan->fee_phase ?? 'normal',
            'rejection_reason'       => $challan->rejection_reason,
            'created_at'             => $challan->created_at?->format('d-M-Y H:i'),
            'approved_at'            => $challan->approved_at?->format('d-M-Y H:i'),
            'approved_by'            => $challan->approvedBy?->name,
            'school_name'            => $challan->school->name,
            'school_code'            => $challan->school->username,
            'district_name'          => $challan->school->district?->name,
            'academic_session'       => $challan->academicYear?->label,
            'allotted_count'         => $allottedCount,
            'included_students'      => $includedStudents,
            'excluded_students'      => $excludedStudents,
            'download_challan_url'   => route('school.challan.download-pdf', $challan->id),
            'download_list_url'      => route('school.challan.download-list', $challan->id),
        ]);
    }

    /**
     * Download Bank Challan PDF.
     */
    public function downloadChallan(Invoice $challan)
    {
        $this->ensureSchoolOwnsChallan($challan);
        return $this->pdfService->downloadChallanPdf($challan);
    }

    /**
     * Download Student List PDF.
     */
    public function downloadStudentList(Invoice $challan)
    {
        $this->ensureSchoolOwnsChallan($challan);
        return $this->pdfService->downloadStudentListPdf($challan);
    }

    /**
     * Ensure current school scope owns the challan.
     */
    private function ensureSchoolOwnsChallan(Invoice $challan): void
    {
        if ($challan->school_id !== $this->schoolScopeId()) {
            abort(403, 'Unauthorized access to school challan.');
        }
    }
}
