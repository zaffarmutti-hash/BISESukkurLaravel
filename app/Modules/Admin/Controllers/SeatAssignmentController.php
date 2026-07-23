<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ExamCenter;
use App\Models\ExamForm;
use App\Modules\Admin\Services\SeatAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class SeatAssignmentController extends Controller
{
    public function __construct(private SeatAssignmentService $allotmentService) {}

    public function index(Request $request): Response
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        $stats = [
            'total_candidates' => 0,
            'allotted'         => 0,
            'pending'          => 0,
        ];

        if ($yearId) {
            $stats['total_candidates'] = ExamForm::where('academic_year_id', $yearId)
                ->where('status', ExamForm::STATUS_CONFIRMED)
                ->count();
            
            $stats['allotted'] = ExamForm::where('academic_year_id', $yearId)
                ->where('status', ExamForm::STATUS_CONFIRMED)
                ->whereNotNull('seat_number')
                ->count();

            $stats['pending'] = $stats['total_candidates'] - $stats['allotted'];
        }

        $history = Activity::where('log_name', 'seat_allotment')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'description' => $a->description,
                'user'        => $a->causer?->name ?? 'System',
                'created_at'  => $a->created_at?->toDateTimeString(),
                'properties'  => $a->properties,
            ]);

        return Inertia::render('admin/examination/SeatAllotment', [
            'stats'      => $stats,
            'centers'    => ExamCenter::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'history'    => $history,
            'activeYear' => $year,
        ]);
    }

    public function runAllotment(Request $request)
    {
        $year = AcademicYear::current();
        if (!$year) {
            return back()->with('error', 'No active academic year configured.');
        }

        $validated = $request->validate([
            'class_level'    => 'required|in:ssc_part1,ssc_part2,hsc_part1,hsc_part2',
            'start_sequence' => 'required|integer|min:100000',
            'exam_center_id' => 'nullable|exists:exam_centers,id',
        ]);

        $count = $this->allotmentService->assign(
            $year->id,
            $validated['class_level'],
            (int) $validated['start_sequence'],
            $validated['exam_center_id'] ? (int) $validated['exam_center_id'] : null
        );

        if ($count === 0) {
            return back()->with('warning', 'No pending confirmed candidates found matching the criteria.');
        }

        activity('seat_allotment')
            ->causedBy(Auth::user())
            ->withProperties([
                'class_level'    => $validated['class_level'],
                'start_sequence' => $validated['start_sequence'],
                'exam_center_id' => $validated['exam_center_id'],
                'count'          => $count,
                'year'           => $year->label,
            ])
            ->log("Seat allotment generated for {$count} candidates in {$validated['class_level']}");

        return back()->with('success', "Seat allotment run complete. Assigned seat numbers to {$count} candidates.");
    }
}
