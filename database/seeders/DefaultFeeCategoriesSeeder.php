<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\FeeStructure;
use Illuminate\Database\Seeder;

class DefaultFeeCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            $activeYear = AcademicYear::first();
        }

        if (!$activeYear) {
            $this->command->warn('No academic year found to seed fee structures.');
            return;
        }

        $classLevels = ['ssc_part1', 'ssc_part2', 'hsc_part1', 'hsc_part2'];
        $studentTypes = ['fresh', 'repeater', 'private'];
        $feeTypes = ['enrollment', 'examination'];

        $count = 0;
        foreach ($classLevels as $class) {
            foreach ($studentTypes as $type) {
                foreach ($feeTypes as $fee) {
                    FeeStructure::updateOrCreate(
                        [
                            'academic_year_id' => $activeYear->id,
                            'class_level'      => $class,
                            'student_type'     => $type,
                            'fee_type'         => $fee,
                        ],
                        [
                            'amount_paisas'    => 10000, // Rs. 100
                            'is_active'        => true,
                        ]
                    );
                    $count++;
                }
            }
        }

        $this->command->info("Seeded {$count} fee structures at Rs. 100 for academic year: {$activeYear->label}");
    }
}
