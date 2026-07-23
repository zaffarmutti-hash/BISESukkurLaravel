<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\District;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentAcademicRecord;
use App\Services\DashboardStatsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(private DashboardStatsService $statsService) {}

    public function enrollment(Request $request): Response
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        $query = StudentAcademicRecord::with(['student.school.district', 'academicYear'])
            ->where('academic_year_id', $yearId);

        if ($request->filled('district_id')) {
            $query->whereHas('student.school', function ($q) use ($request) {
                $q->where('district_id', $request->input('district_id'));
            });
        }

        if ($request->filled('school_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('school_id', $request->input('school_id'));
            });
        }

        if ($request->filled('class_level')) {
            $query->where('class_level', $request->input('class_level'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('enrollment_number', 'like', "%{$search}%");
            });
        }

        return Inertia::render('superadmin/reports/EnrollmentReport', [
            'records'    => $query->latest()->paginate(25)->withQueryString(),
            'districts'  => District::orderBy('name')->get(['id', 'name']),
            'schools'    => School::orderBy('name')->get(['id', 'name', 'username']),
            'activeYear' => $year,
            'filters'    => $request->only(['district_id', 'school_id', 'class_level', 'search']),
        ]);
    }

    public function gap(Request $request): Response
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        $query = Student::with(['school.district'])
            ->withEnrollmentNumber()
            ->whereHas('academicRecords', fn ($q) => $q->where('academic_year_id', $yearId))
            ->whereDoesntHave('examForms', fn ($q) => $q->where('academic_year_id', $yearId));

        if ($request->filled('district_id')) {
            $query->whereHas('school', function ($q) use ($request) {
                $q->where('district_id', $request->input('district_id'));
            });
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->input('school_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('enrollment_number', 'like', "%{$search}%");
            });
        }

        return Inertia::render('superadmin/reports/ExamGapReport', [
            'records'    => $query->latest()->paginate(25)->withQueryString(),
            'districts'  => District::orderBy('name')->get(['id', 'name']),
            'schools'    => School::orderBy('name')->get(['id', 'name', 'username']),
            'activeYear' => $year,
            'filters'    => $request->only(['district_id', 'school_id', 'search']),
        ]);
    }

    public function feeCollection(Request $request): Response
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        $query = Challan::with(['school.district'])
            ->confirmed()
            ->where('academic_year_id', $yearId);

        if ($request->filled('district_id')) {
            $query->whereHas('school', function ($q) use ($request) {
                $q->where('district_id', $request->input('district_id'));
            });
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->input('school_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->input('date_to'));
        }

        return Inertia::render('superadmin/reports/FeeCollectionReport', [
            'records'    => $query->latest()->paginate(25)->withQueryString(),
            'districts'  => District::orderBy('name')->get(['id', 'name']),
            'schools'    => School::orderBy('name')->get(['id', 'name', 'username']),
            'activeYear' => $year,
            'filters'    => $request->only(['district_id', 'school_id', 'date_from', 'date_to']),
        ]);
    }

    public function district(Request $request): Response
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        if ($request->filled('district_id')) {
            $district = District::findOrFail($request->input('district_id'));
            
            $schoolsQuery = School::where('district_id', $district->id)
                ->withCount(['students' => function($q) use ($yearId) {
                    $q->whereHas('academicRecords', fn($sq) => $sq->where('academic_year_id', $yearId));
                }]);

            if ($request->filled('search')) {
                $search = $request->input('search');
                $schoolsQuery->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%");
            }

            $schools = $schoolsQuery->paginate(25)->withQueryString();

            // Enrich schools list with payment summaries
            $schools->through(function($school) use ($yearId) {
                $verifiedAmount = (float) Invoice::confirmed()
                    ->where('school_id', $school->id)
                    ->where('academic_year_id', $yearId)
                    ->sum('total_amount_paisas') / 100;

                $pendingCount = Invoice::pending()
                    ->where('school_id', $school->id)
                    ->where('academic_year_id', $yearId)
                    ->count();

                $missingExamForms = Student::withEnrollmentNumber()
                    ->where('school_id', $school->id)
                    ->whereHas('academicRecords', fn ($q) => $q->where('academic_year_id', $yearId))
                    ->whereDoesntHave('examForms', fn ($q) => $q->where('academic_year_id', $yearId))
                    ->count();

                return [
                    'id'                 => $school->id,
                    'name'               => $school->name,
                    'code'               => $school->code,
                    'student_count'      => $school->students_count,
                    'verified_amount'    => $verifiedAmount,
                    'pending_invoices'   => $pendingCount,
                    'missing_exam_forms' => $missingExamForms,
                ];
            });

            return Inertia::render('superadmin/reports/DistrictDetail', [
                'district'   => $district,
                'schools'    => $schools,
                'activeYear' => $year,
                'filters'    => $request->only(['district_id', 'search']),
            ]);
        }

        // Default comparison list
        $breakdown = $this->statsService->districtBreakdown($yearId);

        return Inertia::render('superadmin/reports/DistrictComparison', [
            'districtBreakdown' => $breakdown,
            'activeYear'        => $year,
        ]);
    }
}
