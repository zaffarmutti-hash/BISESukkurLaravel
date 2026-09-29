<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Certificate;
use App\Models\District;
use App\Models\ExamForm;
use App\Models\Invoice;
use App\Models\Result;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentAcademicRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Three gap-detection reports for BISE Sukkur board-level oversight.
 *
 *   1. enrollmentCoverage() — Schools that have submitted zero enrollment challans.
 *                             A school at zero means ALL its students are missed.
 *
 *   2. resultCoverage()     — Students who sat the exam but have no result published.
 *                             Every non-zero count here is a student in limbo.
 *
 *   3. certificateCoverage()— Students who passed but have not received a certificate.
 *                             Every non-zero count here is an incomplete issuance cycle.
 */
class CoverageReportController extends Controller
{
    // ─── 1. Enrollment Coverage ───────────────────────────────────────────────

    /**
     * List every school with its challan submission count for the active year.
     * Schools with 0 submissions are flagged — they have missed students.
     */
    public function enrollmentCoverage(Request $request)
    {
        $year   = AcademicYear::current();
        $yearId = $year?->id;

        $districtId = $request->input('district_id');
        $showOnly   = $request->input('show', 'all'); // 'all' | 'zero' | 'active'

        $schools = School::with('district')
            ->when($districtId, fn ($q) => $q->where('district_id', $districtId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (School $school) use ($yearId) {
                // Count enrollment challans submitted (any status)
                $submitted = Invoice::where('school_id', $school->id)
                    ->where('invoice_type', 'enrollment')
                    ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
                    ->count();

                // Confirmed (verified) challans
                $confirmed = Invoice::where('school_id', $school->id)
                    ->where('invoice_type', 'enrollment')
                    ->whereIn('status', ['confirmed', 'verified'])
                    ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
                    ->count();

                // Students enrolled in this school for active year
                $enrolled = $yearId
                    ? StudentAcademicRecord::whereHas(
                        'student', fn ($q) => $q->where('school_id', $school->id)
                    )->where('academic_year_id', $yearId)->count()
                    : 0;

                // Students with enrollment numbers
                $allotted = Student::where('school_id', $school->id)
                    ->whereNotNull('enrollment_number')
                    ->when($yearId, fn ($q) => $q->whereHas('academicRecords', fn ($r) => $r->where('academic_year_id', $yearId)))
                    ->count();

                return [
                    'school'          => $school,
                    'district'        => $school->district?->name ?? '—',
                    'submitted'       => $submitted,
                    'confirmed'       => $confirmed,
                    'enrolled'        => $enrolled,
                    'allotted'        => $allotted,
                    'is_zero'         => $submitted === 0,
                    'has_gap'         => $enrolled > $allotted,
                ];
            });

        // Apply show filter
        if ($showOnly === 'zero') {
            $schools = $schools->filter(fn ($s) => $s['is_zero']);
        } elseif ($showOnly === 'gap') {
            $schools = $schools->filter(fn ($s) => $s['has_gap']);
        }

        $summary = [
            'total_schools'        => $schools->count(),
            'zero_submission'      => $schools->where('is_zero', true)->count(),
            'has_gap'              => $schools->where('has_gap', true)->count(),
            'fully_allotted'       => $schools->where('has_gap', false)->where('is_zero', false)->count(),
        ];

        return view('superadmin.reports.coverage', [
            'activeYear' => $year,
            'schools'    => $schools->values(),
            'districts'  => District::orderBy('name')->get(),
            'summary'    => $summary,
            'filters'    => $request->only(['district_id', 'show']),
        ]);
    }

    // ─── 2. Result Coverage ───────────────────────────────────────────────────

    /**
     * Students who sat the exam (confirmed exam form) but have no result record.
     * Per-district breakdown + searchable student list.
     */
    public function resultCoverage(Request $request)
    {
        $year       = AcademicYear::current();
        $yearId     = $year?->id;
        $districtId = $request->input('district_id');

        // Students with confirmed exam form but no result in active year
        $missing = Student::with(['school.district', 'examForms'])
            ->when($districtId, fn ($q) => $q->whereHas('school', fn ($s) => $s->where('district_id', $districtId)))
            ->whereHas('examForms', fn ($q) => $q
                ->where('academic_year_id', $yearId)
                ->where('status', 'confirmed')
            )
            ->whereDoesntHave('results', fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($request->input('search'), fn ($q, $s) => $q
                ->where(fn ($w) => $w
                    ->where('full_name', 'like', "%{$s}%")
                    ->orWhere('enrollment_number', 'like', "%{$s}%")
                )
            )
            ->orderBy('full_name')
            ->paginate(50)
            ->withQueryString();

        // District-level breakdown
        $districtBreakdown = District::withCount('schools')->get()->map(function (District $d) use ($yearId) {
            $schoolIds = School::where('district_id', $d->id)->pluck('id');

            $confirmedExam = ExamForm::where('academic_year_id', $yearId)
                ->where('status', 'confirmed')
                ->whereHas('student', fn ($q) => $q->whereIn('school_id', $schoolIds))
                ->count();

            $withResults = Result::where('academic_year_id', $yearId)
                ->whereHas('student', fn ($q) => $q->whereIn('school_id', $schoolIds))
                ->distinct('student_id')
                ->count('student_id');

            return [
                'district'      => $d->name,
                'confirmed_exam'=> $confirmedExam,
                'with_results'  => $withResults,
                'missing'       => max(0, $confirmedExam - $withResults),
            ];
        });

        $totalMissing = $districtBreakdown->sum('missing');

        return view('superadmin.reports.results', [
            'activeYear'         => $year,
            'missing'            => $missing,
            'districtBreakdown'  => $districtBreakdown,
            'totalMissing'       => $totalMissing,
            'districts'          => District::orderBy('name')->get(),
            'filters'            => $request->only(['district_id', 'search']),
        ]);
    }

    // ─── 3. Certificate Coverage ──────────────────────────────────────────────

    /**
     * Students who passed their exam but have no certificate issued.
     * Uses is_pass flag on results to find passing students without a certificate record.
     */
    public function certificateCoverage(Request $request)
    {
        $year       = AcademicYear::current();
        $yearId     = $year?->id;
        $districtId = $request->input('district_id');

        // Students with at least one passing result but no certificate
        $missing = Student::with(['school.district', 'results'])
            ->when($districtId, fn ($q) => $q->whereHas('school', fn ($s) => $s->where('district_id', $districtId)))
            ->whereHas('results', fn ($q) => $q
                ->where('academic_year_id', $yearId)
                ->where('is_pass', true)
            )
            ->whereDoesntHave('certificates', fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($request->input('search'), fn ($q, $s) => $q
                ->where(fn ($w) => $w
                    ->where('full_name', 'like', "%{$s}%")
                    ->orWhere('enrollment_number', 'like', "%{$s}%")
                )
            )
            ->orderBy('full_name')
            ->paginate(50)
            ->withQueryString();

        // District-level summary
        $districtBreakdown = District::get()->map(function (District $d) use ($yearId) {
            $schoolIds = School::where('district_id', $d->id)->pluck('id');

            $passed = Result::where('academic_year_id', $yearId)
                ->where('is_pass', true)
                ->whereHas('student', fn ($q) => $q->whereIn('school_id', $schoolIds))
                ->distinct('student_id')
                ->count('student_id');

            $issued = Certificate::where('academic_year_id', $yearId)
                ->where('is_issued', true)
                ->whereHas('student', fn ($q) => $q->whereIn('school_id', $schoolIds))
                ->count();

            return [
                'district' => $d->name,
                'passed'   => $passed,
                'issued'   => $issued,
                'pending'  => max(0, $passed - $issued),
            ];
        });

        $totalPassed  = $districtBreakdown->sum('passed');
        $totalIssued  = $districtBreakdown->sum('issued');
        $totalPending = $districtBreakdown->sum('pending');

        return view('superadmin.reports.certificates', [
            'activeYear'        => $year,
            'missing'           => $missing,
            'districtBreakdown' => $districtBreakdown,
            'totalPassed'       => $totalPassed,
            'totalIssued'       => $totalIssued,
            'totalPending'      => $totalPending,
            'districts'         => District::orderBy('name')->get(),
            'filters'           => $request->only(['district_id', 'search']),
        ]);
    }
}
