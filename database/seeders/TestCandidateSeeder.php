<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentAcademicRecord;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestCandidateSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (!$school) {
            $this->command->error('No school found to attach candidates.');
            return;
        }

        $activeYear = AcademicYear::current();
        if (!$activeYear) {
            $this->command->error('No active academic year found.');
            return;
        }

        $candidates = [
            [
                'gr_number'   => 'GR-2026-01',
                'full_name'   => 'Muhammad Ali',
                'surname'     => 'Memon',
                'father_name' => 'Tariq Hussain',
                'b_form'      => '45504-1234567-1',
                'gender'      => 'male',
                'dob'         => '2010-03-12',
            ],
            [
                'gr_number'   => 'GR-2026-02',
                'full_name'   => 'Ahmed Raza',
                'surname'     => 'Soomro',
                'father_name' => 'Ghulam Raza',
                'b_form'      => '45504-2345678-3',
                'gender'      => 'male',
                'dob'         => '2010-06-25',
            ],
            [
                'gr_number'   => 'GR-2026-03',
                'full_name'   => 'Fatima',
                'surname'     => 'Shaikh',
                'father_name' => 'Abdul Sattar',
                'b_form'      => '45504-3456789-2',
                'gender'      => 'female',
                'dob'         => '2010-09-18',
            ],
            [
                'gr_number'   => 'GR-2026-04',
                'full_name'   => 'Zainab',
                'surname'     => 'Mahar',
                'father_name' => 'Zahid Hussain',
                'b_form'      => '45504-4567890-4',
                'gender'      => 'female',
                'dob'         => '2010-11-04',
            ],
            [
                'gr_number'   => 'GR-2026-05',
                'full_name'   => 'Bilal',
                'surname'     => 'Abbasi',
                'father_name' => 'Muhammad Tariq',
                'b_form'      => '45504-5678901-5',
                'gender'      => 'male',
                'dob'         => '2010-01-30',
            ],
            [
                'gr_number'   => 'GR-2026-06',
                'full_name'   => 'Hamza',
                'surname'     => 'Syed',
                'father_name' => 'Imran Shah',
                'b_form'      => '45504-6789012-7',
                'gender'      => 'male',
                'dob'         => '2010-08-14',
            ],
        ];

        foreach ($candidates as $cand) {
            $student = Student::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'gr_number' => $cand['gr_number'],
                ],
                [
                    'admission_date'         => Carbon::parse('2025-04-10'),
                    'full_name'              => $cand['full_name'],
                    'surname'                => $cand['surname'],
                    'father_name'            => $cand['father_name'],
                    'b_form'                 => $cand['b_form'],
                    'father_cnic'            => '45504-9876543-1',
                    'date_of_birth'          => Carbon::parse($cand['dob']),
                    'gender'                 => $cand['gender'],
                    'nationality'            => 'Pakistani',
                    'religion'               => 'Islam',
                    'medium_of_instruction'  => 'English',
                    'phone'                  => '0300-1234567',
                    'address'                => 'Civil Lines, Sukkur',
                    'is_active'              => true,
                    'is_expired'             => false,
                    'enrollment_number'      => null,
                ]
            );

            StudentAcademicRecord::updateOrCreate(
                [
                    'student_id'       => $student->id,
                    'academic_year_id' => $activeYear->id,
                ],
                [
                    'class_level'   => 'ssc_part1',
                    'subject_group' => 'science',
                    'student_type'  => 'fresh',
                    'status'        => StudentAcademicRecord::STATUS_FINAL,
                    'is_locked'     => false,
                ]
            );
        }

        $this->command->info("Successfully seeded " . count($candidates) . " final candidates for " . $school->name);
    }
}
