<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\ExamTimetable;
use App\Models\ExamForm;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ResultEntryController extends Controller
{
    public function index(Request $request): Response
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        $timetables = ExamTimetable::where('academic_year_id', $yearId)
            ->orderBy('exam_date')
            ->get(['id', 'subject_name', 'subject_code', 'class_level', 'total_marks']);

        $selectedTimetableId = $request->input('exam_timetable_id');
        $studentsData = [];
        $timetable = null;

        if ($selectedTimetableId) {
            $timetable = ExamTimetable::findOrFail($selectedTimetableId);

            // Fetch all confirmed exam forms for this class level
            $examForms = ExamForm::with('student')
                ->where('academic_year_id', $yearId)
                ->where('class_level', $timetable->class_level)
                ->where('status', ExamForm::STATUS_CONFIRMED)
                ->get();

            // Fetch existing entered results
            $existingResults = Result::where('exam_timetable_id', $timetable->id)->get()->keyBy('student_id');

            foreach ($examForms as $form) {
                $existing = $existingResults->get($form->student_id);
                $studentsData[] = [
                    'student_id'   => $form->student_id,
                    'exam_form_id' => $form->id,
                    'roll_number'  => $form->seat_number ?? 'Not Allotted',
                    'name'         => $form->student?->full_name,
                    'father_name'  => $form->student?->father_name,
                    'result_id'    => $existing?->id,
                    'marks_obtained'=> $existing?->marks_obtained ?? '',
                    'status'       => $existing?->status ?? 'pending',
                ];
            }
        }

        return Inertia::render('admin/examination/Results', [
            'timetables' => $timetables,
            'students'   => $studentsData,
            'timetable'  => $timetable,
            'filters'    => $request->only(['exam_timetable_id']),
        ]);
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
