<?php

namespace App\Modules\School\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesSchoolScope;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\ExamForm;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    use ResolvesSchoolScope;

    /**
     * Download a single enrollment form PDF.
     */
    public function enrollmentFormPdf(Student $student)
    {
        $this->ensureSchoolOwnsStudent($student);

        $student->load(['currentAcademicRecord', 'school.district']);
        $academicRecord = $student->currentAcademicRecord;

        if (! $academicRecord) {
            abort(404, 'Academic record not found for this student.');
        }

        $activeYear = AcademicYear::current();

        $pdf = Pdf::loadView('print.enrollment-form', [
            'student'        => $student,
            'academicRecord' => $academicRecord,
            'academicYear'   => $activeYear,
            'status'         => $academicRecord->status,
        ]);

        $pdf->setPaper('a4', 'portrait');

        // Filename protection: never expose database auto-increment ID
        $token = substr(md5($student->id . config('app.key')), 0, 10);
        $filename = $academicRecord->isDraft()
            ? "draft-enrollment-{$token}.pdf"
            : ($student->enrollment_number ? "enrollment-{$student->enrollment_number}.pdf" : "enrollment-confirmed-{$token}.pdf");

        return $pdf->download($filename);
    }

    /**
     * Download bulk enrollment forms as a single concatenated PDF.
     */
    public function enrollmentFormBulkPdf(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();
        $tab = $request->get('tab', 'draft');

        $query = Student::query()
            ->where('students.school_id', $schoolId)
            ->whereHas('currentAcademicRecord')
            ->with(['currentAcademicRecord', 'school.district']);

        // Apply same filters as list
        if ($tab === 'draft') {
            $query->whereHas('currentAcademicRecord', fn ($q) => $q->where('status', 'draft'));
        } else {
            $query->whereHas('currentAcademicRecord', fn ($q) =>
                $q->whereIn('status', ['final', 'pending_challan', 'challan_submitted', 'enrolled'])
            );
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'ilike', "%{$search}%")
                  ->orWhere('father_name', 'ilike', "%{$search}%")
                  ->orWhere('enrollment_number', 'ilike', "%{$search}%")
                  ->orWhere('cnic', 'ilike', "%{$search}%")
                  ->orWhere('b_form', 'ilike', "%{$search}%");
            });
        }

        if ($classLevel = $request->get('class_level')) {
            $query->whereHas('currentAcademicRecord', fn ($q) =>
                $q->where('class_level', $classLevel)
            );
        }

        if ($group = $request->get('group')) {
            $query->whereHas('currentAcademicRecord', fn ($q) =>
                $q->where('subject_group', $group)
            );
        }

        $students = $query->orderBy('full_name')->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No students found to export.');
        }

        // Render bulk template by repeating single forms with CSS page-break
        $html = '';
        foreach ($students as $index => $student) {
            $record = $student->currentAcademicRecord;
            $singleHtml = view('print.enrollment-form', [
                'student'        => $student,
                'academicRecord' => $record,
                'academicYear'   => $activeYear,
                'status'         => $record->status,
            ])->render();

            // Extract body content or wrap in page-break
            if ($index > 0) {
                $html .= '<div style="page-break-before: always;"></div>';
            }
            $html .= $singleHtml;
        }

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');

        $bulkToken = substr(md5(now()->timestamp . $schoolId), 0, 10);
        $filename = "bulk-enrollments-{$activeTab}-{$bulkToken}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Download a single exam admission slip PDF (which acts as the exam form print view).
     */
    public function examFormPdf(ExamForm $examForm)
    {
        $this->ensureSchoolOwnsExamForm($examForm);

        $examForm->load(['student', 'academicYear', 'studentAcademicRecord', 'subjects.examTimetable', 'examCenter']);

        // Generate PDF using existing admission-slip template
        $pdf = Pdf::loadView('print.admission-slip', [
            'examForm' => $examForm,
            'student'  => $examForm->student,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $token = substr(md5($examForm->id . config('app.key')), 0, 10);
        $filename = $examForm->isDraft()
            ? "draft-exam-slip-{$token}.pdf"
            : ($examForm->seat_number ? "exam-slip-{$examForm->seat_number}.pdf" : "exam-slip-final-{$token}.pdf");

        return $pdf->download($filename);
    }

    public function examFormBulkPdf(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $tab = $request->get('tab', 'draft');

        $query = ExamForm::query()
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->with(['student', 'academicYear', 'studentAcademicRecord', 'subjects.examTimetable', 'examCenter']);

        if ($tab === 'draft') {
            $query->where('status', 'draft');
        } else {
            $query->whereIn('status', ['final', 'submitted', 'confirmed', 'rejected']);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('student', fn ($q) =>
                $q->where('full_name', 'ilike', "%{$search}%")
                  ->orWhere('enrollment_number', 'ilike', "%{$search}%")
                  ->orWhere('cnic', 'ilike', "%{$search}%")
            );
        }

        if ($classLevel = $request->get('class_level')) {
            $query->whereHas('studentAcademicRecord', fn ($q) =>
                $q->where('class_level', $classLevel)
            );
        }

        $examForms = $query->orderByDesc('created_at')->get();

        if ($examForms->isEmpty()) {
            return back()->with('error', 'No exam forms found to export.');
        }

        $html = '';
        foreach ($examForms as $index => $examForm) {
            $singleHtml = view('print.admission-slip', [
                'examForm' => $examForm,
                'student'  => $examForm->student,
            ])->render();

            if ($index > 0) {
                $html .= '<div style="page-break-before: always;"></div>';
            }
            $html .= $singleHtml;
        }

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');

        $bulkToken = substr(md5(now()->timestamp . $schoolId), 0, 10);
        $filename = "bulk-exam-slips-{$tab}-{$bulkToken}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Download gap report PDF — students with enrollment numbers but no exam form.
     */
    public function examGapReportPdf(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();

        $query = Student::query()
            ->where('school_id', $schoolId)
            ->whereNotNull('enrollment_number')
            ->whereHas('currentAcademicRecord', fn ($q) =>
                $q->whereIn('status', ['final', 'pending_challan', 'challan_submitted', 'enrolled'])
            )
            ->when($activeYear, fn ($q) => $q->whereDoesntHave('examForms', fn ($ef) =>
                $ef->where('academic_year_id', $activeYear->id)
            ))
            ->with('currentAcademicRecord');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'ilike', "%{$search}%")
                  ->orWhere('enrollment_number', 'ilike', "%{$search}%")
                  ->orWhere('father_name', 'ilike', "%{$search}%");
            });
        }

        $students = $query->orderBy('full_name')->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No students found for gap report export.');
        }

        $rows = $students->map(function (Student $student) {
            $record = $student->currentAcademicRecord;
            $issuedAt = $student->enrollment_number_issued_at ?? $student->created_at;

            return [
                'full_name'             => $student->full_name,
                'father_name'           => $student->father_name,
                'enrollment_number'     => $student->enrollment_number,
                'class_level'           => $record?->class_level ?? '',
                'subject_group'         => $record?->subject_group ?? '',
                'days_since_enrollment' => $issuedAt ? (int) $issuedAt->diffInDays(now()) : 0,
            ];
        });

        $pdf = Pdf::loadView('print.exam-gap-report', [
            'students'     => $rows,
            'activeYear'   => $activeYear,
            'school'       => $this->scopedSchool(),
            'generated_at' => now(),
        ]);
        $pdf->setPaper('a4', 'landscape');

        $token = substr(md5(now()->timestamp . $schoolId), 0, 10);

        return $pdf->download("exam-gap-report-{$token}.pdf");
    }

    public function enrollmentChallan(Invoice $challan)
    {
        $this->ensureSchoolOwnsChallan($challan);
        $challan->load(['school.district', 'academicYear']);

        $pdf = Pdf::loadView('print.enrollment-challan', ['challan' => $challan]);
        $pdf->setPaper('a4', 'portrait');
        
        $token = substr(md5($challan->id . config('app.key')), 0, 8);
        return $pdf->download("enrollment-challan-{$token}.pdf");
    }

    public function challanStudentList(Invoice $challan)
    {
        $this->ensureSchoolOwnsChallan($challan);
        $challan->load(['school.district', 'academicYear', 'challanStudents.student', 'challanStudents.studentAcademicRecord']);

        $pdf = Pdf::loadView('print.challan-student-list', ['challan' => $challan]);
        $pdf->setPaper('a4', 'portrait');

        $token = substr(md5($challan->id . config('app.key')), 0, 8);
        return $pdf->download("challan-student-list-{$token}.pdf");
    }

    public function examChallan(Invoice $challan)
    {
        $this->ensureSchoolOwnsChallan($challan);
        // Placeholder
        return response('Exam Challan Print View');
    }

    public function admissionSlip(Student $student)
    {
        $this->ensureSchoolOwnsStudent($student);
        // Reused by examFormPdf
        abort(404);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function ensureSchoolOwnsStudent(Student $student): void
    {
        if ($student->school_id !== $this->schoolScopeId()) {
            abort(403, 'Unauthorized scope.');
        }
    }

    private function ensureSchoolOwnsExamForm(ExamForm $examForm): void
    {
        $examForm->loadMissing('student');
        if ($examForm->student->school_id !== $this->schoolScopeId()) {
            abort(403, 'Unauthorized scope.');
        }
    }

    private function ensureSchoolOwnsChallan(Invoice $challan): void
    {
        if ($challan->school_id !== $this->schoolScopeId()) {
            abort(403, 'Unauthorized scope.');
        }
    }
}
