<?php

namespace App\Services;

use App\Contracts\IEnrollmentNumberService;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\EnrollmentSequence;
use App\Models\Invoice;
use App\Models\StudentAcademicRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollmentNumberService implements IEnrollmentNumberService
{
    /**
     * Generate and assign an official enrollment number to a student inside a locked sequence transaction.
     *
     * Format: E{YY}{GroupCode}{DistrictCode}{Zone}-{SchoolCode}-{SEQ}
     * Example: E26SKH1-038-0001
     */
    public function assignEnrollmentNumber(
        Student $student,
        int $yearId,
        string $allotmentType = 'auto',
        ?string $reason = null,
        ?int $userId = null
    ): string {
        if ($student->enrollment_number) {
            return $student->enrollment_number;
        }

        $academicYear = AcademicYear::findOrFail($yearId);
        $school = $student->school;

        if (!$school) {
            throw new \RuntimeException("Student (ID: {$student->id}) does not belong to any valid school.");
        }

        $academicRecord = StudentAcademicRecord::where('student_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->first();

        if (!$academicRecord) {
            throw new \RuntimeException("Student has no academic record for academic year {$yearId}.");
        }

        // 1. Two-digit academic year (e.g. 2026 -> 26)
        $yearStart = $academicYear->year_start ?? date('Y');
        $yy = substr((string) $yearStart, -2);

        // 2. Group Code (single uppercase letter)
        $groupCode = $this->resolveGroupCode($academicRecord->subject_group);

        // 3. District Code (two uppercase letters)
        $districtCode = $this->resolveDistrictCode($school);

        // 4. Zone (single digit, default 1)
        $zone = (string) ($school->zone ?? 1);

        // 5. School Code (numeric part zero-padded to 3 digits)
        $schoolCode = $this->resolveSchoolNumericCode($school);

        // 6. Sequence Scoping: academic_year_id + school_id + subject_group
        // Find or create the sequence row
        $sequenceRecord = EnrollmentSequence::firstOrCreate(
            [
                'academic_year_id' => $yearId,
                'school_id'        => $school->id,
                'subject_group'    => $academicRecord->subject_group,
            ],
            [
                'next_sequence' => 1,
            ]
        );

        // Lock row with SELECT FOR UPDATE to serialize concurrent allotment for same scope
        $lockedRecord = EnrollmentSequence::where('id', $sequenceRecord->id)
            ->lockForUpdate()
            ->first();

        $currentSeq = $lockedRecord->next_sequence;

        // Increment sequence
        $lockedRecord->update([
            'next_sequence' => $currentSeq + 1,
        ]);

        // Pad sequence to 4 digits (e.g. 0001, 0042)
        $seqPadded = str_pad((string) $currentSeq, 4, '0', STR_PAD_LEFT);

        // Construct official enrollment number
        $enrollmentNo = "E{$yy}{$groupCode}{$districtCode}{$zone}-{$schoolCode}-{$seqPadded}";

        // Update student record
        $student->update([
            'enrollment_number'           => $enrollmentNo,
            'enrollment_number_issued_at' => now(),
            'allotment_type'              => $allotmentType,
            'allotment_reason'            => $reason,
            'allotted_by'                 => $userId,
        ]);

        // Transition student academic record status to 'enrolled'
        $academicRecord->update([
            'status' => StudentAcademicRecord::STATUS_ENROLLED,
        ]);

        return $enrollmentNo;
    }

    /**
     * Execute manual allotment for an edge-case student with mandatory reason.
     */
    public function manualAllotment(int $studentId, int $yearId, string $reason, int $userId): string
    {
        if (strlen(trim($reason)) < 30) {
            throw new \InvalidArgumentException("Manual allotment requires a detailed reason of at least 30 characters.");
        }

        $student = Student::with(['school.district'])->findOrFail($studentId);

        if ($student->hasEnrollmentNumber()) {
            throw new \RuntimeException("Student {$student->full_name} already has an enrollment number: {$student->enrollment_number}");
        }

        return DB::transaction(function () use ($student, $yearId, $reason, $userId) {
            $number = $this->assignEnrollmentNumber(
                student: $student,
                yearId: $yearId,
                allotmentType: 'manual',
                reason: $reason,
                userId: $userId
            );

            // Log activity
            if (function_exists('activity')) {
                activity('enrollment_allotment')
                    ->causedBy($userId)
                    ->performedOn($student)
                    ->withProperties([
                        'manual'            => true,
                        'enrollment_number' => $number,
                        'reason'            => $reason,
                    ])
                    ->log("Manual enrollment number {$number} allotted to student {$student->full_name}");
            }

            return $number;
        });
    }

    /**
     * Allot enrollment numbers for all eligible included students in a verified invoice.
     */
    public function allotForInvoice(Invoice $invoice): array
    {
        $invoice->loadMissing(['invoiceStudents.student', 'school.district', 'academicYear']);

        $allotted = 0;
        $failed = 0;
        $failures = [];

        $items = $invoice->invoiceStudents->where('is_included', true);

        foreach ($items as $item) {
            $student = $item->student;
            if (!$student) {
                continue;
            }

            // Gracefully skip if already allotted
            if ($student->enrollment_number) {
                continue;
            }

            try {
                $this->assignEnrollmentNumber(
                    student: $student,
                    yearId: $invoice->academic_year_id,
                    allotmentType: 'auto',
                    userId: $invoice->approved_by
                );
                $allotted++;
            } catch (\Throwable $e) {
                $failed++;
                $failures[] = [
                    'student_id'   => $student->id,
                    'student_name' => $student->full_name,
                    'error'        => $e->getMessage(),
                ];
                Log::error("Failed to allot enrollment number for student {$student->id}: " . $e->getMessage(), [
                    'exception' => $e,
                    'invoice_id' => $invoice->id,
                ]);
            }
        }

        return [
            'allotted_count' => $allotted,
            'failure_count'  => $failed,
            'failures'       => $failures,
        ];
    }

    /**
     * Map subject group to single uppercase letter code.
     * Science=S, Arts=A, Commerce=C, General=G, PreEngineering=P, PreMedical=M, Computer=T, Humanities=H, HomeEconomics=O
     */
    public function resolveGroupCode(?string $group): string
    {
        $norm = strtolower(str_replace(['_', '-', ' '], '', $group ?? 'general'));

        return match (true) {
            str_contains($norm, 'preeng')    => 'P',
            str_contains($norm, 'premed')    => 'M',
            str_contains($norm, 'comp')      => 'T',
            str_contains($norm, 'home')      => 'O',
            str_contains($norm, 'human')     => 'H',
            str_contains($norm, 'sci')       => 'S',
            str_contains($norm, 'art')       => 'A',
            str_contains($norm, 'comm')      => 'C',
            str_contains($norm, 'gen')       => 'G',
            default                          => 'G',
        };
    }

    /**
     * Map district to two-letter uppercase code.
     * Khairpur=KH, Sukkur=SK, Naushahro Feroze=NK, Ghotki=GK, Shikarpur=SB
     */
    public function resolveDistrictCode($school): string
    {
        $name = strtolower($school->district?->name ?? '');

        return match (true) {
            str_contains($name, 'khairpur')   => 'KH',
            str_contains($name, 'sukkur')     => 'SK',
            str_contains($name, 'naushahro')  => 'NK',
            str_contains($name, 'ghotki')     => 'GK',
            str_contains($name, 'shikarpur')  => 'SB',
            default                           => strtoupper(substr($school->district?->code ?? 'SK', 0, 2)),
        };
    }

    /**
     * Extract 3-digit numeric school code (e.g. "038" from "KH1-038" or school->id).
     */
    public function resolveSchoolNumericCode($school): string
    {
        // Check if username has standard pattern like KH1-038-001 or KH1-038
        if (preg_match('/-(\d{1,4})/', $school->username ?? '', $matches)) {
            return str_pad($matches[1], 3, '0', STR_PAD_LEFT);
        }

        // Fallback to numeric digits in code or school id
        $digits = preg_replace('/\D/', '', $school->username ?? '');
        if (!empty($digits)) {
            return str_pad(substr($digits, -3), 3, '0', STR_PAD_LEFT);
        }

        return str_pad((string) $school->id, 3, '0', STR_PAD_LEFT);
    }
}
