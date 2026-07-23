<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExamTimetable;
use App\Models\ExamCenter;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ExamTimetableController extends Controller
{
    public function index(Request $request): Response
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        $query = ExamTimetable::with('examCenter');

        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        }

        if ($request->filled('class_level')) {
            $query->where('class_level', $request->input('class_level'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('subject_name', 'like', "%{$search}%")
                  ->orWhere('subject_code', 'like', "%{$search}%");
            });
        }

        return Inertia::render('admin/examination/Timetable', [
            'timetables' => $query->orderBy('exam_date')->orderBy('start_time')->get(),
            'centers'    => ExamCenter::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'activeYear' => $year,
            'filters'    => $request->only(['class_level', 'search']),
        ]);
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
