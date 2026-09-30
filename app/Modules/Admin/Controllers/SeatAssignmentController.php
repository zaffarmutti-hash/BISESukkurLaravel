<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ExamCenter;
use App\Models\ExamForm;
use App\Modules\Admin\Services\SeatAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class SeatAssignmentController extends Controller
{
    public function __construct(private SeatAssignmentService $allotmentService) {}

    public function index(Request $request)
    {
        return redirect()->route('superadmin.exam.seats');
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
