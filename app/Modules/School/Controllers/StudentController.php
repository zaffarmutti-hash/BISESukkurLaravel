<?php

namespace App\Modules\School\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesSchoolScope;
use App\Http\Requests\School\EnrollmentFormRequest;
use App\Models\Student;
use App\Models\StudentAcademicRecord;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    use ResolvesSchoolScope;

    // SECURITY: All queries MUST scope to school_scope_id
    public function index(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();

        if ($request->wantsJson() || $request->ajax()) {
            $classLevel = $request->get('class_level');
            $group = $request->get('group');
            $status = $request->get('status');
            $studentType = $request->get('student_type');
            $invoiceType = $request->get('invoice_type', 'enrollment');

            if ($status === 'pending_challan' || $status === 'challan_eligible') {
                if ($invoiceType === 'examination') {
                    $levels = [];
                    if ($classLevel === 'matric') {
                        $levels = ['ssc_part1', 'ssc_part2'];
                    } elseif ($classLevel === 'intermediate') {
                        $levels = ['hsc_part1', 'hsc_part2'];
                    } else {
                        $levels = [$classLevel];
                    }

                    $records = StudentAcademicRecord::query()
                        ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId)->where('is_active', true))
                        ->where('academic_year_id', $activeYear?->id ?? 0)
                        ->whereIn('class_level', $levels)
                        ->where('subject_group', $group)
                        ->whereHas('examForms', fn ($q) => 
                            $q->where('academic_year_id', $activeYear?->id ?? 0)
                              ->where('status', 'final')
                        )
                        ->whereDoesntHave('invoiceStudents', fn ($q) => 
                            $q->whereHas('invoice', fn ($cq) => $cq->where('invoice_type', 'examination'))
                        )
                        ->with('student')
                        ->get();
                } else {
                    $challanService = new \App\Services\ChallanService();
                    $records = $challanService->getEligibleStudents(
                        $schoolId,
                        $activeYear?->id ?? 0,
                        $classLevel ?? '',
                        $group ?? ''
                    );
                }

                // Apply student type filter if passed
                if ($studentType) {
                    $mappedType = $studentType === 'regular' ? 'fresh' : $studentType;
                    $records = $records->filter(fn ($r) => $r->student_type === $mappedType);
                }

                $boardPolicyService = new \App\Services\BoardPolicyService();
                $phaseResult = $invoiceType === 'examination'
                    ? $boardPolicyService->examinationPhaseForSchool($schoolId)
                    : $boardPolicyService->enrollmentPhaseForSchool($schoolId);
                $phase = $phaseResult->phase;

                $formatted = $records->map(function ($record) use ($boardPolicyService, $activeYear, $invoiceType, $phase) {
                    $amountPaisas = $boardPolicyService->resolveFeeAmountPaisas(
                        $record->class_level,
                        $record->student_type,
                        $invoiceType,
                        $activeYear?->id,
                        $phase
                    );
                    $feeAmount = (float) ($amountPaisas / 100);

                    return [
                        'id' => $record->student->id,
                        'full_name' => $record->student->full_name,
                        'father_name' => $record->student->father_name,
                        'cnic_or_bform' => $record->student->cnic ?? $record->student->b_form,
                        'cnic' => $record->student->cnic,
                        'b_form' => $record->student->b_form,
                        'fee_amount' => $feeAmount,
                    ];
                })->values();

                return response()->json($formatted);
            }
        }

        // Build base query: students with their current academic record
        $query = Student::query()
            ->where('students.school_id', $schoolId)
            ->whereHas('currentAcademicRecord', fn ($q) =>
                $q->whereIn('status', ['final', 'pending_challan', 'challan_submitted', 'enrolled'])
            )
            ->with(['currentAcademicRecord']);

        // Apply search filter (sanitized and length-limited)
        if ($search = $request->get('search')) {
            $search = mb_substr(trim($search), 0, 100); // Limit to 100 chars
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $search); // Escape LIKE wildcards
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'ilike', "%{$search}%")
                  ->orWhere('father_name', 'ilike', "%{$search}%")
                  ->orWhere('enrollment_number', 'ilike', "%{$search}%")
                  ->orWhere('cnic', 'ilike', "%{$search}%")
                  ->orWhere('b_form', 'ilike', "%{$search}%");
            });
        }

        // Apply class_level filter
        if ($classLevel = $request->get('class_level')) {
            $query->whereHas('currentAcademicRecord', fn ($q) =>
                $q->where('class_level', $classLevel)
            );
        }

        // Apply group filter
        if ($group = $request->get('group')) {
            $query->whereHas('currentAcademicRecord', fn ($q) =>
                $q->where('subject_group', $group)
            );
        }

        // Apply status filter
        if ($status = $request->get('status')) {
            $query->whereHas('currentAcademicRecord', fn ($q) =>
                $q->where('status', $status)
            );
        }

        $students = $query->orderBy('full_name')->paginate(20)->withQueryString();

        $students->through(fn ($student) => array_merge(
            $student->toArray(),
            ['academic_record' => $student->currentAcademicRecord]
        ));

        // Stat cards — only count final+ records
        $statQuery = StudentAcademicRecord::query()
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->where('academic_year_id', $activeYear?->id)
            ->whereIn('status', ['final', 'pending_challan', 'challan_submitted', 'enrolled']);

        $sscCount = (clone $statQuery)->whereIn('class_level', ['ssc_part1', 'ssc_part2'])->count();
        $hscCount = (clone $statQuery)->whereIn('class_level', ['hsc_part1', 'hsc_part2'])->count();

        return Inertia::render('school/enrollment/EnrollmentIndex', [
            'students'   => $students,
            'filters'    => $request->only(['search', 'class_level', 'group', 'status']),
            'activeYear' => $activeYear,
            'stats'      => [
                'ssc_enrollment' => $sscCount,
                'hsc_enrollment' => $hscCount,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('school/enrollment/EnrollmentCreate', [
            'student'        => null,
            'academicRecord' => null,
            'isLocked'       => false,
        ]);
    }

    public function store(EnrollmentFormRequest $request)
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();
        $saveAs = 'final';

        if (! $activeYear) {
            return back()->with('error', 'No active academic year configured.');
        }

        return DB::transaction(function () use ($request, $schoolId, $activeYear, $saveAs) {
            // Create student record
            $student = Student::create([
                'school_id'               => $schoolId,
                'gr_number'               => $request->input('gr_number'),
                'admission_date'          => $request->input('admission_date'),
                'full_name'               => $request->input('full_name'),
                'surname'                 => $request->input('surname'),
                'father_name'             => $request->input('father_name'),
                'father_cnic'             => $request->input('father_cnic'),
                'cnic'                    => $request->input('cnic'),
                'b_form'                  => $request->input('b_form'),
                'date_of_birth'           => $request->input('date_of_birth'),
                'marks_of_identification' => $request->input('marks_of_identification'),
                'gender'                  => $request->input('gender'),
                'nationality'             => $request->input('nationality', 'Pakistani'),
                'religion'                => $request->input('religion'),
                'medium_of_instruction'   => $request->input('medium_of_instruction', 'Urdu'),
                'phone'                   => $request->input('phone'),
                'guardian_phone'          => $request->input('guardian_phone'),
                'guardian_name'           => $request->input('guardian_name'),
                'guardian_cnic'           => $request->input('guardian_cnic'),
                'address'                 => $request->input('address'),
                'postal_code'             => $request->input('postal_code'),
                'remarks'                 => $request->input('remarks'),
            ]);

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('student-photos', config('app.student_photo_disk', 'local'));
                $student->update(['photo_path' => $path]);
            }

            // Create academic record for this year with status
            StudentAcademicRecord::create([
                'student_id'       => $student->id,
                'academic_year_id' => $activeYear->id,
                'class_level'      => $request->input('class_level'),
                'subject_group'    => $request->input('subject_group'),
                'student_type'     => $request->input('student_type', 'fresh'),
                'status'           => $saveAs,
            ]);

            $message = 'Enrollment confirmed and ready for challan generation.';

            return redirect()
                ->route('school.students.index')
                ->with('success', $message);
        });
    }

    public function show(Student $student): Response
    {
        $this->ensureSchoolOwns($student);

        $student->load(['currentAcademicRecord', 'school']);

        return Inertia::render('school/enrollment/EnrollmentCreate', [
            'student'        => $student,
            'academicRecord' => $student->currentAcademicRecord,
            'isLocked'       => $this->isRecordLocked($student),
            'viewOnly'       => true,
        ]);
    }

    public function edit(Student $student): Response
    {
        $this->ensureSchoolOwns($student);

        $student->load(['currentAcademicRecord']);

        return Inertia::render('school/enrollment/EnrollmentCreate', [
            'student'        => $student,
            'academicRecord' => $student->currentAcademicRecord,
            'isLocked'       => $this->isRecordLocked($student),
        ]);
    }

    public function update(EnrollmentFormRequest $request, Student $student)
    {
        $this->ensureSchoolOwns($student);

        if ($this->isRecordLocked($student)) {
            return back()->with('error', 'This record is locked and cannot be edited.');
        }

        $saveAs = 'final';

        return DB::transaction(function () use ($request, $student, $saveAs) {
            $student->update([
                'gr_number'               => $request->input('gr_number'),
                'admission_date'          => $request->input('admission_date'),
                'full_name'               => $request->input('full_name'),
                'surname'                 => $request->input('surname'),
                'father_name'             => $request->input('father_name'),
                'father_cnic'             => $request->input('father_cnic'),
                'cnic'                    => $request->input('cnic'),
                'b_form'                  => $request->input('b_form'),
                'date_of_birth'           => $request->input('date_of_birth'),
                'marks_of_identification' => $request->input('marks_of_identification'),
                'gender'                  => $request->input('gender'),
                'nationality'             => $request->input('nationality', 'Pakistani'),
                'religion'                => $request->input('religion'),
                'medium_of_instruction'   => $request->input('medium_of_instruction', 'Urdu'),
                'phone'                   => $request->input('phone'),
                'guardian_phone'          => $request->input('guardian_phone'),
                'guardian_name'           => $request->input('guardian_name'),
                'guardian_cnic'           => $request->input('guardian_cnic'),
                'address'                 => $request->input('address'),
                'postal_code'             => $request->input('postal_code'),
                'remarks'                 => $request->input('remarks'),
            ]);

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('student-photos', config('app.student_photo_disk', 'local'));
                $student->update(['photo_path' => $path]);
            }

            // Update academic record
            $record = $student->currentAcademicRecord;
            if ($record) {
                $record->update([
                    'class_level'   => $request->input('class_level'),
                    'subject_group' => $request->input('subject_group'),
                    'student_type'  => $request->input('student_type', $record->student_type),
                    'status'        => $saveAs,
                ]);
            }

            $message = 'Enrollment confirmed and ready for challan generation.';

            return redirect()
                ->route('school.students.index')
                ->with('success', $message);
        });
    }

    // saveFinal method removed as enrollment forms are directly saved as final.

    public function destroy(Request $request, Student $student)
    {
        $this->ensureSchoolOwns($student);

        $record = $student->currentAcademicRecord;

        // Extra confirmation required for final records
        if ($record && $record->isFinal() && ! $request->boolean('confirm_final_delete')) {
            return back()->with('error', 'Final records require explicit confirmation to delete.');
        }

        if ($this->isRecordLocked($student)) {
            return back()->with('error', 'This record is locked and cannot be deleted.');
        }

        return DB::transaction(function () use ($student) {
            $student->delete(); // soft-delete

            return redirect()
                ->route('school.students.index')
                ->with('success', 'Student record deleted.');
        });
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function ensureSchoolOwns(Student $student): void
    {
        if ($student->school_id !== $this->schoolScopeId()) {
            abort(403, 'You do not have permission to access this student.');
        }
    }

    private function isRecordLocked(Student $student): bool
    {
        $record = $student->currentAcademicRecord;

        if (! $record) {
            return false;
        }

        // Locked if already in the challan lifecycle (paid/verified)
        if ($record->is_locked) {
            return true;
        }

        // Locked if enrolled (has enrollment number + confirmed challan)
        if ($record->isInChallanLifecycle()) {
            // Check if included in a paid/verified invoice
            $hasPaidInvoice = $student->invoiceStudents()
                ->whereHas('invoice', fn ($q) => $q->whereIn('status', ['confirmed']))
                ->exists();

            return $hasPaidInvoice;
        }

        return false;
    }

    private function validateForFinal(Student $student, StudentAcademicRecord $record): array
    {
        $errors = [];

        if (empty($student->date_of_birth)) {
            $errors[] = 'Date of birth is required.';
        }

        if (empty($student->cnic) && empty($student->b_form)) {
            $errors[] = 'Either CNIC or B-Form number is required.';
        }

        if (empty($student->phone) && empty($student->guardian_phone)) {
            $errors[] = 'At least one contact number is required.';
        }

        if (empty($student->address)) {
            $errors[] = 'Address is required.';
        }

        if (empty($record->student_type)) {
            $errors[] = 'Student type (fresh/repeater/private) is required.';
        }

        return $errors;
    }
}
