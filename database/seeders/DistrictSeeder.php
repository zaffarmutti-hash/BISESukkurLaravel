<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        // The five districts served by BISE Sukkur
        $districts = [
            ['name' => 'Sukkur',            'code' => 'SUK'],
            ['name' => 'Khairpur',          'code' => 'KHP'],
            ['name' => 'Naushahro Feroze',  'code' => 'NF'],
            ['name' => 'Ghotki',            'code' => 'GHK'],
            ['name' => 'Shikarpur',         'code' => 'SKP'],
        ];

        foreach ($districts as $district) {
            District::firstOrCreate(['code' => $district['code']], $district);
        }

        $this->command->info('Districts seeded: ' . implode(', ', array_column($districts, 'name')));
    }
}
