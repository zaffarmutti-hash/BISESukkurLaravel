<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Certificate;
use App\Models\District;
use App\Models\ExamCenter;
use App\Models\ExamForm;
use App\Models\ExamTimetable;
use App\Models\FeeStructure;
use App\Models\Result;
use App\Models\Student;
use App\Services\DashboardStatsService;
use App\Services\WindowPhaseService;
use Illuminate\Http\Request;

class ExamHubController extends Controller
{
    public function __construct(
        private DashboardStatsService $stats,
        private WindowPhaseService $windowPhaseService
    ) {}

    public function index(Request $request)
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;
        $activeTab = $request->get('tab', 'window');

        // Tab 1: Exam Window
        $globalPhase = $this->windowPhaseService->resolvePhase(null, 'examination');

        // Tab 2: Exam Fees
        $fees = FeeStructure::where('academic_year_id', $yearId)
            ->where('fee_type', 'examination')
            ->orderBy('class_level')
            ->get();

        // Tab 3: Seat Allotment Status
        $eligibleSeatCount = Student::whereNotNull('enrollment_number')
            ->whereHas('academicRecords', fn ($q) => $q->where('academic_year_id', $yearId))
            ->count();
        $assignedSeatCount = ExamForm::where('academic_year_id', $yearId)
            ->whereNotNull('seat_number')
            ->count();
        $pendingSeatCount = max(0, $eligibleSeatCount - $assignedSeatCount);

        // Tab 4: Centers
        $centers = ExamCenter::with('district')->orderBy('name')->get();
        $districts = District::orderBy('name')->get();

        // Tab 5: Timetable Papers
        $timetables = ExamTimetable::where('academic_year_id', $yearId)
            ->orderBy('exam_date')
            ->get();

        // Tab 6: Results
        $recentResults = Result::with(['student'])
            ->latest()
            ->limit(20)
            ->get();

        // Tab 7: Reports
        $districtStats = $this->stats->districtBreakdown($yearId);

        // Tab 8: Certificates
        $certificates = Certificate::with(['student.school'])
            ->latest()
            ->limit(25)
            ->get();

        return view('superadmin.exam.hub', [
            'activeYear'         => $year,
            'activeTab'          => $activeTab,
            'globalPhase'        => $globalPhase,
            'fees'               => $fees,
            'eligibleSeatCount'  => $eligibleSeatCount,
            'assignedSeatCount'  => $assignedSeatCount,
            'pendingSeatCount'   => $pendingSeatCount,
            'centers'            => $centers,
            'districts'          => $districts,
            'timetables'         => $timetables,
            'recentResults'      => $recentResults,
            'districtStats'      => $districtStats,
            'certificates'       => $certificates,
            'stats'              => [
                'pending_verification' => $this->stats->pendingExamVerification($yearId),
                'missing_exam_forms'   => $this->stats->missingExamForms($yearId),
                'total_students'       => $this->stats->totalStudents($yearId),
            ],
        ]);
    }
}
