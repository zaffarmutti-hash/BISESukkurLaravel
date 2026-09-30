<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExamTimetable;
use App\Models\ExamCenter;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ExamTimetableController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('superadmin.exam.timetable');
    }

    public function store(Request $request)
    {
        $year = AcademicYear::current();
        if (!$year) {
            return back()->with('error', 'No active academic year configured.');
        }

        $validated = $request->validate([
            'exam_center_id' => 'required|exists:exam_centers,id',
            'class_level'    => 'required|in:ssc_part1,ssc_part2,hsc_part1,hsc_part2',
            'subject_name'   => 'required|string|max:150',
            'subject_code'   => 'required|string|max:20',
            'exam_date'      => 'required|date',
            'start_time'     => 'required|string',
            'end_time'       => 'required|string',
            'total_marks'    => 'required|integer|min:1',
        ]);

        $timetable = ExamTimetable::create([
            ...$validated,
            'academic_year_id' => $year->id,
        ]);

        activity('exam_timetable')
            ->causedBy(Auth::user())
            ->performedOn($timetable)
            ->withProperties(['subject' => $timetable->subject_name, 'class' => $timetable->class_level])
            ->log("Exam timetable scheduled for {$timetable->subject_name} ({$timetable->class_level})");

        return back()->with('success', 'Exam slot scheduled successfully.');
    }

    public function update(Request $request, ExamTimetable $timetable)
    {
        $validated = $request->validate([
            'exam_center_id' => 'required|exists:exam_centers,id',
            'class_level'    => 'required|in:ssc_part1,ssc_part2,hsc_part1,hsc_part2',
            'subject_name'   => 'required|string|max:150',
            'subject_code'   => 'required|string|max:20',
            'exam_date'      => 'required|date',
            'start_time'     => 'required|string',
            'end_time'       => 'required|string',
            'total_marks'    => 'required|integer|min:1',
        ]);

        $timetable->update($validated);

        activity('exam_timetable')
            ->causedBy(Auth::user())
            ->performedOn($timetable)
            ->withProperties(['subject' => $timetable->subject_name, 'class' => $timetable->class_level])
            ->log("Exam timetable updated for {$timetable->subject_name} ({$timetable->class_level})");

        return back()->with('success', 'Exam slot updated successfully.');
    }

    public function destroy(ExamTimetable $timetable)
    {
        if ($timetable->results()->exists()) {
            return back()->with('error', 'Cannot delete this slot. Results have already been entered for this exam.');
        }

        activity('exam_timetable')
            ->causedBy(Auth::user())
            ->performedOn($timetable)
            ->withProperties(['subject' => $timetable->subject_name, 'class' => $timetable->class_level])
            ->log("Exam timetable slot deleted for {$timetable->subject_name}");

        $timetable->delete();

        return back()->with('success', 'Exam slot deleted successfully.');
    }
}
