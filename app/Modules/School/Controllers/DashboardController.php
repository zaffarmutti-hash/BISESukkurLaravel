<?php

namespace App\Modules\School\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesSchoolScope;
use App\Services\BoardPolicyService;
use App\Models\Student;
use App\Models\StudentAcademicRecord;
use App\Models\Invoice;
use App\Models\ExamForm;

class DashboardController extends Controller
{
    use ResolvesSchoolScope;

    public function __construct(private BoardPolicyService $boardPolicy) {}

    public function index()
    {
        $schoolId = $this->schoolScopeId();
        $boardPolicy = $this->boardPolicy->policySnapshotForSchool($schoolId);

        $school = auth()->user()?->school;
        $allowedLevels = match ($school?->type) {
            'college'          => ['intermediate'],
            'higher_secondary' => ['matric', 'intermediate'],
            default            => ['matric', 'intermediate'],
        };

        $totalStudents = Student::where('school_id', $schoolId)->count();
        $sscStudents = StudentAcademicRecord::whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->whereIn('class_level', ['9th', '10th', 'SSC-I', 'SSC-II'])
            ->count();
        $hscStudents = StudentAcademicRecord::whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->whereIn('class_level', ['11th', '12th', 'HSC-I', 'HSC-II'])
            ->count();

        $totalChallans = Invoice::where('school_id', $schoolId)->count();
        $pendingChallans = Invoice::where('school_id', $schoolId)->where('status', 'pending')->count();
        $pendingExamForms = ExamForm::whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', 'draft')
            ->count();
        $awaitingEnrollmentNo = Student::where('school_id', $schoolId)->whereNull('enrollment_number')->count();

        $stats = [
            'active_year'                 => $boardPolicy['label'] ?? null,
            'total_students'              => $totalStudents,
            'ssc_students'                => $sscStudents,
            'hsc_students'                => $hscStudents,
            'total_challans'              => $totalChallans,
            'pending_enrollment_challans' => $pendingChallans,
            'pending_exam_forms'          => $pendingExamForms,
            'awaiting_enrollment_no'      => $awaitingEnrollmentNo,
        ];

        return view('school.dashboard', [
            'stats'         => $stats,
            'activeYear'    => $boardPolicy,
            'allowedLevels' => $allowedLevels,
        ]);
    }
}
