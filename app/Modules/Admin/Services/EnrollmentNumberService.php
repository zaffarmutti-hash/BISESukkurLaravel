<?php

namespace App\Modules\Admin\Services;

use App\Models\Student;
use App\Models\StudentAcademicRecord;
use Illuminate\Support\Facades\DB;

/**
 * Issues unique enrollment numbers using PostgreSQL sequence nextval()
 */
class EnrollmentNumberService
{
    public function allotForYear(int $yearId): int
    {
        $coreService = new \App\Services\EnrollmentNumberService();

        return DB::transaction(function () use ($yearId, $coreService) {
            $students = Student::whereNull('enrollment_number')
                ->whereHas('invoiceStudents.invoice', function($q) use ($yearId) {
                    $q->where('invoice_type', 'enrollment')
                      ->where('status', 'confirmed')
                      ->where('academic_year_id', $yearId);
                })
                ->get();

            $count = 0;
            foreach ($students as $student) {
                $coreService->assignEnrollmentNumber($student, $yearId);
                $count++;
            }

            return $count;
        });
    }
}
