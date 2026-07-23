<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DistrictSeeder::class,
            TehsilSeeder::class,
            AcademicYearSeeder::class,
            DefaultFeeCategoriesSeeder::class,
            SuperAdminSeeder::class,
            TestSchoolSeeder::class,
        ]);
    }
}
