<?php

namespace App\Services;

use App\Models\StudentAcademicRecord;
use Illuminate\Database\Eloquent\Collection;

class ChallanService
{
    /**
     * Get students whose academic records are 'final' and not yet included
     * in any challan for the specified academic year.
     *
     * RULE: Only 'final' status records are eligible for challan generation.
     * Draft records are explicitly excluded.
     */
    public function getEligibleStudents(
        int $schoolId,
        int $yearId,
        $classLevel,
        string $group
    ): Collection {
        $levels = is_array($classLevel) ? $classLevel : (
            $classLevel === 'matric' ? ['ssc_part1', 'ssc_part2'] : (
                $classLevel === 'intermediate' ? ['hsc_part1', 'hsc_part2'] : [$classLevel]
            )
        );

        return StudentAcademicRecord::query()
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId)->where('is_active', true))
            ->where('academic_year_id', $yearId)
            ->whereIn('class_level', $levels)
            ->where('subject_group', $group)
            ->where('status', StudentAcademicRecord::STATUS_FINAL) // ← THE KEY FILTER
            ->whereDoesntHave('invoiceStudents')
            ->with('student')
            ->get();
    }

    /**
     * Check if a student academic record is eligible for challan inclusion.
     */
    public function isEligible(StudentAcademicRecord $record): bool
    {
        return $record->isFinal()
            && ! $record->is_locked
            && $record->invoiceStudents()->doesntExist();
    }
}
