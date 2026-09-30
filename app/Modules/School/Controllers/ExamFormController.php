<?php

namespace App\Modules\School\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesSchoolScope;
use App\Models\AcademicYear;
use App\Models\ExamForm;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExamFormController extends Controller
{
    use ResolvesSchoolScope;

    // SECURITY: All queries MUST scope to school_scope_id
    public function index(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $school = $this->scopedSchool();
        $activeYear = AcademicYear::current();
        $tab = $request->get('tab', 'draft');

        $query = ExamForm::query()
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->with(['student', 'studentAcademicRecord']);

        if ($tab === 'draft') {
            $query->where('status', ExamForm::STATUS_DRAFT);
        } else {
            $query->whereIn('status', [
                ExamForm::STATUS_FINAL,
                ExamForm::STATUS_SUBMITTED,
                ExamForm::STATUS_CONFIRMED,
                ExamForm::STATUS_REJECTED,
            ]);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('student', fn ($q) =>
                $q->where('full_name', 'ilike', "%{$search}%")
                  ->orWhere('enrollment_number', 'ilike', "%{$search}%")
                  ->orWhere('cnic', 'ilike', "%{$search}%")
            );
        }

        if ($classLevel = $request->get('class_level')) {
            if (in_array($classLevel, ['ssc', 'hsc'], true)) {
                $levels = $classLevel === 'ssc'
                    ? ['ssc_part1', 'ssc_part2']
                    : ['hsc_part1', 'hsc_part2'];
                $query->whereHas('studentAcademicRecord', fn ($q) => $q->whereIn('class_level', $levels));
            } else {
                $query->whereHas('studentAcademicRecord', fn ($q) => $q->where('class_level', $classLevel));
            }
        }

        if ($group = $request->get('group')) {
            $query->whereHas('studentAcademicRecord', fn ($q) => $q->where('subject_group', $group));
        }

        if ($status = $request->get('status')) {
            if ($status === 'paid') {
                $query->where('status', ExamForm::STATUS_CONFIRMED);
            } elseif ($status === 'unpaid') {
                $query->whereIn('status', [ExamForm::STATUS_DRAFT, ExamForm::STATUS_FINAL, ExamForm::STATUS_SUBMITTED]);
            } else {
                $query->where('status', $status);
            }
        }

        $examForms = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $baseCount = ExamForm::query()
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId));

        if ($activeYear) {
            $baseCount->where('academic_year_id', $activeYear->id);
        }

        $draftCount = (clone $baseCount)->where('status', ExamForm::STATUS_DRAFT)->count();
        $finalCount = (clone $baseCount)->whereIn('status', [
            ExamForm::STATUS_FINAL,
            ExamForm::STATUS_SUBMITTED,
            ExamForm::STATUS_CONFIRMED,
            ExamForm::STATUS_REJECTED,
        ])->count();

        $statsQuery = (clone $baseCount);
        $sscCount = (clone $statsQuery)->whereHas('studentAcademicRecord', fn ($q) =>
            $q->whereIn('class_level', ['ssc_part1', 'ssc_part2'])
        )->count();
        $hscCount = (clone $statsQuery)->whereHas('studentAcademicRecord', fn ($q) =>
            $q->whereIn('class_level', ['hsc_part1', 'hsc_part2'])
        )->count();
        $feesPaid = (clone $statsQuery)->where('status', ExamForm::STATUS_CONFIRMED)->count();
        $feesUnpaid = (clone $statsQuery)->whereIn('status', [ExamForm::STATUS_DRAFT, ExamForm::STATUS_FINAL, ExamForm::STATUS_SUBMITTED])->count();
        $gapCount = $this->gapStudentsQuery($schoolId, $activeYear?->id)->count();

        return view('school.examination.index', [
            'examForms'  => $examForms,
            'filters'    => $request->only(['search', 'class_level', 'group', 'status', 'tab']),
            'activeYear' => $activeYear,
            'tab'        => $tab,
            'draftCount' => $draftCount,
            'finalCount' => $finalCount,
            'stats'      => [
                'ssc_count'   => $sscCount,
                'hsc_count'   => $hscCount,
                'fees_paid'   => $feesPaid,
                'fees_unpaid' => $feesUnpaid,
                'gap_count'   => $gapCount,
            ],
            'school' => $school,
        ]);
    }

    public function create(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $mode = $request->get('mode', 'new');

        if ($request->filled('enrollment_number')) {
            $student = Student::query()
                ->where('school_id', $schoolId)
                ->where('enrollment_number', $request->get('enrollment_number'))
                ->firstOrFail();

            return $this->renderCreateForm($student);
        }

        if ($request->filled('student_id')) {
            $student = Student::findOrFail($request->integer('student_id'));
            $this->ensureSchoolOwns($student);

            return $this->renderCreateForm($student);
        }

        $eligibleStudents = $this->eligibleStudentsForExamForm($schoolId, $mode);

        return view('school.examination.select_student', [
            'mode'       => $mode,
            'students'   => $eligibleStudents,
            'activeYear' => AcademicYear::current(),
        ]);
    }

    public function store(Request $request)
    {
        $activeYear = AcademicYear::current();
        $saveAs = $request->input('save_as', 'draft');

        $request->validate([
            'student_id'                 => ['required', 'integer', 'exists:students,id'],
            'student_academic_record_id' => ['required', 'integer', 'exists:student_academic_records,id'],
            'exam_center_id'             => ['nullable', 'integer', 'exists:exam_centers,id'],
            'save_as'                    => ['required', 'in:draft,final'],
        ]);

        $student = Student::findOrFail($request->input('student_id'));
        $this->ensureSchoolOwns($student);

        $record = $student->currentAcademicRecord;
        if (! $record || ! in_array($record->status, ['final', 'pending_challan', 'challan_submitted', 'enrolled'])) {
            return back()->with('error', 'Student must have a finalized enrollment before creating an exam form.');
        }

        $existing = ExamForm::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $activeYear?->id)
            ->first();

        if ($existing) {
            return redirect()
                ->route('school.examination.form.edit', $existing)
                ->with('info', 'An exam form already exists for this student.');
        }

        ExamForm::create([
            'student_id'                 => $student->id,
            'academic_year_id'           => $activeYear->id,
            'student_academic_record_id' => $request->input('student_academic_record_id'),
            'exam_center_id'             => $request->input('exam_center_id'),
            'status'                     => $saveAs === 'final' ? ExamForm::STATUS_FINAL : ExamForm::STATUS_DRAFT,
        ]);

        $message = $saveAs === 'final'
            ? 'Exam form confirmed and ready for submission.'
            : 'Saved as draft. You can continue editing later.';

        return redirect()
            ->route('school.examination.forms', ['tab' => $saveAs])
            ->with('success', $message);
    }

    public function show(ExamForm $examForm)
    {
        $this->ensureSchoolOwnsExamForm($examForm);

        $examForm->load(['student.currentAcademicRecord', 'studentAcademicRecord', 'subjects', 'examCenter']);

        return view('school.examination.create', [
            'student'  => $examForm->student,
            'examForm' => $examForm,
            'isLocked' => in_array($examForm->status, ['confirmed']),
            'viewOnly' => true,
        ]);
    }

    public function edit(ExamForm $examForm)
    {
        $this->ensureSchoolOwnsExamForm($examForm);

        if (! $examForm->isDraft()) {
            return redirect()
                ->route('school.examination.form.show', $examForm)
                ->with('error', 'Only draft exam forms can be edited.');
        }

        $examForm->load(['student.currentAcademicRecord', 'studentAcademicRecord', 'subjects', 'examCenter']);

        return view('school.examination.create', [
            'student'  => $examForm->student,
            'examForm' => $examForm,
            'isLocked' => false,
        ]);
    }

    public function update(Request $request, ExamForm $examForm)
    {
        $this->ensureSchoolOwnsExamForm($examForm);

        if (! $examForm->isDraft()) {
            return back()->with('error', 'This exam form is locked and cannot be edited.');
        }

        $saveAs = $request->input('save_as', 'draft');

        $request->validate([
            'exam_center_id' => ['nullable', 'integer', 'exists:exam_centers,id'],
            'save_as'        => ['required', 'in:draft,final'],
        ]);

        $examForm->update([
            'exam_center_id' => $request->input('exam_center_id'),
            'status'         => $saveAs === 'final' ? ExamForm::STATUS_FINAL : ExamForm::STATUS_DRAFT,
        ]);

        $message = $saveAs === 'final'
            ? 'Exam form confirmed and ready for submission.'
            : 'Saved as draft. You can continue editing later.';

        return redirect()
            ->route('school.examination.forms', ['tab' => $saveAs])
            ->with('success', $message);
    }

    public function destroy(ExamForm $examForm)
    {
        $this->ensureSchoolOwnsExamForm($examForm);

        if (! $examForm->isDraft()) {
            return back()->with('error', 'Only draft exam forms can be deleted.');
        }

        $examForm->delete();

        return redirect()
            ->route('school.examination.forms')
            ->with('success', 'Exam form deleted.');
    }

    public function gapReport(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();

        $query = $this->gapStudentsQuery($schoolId, $activeYear?->id);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'ilike', "%{$search}%")
                  ->orWhere('enrollment_number', 'ilike', "%{$search}%")
                  ->orWhere('father_name', 'ilike', "%{$search}%");
            });
        }

        $students = $query->orderBy('full_name')->get();

        return view('school.examination.select_student', [
            'mode'       => 'returning',
            'students'   => $students,
            'activeYear' => $activeYear,
        ]);
    }

    public function saveFinal(ExamForm $examForm)
    {
        $this->ensureSchoolOwnsExamForm($examForm);

        if (! $examForm->isDraft()) {
            return back()->with('error', 'This exam form is not in draft status.');
        }

        $student = $examForm->student;
        $record = $student?->currentAcademicRecord;

        if (! $record || ! in_array($record->status, ['final', 'pending_challan', 'challan_submitted', 'enrolled'])) {
            return back()->with('error', 'Cannot finalize: student enrollment has not been confirmed as final.');
        }

        $examForm->markAsFinal();

        return back()->with('success', 'Exam form confirmed and moved to Final list.');
    }

    public function submit(Request $request, ExamForm $examForm)
    {
        $this->ensureSchoolOwnsExamForm($examForm);

        if (! $examForm->isFinal()) {
            return back()->with('error', 'Only finalized exam forms can be submitted.');
        }

        $examForm->update([
            'status'       => ExamForm::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        return back()->with('success', 'Exam form submitted for board review.');
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function renderCreateForm(Student $student)
    {
        $this->ensureSchoolOwns($student);
        $student->load(['currentAcademicRecord']);

        $activeYear = AcademicYear::current();
        $existing = ExamForm::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $activeYear?->id)
            ->first();

        if ($existing) {
            return redirect()
                ->route('school.examination.form.edit', $existing)
                ->with('info', 'An exam form already exists for this student.');
        }

        return view('school.examination.create', [
            'student'  => $student,
            'examForm' => null,
            'isLocked' => false,
        ]);
    }

    private function gapStudentsQuery(int $schoolId, ?int $yearId)
    {
        return Student::query()
            ->where('school_id', $schoolId)
            ->whereNotNull('enrollment_number')
            ->whereHas('currentAcademicRecord', fn ($q) =>
                $q->whereIn('status', ['final', 'pending_challan', 'challan_submitted', 'enrolled'])
            )
            ->when($yearId, fn ($q) => $q->whereDoesntHave('examForms', fn ($ef) =>
                $ef->where('academic_year_id', $yearId)
            ))
            ->with('currentAcademicRecord');
    }

    private function eligibleStudentsForExamForm(int $schoolId, string $mode)
    {
        $partLevels = $mode === 'returning'
            ? ['ssc_part2', 'hsc_part2']
            : ['ssc_part1', 'hsc_part1'];

        $activeYear = AcademicYear::current();

        return Student::query()
            ->where('school_id', $schoolId)
            ->whereHas('currentAcademicRecord', fn ($q) =>
                $q->whereIn('class_level', $partLevels)
                  ->whereIn('status', ['final', 'pending_challan', 'challan_submitted', 'enrolled'])
            )
            ->when($activeYear, fn ($q) => $q->whereDoesntHave('examForms', fn ($ef) =>
                $ef->where('academic_year_id', $activeYear->id)
            ))
            ->with('currentAcademicRecord')
            ->orderBy('full_name')
            ->get();
    }

    private function studentForFrontend(Student $student): array
    {
        $student->loadMissing('currentAcademicRecord');

        return [
            ...$student->toArray(),
            'academic_record' => $student->currentAcademicRecord,
        ];
    }

    private function schoolForFrontend(School $school): array
    {
        $type = strtolower((string) $school->type);

        return [
            ...$school->toArray(),
            'allowed_levels' => str_contains($type, 'hsc') || str_contains($type, 'inter')
                ? ['matric', 'intermediate']
                : ['matric'],
        ];
    }

    private function ensureSchoolOwns(Student $student): void
    {
        if ($student->school_id !== $this->schoolScopeId()) {
            abort(403, 'You do not have permission to access this student.');
        }
    }

    private function ensureSchoolOwnsExamForm(ExamForm $examForm): void
    {
        $examForm->loadMissing('student');
        if ($examForm->student->school_id !== $this->schoolScopeId()) {
            abort(403, 'You do not have permission to access this exam form.');
        }
    }
}
