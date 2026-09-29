<?php

namespace App\Modules\Admin\Controllers;

use App\Contracts\IEnrollmentNumberService;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\District;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnrollmentNumberController extends Controller
{
    public function __construct(
        private IEnrollmentNumberService $allotmentService
    ) {}

    /**
     * Display the official Allotment History registry and manual allotment console.
     */
    public function index(Request $request)
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        $districts = District::orderBy('name')->get();
        $schools = School::orderBy('name')->get();

        // Calculate KPI stats
        $stats = [
            'total_students' => 0,
            'allotted'       => 0,
            'pending'        => 0,
        ];

        if ($yearId) {
            $stats['total_students'] = Student::whereHas('academicRecords', fn ($q) => $q->where('academic_year_id', $yearId))->count();
            $stats['allotted'] = Student::whereNotNull('enrollment_number')
                ->whereHas('academicRecords', fn ($q) => $q->where('academic_year_id', $yearId))
                ->count();
            $stats['pending'] = Student::whereNull('enrollment_number')
                ->whereHas('invoiceStudents.invoice', fn ($q) =>
                    $q->where('invoice_type', 'enrollment')
                      ->whereIn('status', ['submitted', 'pending'])
                      ->where('academic_year_id', $yearId)
                )
                ->count();
        }

        // Query students with allotted enrollment numbers
        $query = Student::whereNotNull('enrollment_number')
            ->with([
                'school.district',
                'currentAcademicRecord.academicYear',
                'allottedByUser',
                'invoiceStudents.invoice' => fn ($q) => $q->where('invoice_type', 'enrollment'),
            ]);

        // Filter: District
        if ($request->filled('district_id')) {
            $query->whereHas('school', fn ($sq) => $sq->where('district_id', $request->district_id));
        }

        // Filter: School
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter: Class
        if ($request->filled('class_level')) {
            $query->whereHas('academicRecords', fn ($rq) => $rq->where('class_level', $request->class_level));
        }

        // Filter: Group
        if ($request->filled('group')) {
            $query->whereHas('academicRecords', fn ($rq) => $rq->where('subject_group', $request->group));
        }

        // Filter: Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('enrollment_number_issued_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('enrollment_number_issued_at', '<=', $request->date_to);
        }

        // Filter: Search (Name, CNIC, Enrollment Number)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('enrollment_number', 'ilike', "%{$search}%")
                  ->orWhere('full_name', 'ilike', "%{$search}%")
                  ->orWhere('father_name', 'ilike', "%{$search}%")
                  ->orWhere('cnic', 'ilike', "%{$search}%")
                  ->orWhere('b_form', 'ilike', "%{$search}%");
            });
        }

        // 25 per page server-side paginated
        $records = $query->latest('enrollment_number_issued_at')->paginate(25)->withQueryString();

        return view('superadmin.enrollment.allotment', [
            'records'    => $records,
            'stats'      => $stats,
            'activeYear' => $year,
            'districts'  => $districts,
            'schools'    => $schools,
            'filters'    => $request->only(['district_id', 'school_id', 'class_level', 'group', 'search', 'date_from', 'date_to']),
        ]);
    }

    /**
     * AJAX: Search student for Edge Case Manual Allotment.
     */
    public function searchStudentForManual(Request $request)
    {
        $query = trim($request->get('query', ''));
        if (strlen($query) < 3) {
            return response()->json(['found' => false, 'message' => 'Enter at least 3 characters to search.']);
        }

        $student = Student::with(['school.district', 'currentAcademicRecord'])
            ->where(function ($q) use ($query) {
                $q->where('cnic', $query)
                  ->orWhere('b_form', $query)
                  ->orWhere('gr_number', $query)
                  ->orWhere('full_name', 'ilike', "%{$query}%");
            })
            ->first();

        if (!$student) {
            return response()->json(['found' => false, 'message' => 'No candidate found matching this CNIC, B-Form, or Name.']);
        }

        return response()->json([
            'found'                  => true,
            'id'                     => $student->id,
            'full_name'              => $student->full_name,
            'father_name'            => $student->father_name,
            'cnic'                   => $student->cnic ?? $student->b_form ?? '—',
            'school_name'            => $student->school?->name ?? '—',
            'school_code'            => $student->school?->username ?? '—',
            'district_name'          => $student->school?->district?->name ?? '—',
            'class_level'            => strtoupper($student->currentAcademicRecord?->class_level ?? 'SSC'),
            'subject_group'          => ucfirst($student->currentAcademicRecord?->subject_group ?? 'General'),
            'has_enrollment_number'  => !empty($student->enrollment_number),
            'enrollment_number'      => $student->enrollment_number,
        ]);
    }

    /**
     * Execute manual allotment for edge case candidate.
     */
    public function manualAllot(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'reason'     => 'required|string|min:30|max:1000',
        ]);

        $year = AcademicYear::current();
        if (!$year) {
            return response()->json(['success' => false, 'message' => 'No active academic year configured.'], 422);
        }

        try {
            $number = $this->allotmentService->manualAllotment(
                studentId: $validated['student_id'],
                yearId: $year->id,
                reason: $validated['reason'],
                userId: Auth::id()
            );

            return response()->json([
                'success'           => true,
                'enrollment_number' => $number,
                'message'           => "Manual enrollment number {$number} generated and registered successfully.",
            ]);
        } catch (\Throwable $e) {
            Log::error("Manual allotment failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
