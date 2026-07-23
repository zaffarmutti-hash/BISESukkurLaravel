<?php

namespace App\Services;

use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\EnrollmentSequence;
use App\Models\StudentAcademicRecord;
use Illuminate\Support\Facades\DB;

class EnrollmentNumberService
{
    /**
     * Generate and assign a permanent enrollment number to a student.
     * This MUST be run inside a database transaction.
     */
    public function assignEnrollmentNumber(Student $student, int $yearId): string
    {
        if ($student->enrollment_number) {
            return $student->enrollment_number;
        }

        $academicYear = AcademicYear::findOrFail($yearId);
        $school = $student->school;
        
        if (!$school) {
            throw new \RuntimeException("Student does not belong to any school.");
        }

        $academicRecord = StudentAcademicRecord::where('student_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->first();

        if (!$academicRecord) {
            throw new \RuntimeException("Student has no academic record for year {$yearId}.");
        }

        $yearSuffix = substr((string) $academicYear->year_start, -2);
        $groupCode = $this->getGroupCode($academicRecord->subject_group);
        $schoolUsername = $school->username;

        // 1. Find or create the sequence row for this combination (year + school + group)
        $sequenceRecord = EnrollmentSequence::firstOrCreate(
            [
                'academic_year_id' => $yearId,
                'school_id' => $school->id,
                'subject_group' => $academicRecord->subject_group,
            ],
            [
                'next_sequence' => 1
            ]
        );

        // 2. Lock the row using lockForUpdate() to prevent concurrent race conditions
        $sequenceRecord = EnrollmentSequence::where('id', $sequenceRecord->id)
            ->lockForUpdate()
            ->first();

        $sequence = $sequenceRecord->next_sequence;

        // 3. Increment and save back
        $sequenceRecord->update([
            'next_sequence' => $sequence + 1
        ]);

        // 4. Construct the unique zero-padded 5-digit enrollment number
        $sequencePadded = str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
        $enrollmentNumber = "E{$yearSuffix}{$groupCode}{$schoolUsername}-{$sequencePadded}";

        // 5. Update student and record status
        $student->update([
            'enrollment_number' => $enrollmentNumber,
            'enrollment_number_issued_at' => now(),
        ]);

        $academicRecord->update([
            'status' => StudentAcademicRecord::STATUS_ENROLLED,
        ]);

        return $enrollmentNumber;
    }

    private function getGroupCode(string $group): string
    {
        return match (strtolower(trim($group))) {
            'science' => 'S',
            'arts' => 'A',
            'commerce' => 'C',
            'general' => 'G',
            'pre_medical' => 'PM',
            'pre_engineering' => 'PE',
            default => 'X',
        };
    }
}
