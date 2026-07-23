<?php

namespace App\Modules\Admin\Services;

use App\Models\Student;
use App\Models\StudentAcademicRecord;
use Illuminate\Support\Facades\DB;

/**
 * Advances the system to a new academic year, promotes students, marks expired
 */
class YearRolloverService
{
    /**
     * Promote SSC-I and HSC-I students to SSC-II and HSC-II respectively for the next academic year.
     */
    public function promoteStudents(int $currentYearId, int $nextYearId): int
    {
        return DB::transaction(function () use ($currentYearId, $nextYearId) {
            $records = StudentAcademicRecord::where('academic_year_id', $currentYearId)
                ->whereIn('class_level', ['ssc_part1', 'hsc_part1'])
                ->where('status', StudentAcademicRecord::STATUS_ENROLLED)
                ->get();

            $count = 0;
            foreach ($records as $oldRecord) {
                // Ensure student doesn't already have a record in the next year
                $exists = StudentAcademicRecord::where('student_id', $oldRecord->student_id)
                    ->where('academic_year_id', $nextYearId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $nextClassLevel = $oldRecord->class_level === 'ssc_part1' ? 'ssc_part2' : 'hsc_part2';

                StudentAcademicRecord::create([
                    'student_id'        => $oldRecord->student_id,
                    'academic_year_id'  => $nextYearId,
                    'class_level'       => $nextClassLevel,
                    'subject_group'     => $oldRecord->subject_group,
                    'student_type'      => 'fresh',
                    'status'            => StudentAcademicRecord::STATUS_DRAFT,
                    'previous_record_id'=> $oldRecord->id,
                    'is_locked'         => false,
                ]);

                $count++;
            }

            return $count;
        });
    }

    /**
     * Marks students who completed SSC-II or HSC-II or have been inactive as expired.
     */
    public function expireStudents(int $currentYearId): int
    {
        return DB::transaction(function () use ($currentYearId) {
            // Find students who finished their part 2 exams in the current year
            $completedStudentIds = StudentAcademicRecord::where('academic_year_id', $currentYearId)
                ->whereIn('class_level', ['ssc_part2', 'hsc_part2'])
                ->pluck('student_id');

            $count = 0;
            if ($completedStudentIds->isNotEmpty()) {
                $count = Student::whereIn('id', $completedStudentIds)
                    ->where('is_expired', false)
                    ->update([
                        'is_active' => false,
                        'is_expired' => true,
                        'expired_at' => now(),
                    ]);
            }

            // Flag students as expired who have no activity records in the current or previous years (2 years inactive)
            $inactiveCount = Student::where('is_active', true)
                ->where('is_expired', false)
                ->whereDoesntHave('academicRecords', function ($q) use ($currentYearId) {
                    $q->where('academic_year_id', '>=', $currentYearId);
                })
                ->update([
                    'is_active' => false,
                    'is_expired' => true,
                    'expired_at' => now(),
                ]);

            return $count + $inactiveCount;
        });
    }
}
