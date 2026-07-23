<?php

namespace App\Modules\Admin\Services;

use App\Models\ExamForm;
use Illuminate\Support\Facades\DB;

/**
 * Assigns seat numbers to exam-eligible students
 */
class SeatAssignmentService
{
    /**
     * Run seat number allotment for stuck or newly confirmed candidates.
     */
    public function assign(int $yearId, string $classLevel, int $startSequence, ?int $centerId = null): int
    {
        return DB::transaction(function () use ($yearId, $classLevel, $startSequence, $centerId) {
            $query = ExamForm::with('student')
                ->where('academic_year_id', $yearId)
                ->where('class_level', $classLevel)
                ->where('status', ExamForm::STATUS_CONFIRMED)
                ->whereNull('seat_number');

            if ($centerId) {
                $query->where('exam_center_id', $centerId);
            }

            // Order by student school_id and name so school candidates are sorted together
            $forms = $query->get()->sortBy(function ($form) {
                return ($form->student?->school_id ?? 0) . '_' . ($form->student?->full_name ?? '');
            });

            $count = 0;
            foreach ($forms as $form) {
                $form->update([
                    'seat_number' => (string) $startSequence,
                    'seat_number_assigned_at' => now(),
                ]);
                $startSequence++;
                $count++;
            }

            return $count;
        });
    }
}
