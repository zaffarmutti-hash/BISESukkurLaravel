<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestSchoolSeeder extends Seeder
{
    public function run(): void
    {
        // Create test school
        $tehsil = \App\Models\Tehsil::where('name', 'Sukkur')->first();

        $school = School::firstOrCreate(
            ['username' => 'SUK1-001-00001'],
            [
                'district_id' => 1,
                'tehsil_id' => $tehsil?->id,
                'name' => 'Test High School',
                'type' => 'school',
                'gender' => 'mixed',
                'principal_name' => 'Test Principal',
                'address' => '123 Test Street, Test City',
                'phone' => '0300-1234567',
                'email' => 'test@school.edu.pk',
                'is_active' => true,
            ]
        );

        // Create test school admin user
        $user = User::firstOrCreate(
            ['email' => 'school@test.com'],
            [
                'username' => 'school@test.com',
                'school_id' => $school->id,
                'name' => 'Test School Admin',
                'password' => Hash::make('password123'),
                'role' => 'school_admin',
                'is_active' => true,
            ]
        );
        $user->assignRole('school_admin');

        $this->command->info('Test school created: ' . $school->name);
        $this->command->info('Test school admin created: school@test.com / password123');
    }
}
