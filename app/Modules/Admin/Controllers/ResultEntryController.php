<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\ExamTimetable;
use App\Models\ExamForm;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ResultEntryController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('superadmin.exam.results');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_timetable_id' => 'required|exists:exam_timetables,id',
            'results'           => 'required|array',
            'results.*.student_id'   => 'required|exists:students,id',
            'results.*.exam_form_id' => 'required|exists:exam_forms,id',
            'results.*.marks_obtained' => 'nullable|integer|min:0',
        ]);

        $timetable = ExamTimetable::findOrFail($validated['exam_timetable_id']);
        $totalMarks = $timetable->total_marks;

        foreach ($validated['results'] as $item) {
            if ($item['marks_obtained'] === null || $item['marks_obtained'] === '') {
                continue;
            }

            $obtained = (int) $item['marks_obtained'];
            if ($obtained > $totalMarks) {
                return back()->with('error', "Marks obtained ({$obtained}) cannot exceed total marks ({$totalMarks}) for subject {$timetable->subject_name}.");
            }

            $percentage = ($obtained / $totalMarks) * 100;
            $isPass = $percentage >= 33.0;

            // Grade assignment
            if ($percentage >= 80) $grade = 'A-1';
            elseif ($percentage >= 70) $grade = 'A';
            elseif ($percentage >= 60) $grade = 'B';
            elseif ($percentage >= 50) $grade = 'C';
            elseif ($percentage >= 40) $grade = 'D';
            elseif ($percentage >= 33) $grade = 'E';
            else $grade = 'F';

            Result::updateOrCreate(
                [
                    'student_id'        => $item['student_id'],
                    'exam_timetable_id' => $timetable->id,
                ],
                [
                    'academic_year_id'  => $timetable->academic_year_id,
                    'exam_form_id'      => $item['exam_form_id'],
                    'subject_name'      => $timetable->subject_name,
                    'subject_code'      => $timetable->subject_code,
                    'total_marks'       => $totalMarks,
                    'marks_obtained'    => $obtained,
                    'percentage'        => $percentage,
                    'passing_percentage'=> 33.0,
                    'is_pass'           => $isPass,
                    'grade'             => $grade,
                    'status'            => 'entered',
                    'entered_by'        => Auth::id(),
                    'entered_at'        => now(),
                ]
            );
        }

        activity('exam_results')
            ->causedBy(Auth::user())
            ->log("Results entered for subject {$timetable->subject_name} ({$timetable->class_level})");

        return back()->with('success', 'Marks entered and saved successfully.');
    }

    public function verify(Request $request, Result $result)
    {
        $result->update([
            'status'      => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        activity('exam_results')
            ->causedBy(Auth::user())
            ->performedOn($result)
            ->log("Result verified for student ID {$result->student_id} in subject {$result->subject_name}");

        return back()->with('success', 'Candidate marks verified successfully.');
    }
}
