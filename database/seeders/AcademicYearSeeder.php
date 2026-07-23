<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = (int) now()->year;

        $year = AcademicYear::updateOrCreate(
            ['year_start' => $currentYear],
            [
                'label' => (string) $currentYear,
                'year_start' => $currentYear,
                'year_end' => $currentYear,
                'is_active' => true,
                'enrollment_open_date' => "{$currentYear}-07-01",
                'enrollment_close_date' => "{$currentYear}-09-30",
                'enrollment_window_open' => true,
                'examination_open_date' => "{$currentYear}-12-01",
                'examination_close_date' => "{$currentYear}-12-31",
                'examination_window_open' => false,
            ]
        );

        AcademicYear::query()
            ->whereKeyNot($year->id)
            ->update(['is_active' => false]);

        $this->command->info("Current academic year active: {$year->label}");
    }
}
