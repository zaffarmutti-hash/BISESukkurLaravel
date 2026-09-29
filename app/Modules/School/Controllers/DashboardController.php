<?php

namespace App\Modules\School\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesSchoolScope;
use App\Services\BoardPolicyService;
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
        $yearId = $boardPolicy['academic_year_id'] ?? null;
        $allowedLevels = match ($school?->type) {
            'college'          => ['intermediate'],
            'higher_secondary' => ['matric', 'intermediate'],
            default            => ['matric', 'intermediate'],
        };

        $recordsQuery = StudentAcademicRecord::query()
            ->where('academic_year_id', $yearId)
            ->where('status', StudentAcademicRecord::STATUS_ENROLLED)
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId));
        $totalStudents = (clone $recordsQuery)->count();
        $enrollmentByClass = (clone $recordsQuery)
            ->select('class_level')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('class_level')
            ->pluck('total', 'class_level');

        $examsByClass = ExamForm::query()
            ->join('student_academic_records', 'exam_forms.student_academic_record_id', '=', 'student_academic_records.id')
            ->join('students', 'exam_forms.student_id', '=', 'students.id')
            ->where('exam_forms.academic_year_id', $yearId)
            ->where('student_academic_records.academic_year_id', $yearId)
            ->where('students.school_id', $schoolId)
            ->whereNull('students.deleted_at')
            ->select('student_academic_records.class_level')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('student_academic_records.class_level')
            ->pluck('total', 'student_academic_records.class_level');

        $invoiceTotalsByClass = Invoice::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $yearId)
            ->whereIn('class_level', ['ssc_part1', 'ssc_part2', 'hsc_part1', 'hsc_part2'])
            ->select('class_level')
            ->selectRaw("SUM(CASE WHEN status IN ('confirmed', 'verified') THEN total_amount_paisas ELSE 0 END) AS fees_paid_paisas")
            ->selectRaw("SUM(CASE WHEN status IN ('submitted', 'pending', 'draft', 'unpaid') THEN total_amount_paisas ELSE 0 END) AS fees_payable_paisas")
            ->groupBy('class_level')
            ->get()
            ->keyBy('class_level');

        $classLabels = [
            'ssc_part1' => '9th Class',
            'ssc_part2' => '10th Class',
            'hsc_part1' => '11th Class',
            'hsc_part2' => '12th Class',
        ];
        $classBreakdown = collect($classLabels)->map(function ($label, $level) use ($enrollmentByClass, $examsByClass, $invoiceTotalsByClass) {
            $invoiceTotals = $invoiceTotalsByClass->get($level);

            return [
                'class' => $label,
                'enrollment' => (int) $enrollmentByClass->get($level, 0),
                'examinations' => (int) $examsByClass->get($level, 0),
                'fees_paid' => (int) ($invoiceTotals?->fees_paid_paisas ?? 0) / 100,
                'fees_payable' => (int) ($invoiceTotals?->fees_payable_paisas ?? 0) / 100,
            ];
        })->values();

        $totalExaminations = ExamForm::where('academic_year_id', $yearId)
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->whereHas('studentAcademicRecord', fn ($q) => $q->where('academic_year_id', $yearId))
            ->count();

        $schoolInvoices = Invoice::where('school_id', $schoolId)->where('academic_year_id', $yearId);
        $feesPaid = (clone $schoolInvoices)->whereIn('status', ['confirmed', 'verified'])->sum('total_amount_paisas') / 100;
        $feesPayable = (clone $schoolInvoices)->whereIn('status', ['submitted', 'pending', 'draft', 'unpaid'])->sum('total_amount_paisas') / 100;

        $stats = [
            'active_year'                 => $boardPolicy['label'] ?? null,
            'total_students'              => $totalStudents,
            'total_examinations'          => $totalExaminations,
            'fees_paid'                   => $feesPaid,
            'fees_payable'                => $feesPayable,
        ];

        return view('school.dashboard', [
            'stats'         => $stats,
            'activeYear'    => $boardPolicy,
            'allowedLevels' => $allowedLevels,
            'classBreakdown' => $classBreakdown,
        ]);
    }
}
