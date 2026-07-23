<?php

namespace App\Modules\Admin\Services;

use App\Models\Certificate;
use App\Models\CertificateSubject;
use App\Models\Student;
use App\Models\ExamForm;
use App\Models\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

/**
 * Generates certificates for students who passed final year
 */
class CertificateService
{
    /**
     * Generate certificates for passing candidates in SSC Part II or HSC Part II.
     */
    public function generateForYear(int $yearId, string $level): int
    {
        // level can be 'ssc' or 'hsc'
        $classLevel = $level === 'ssc' ? 'ssc_part2' : 'hsc_part2';

        return DB::transaction(function () use ($yearId, $classLevel, $level) {
            // Find confirmed exam forms that do not have certificates issued yet
            $eligibleForms = ExamForm::with('student')
                ->where('academic_year_id', $yearId)
                ->where('class_level', $classLevel)
                ->where('status', ExamForm::STATUS_CONFIRMED)
                ->whereDoesntHave('student.certificates', function($q) use ($yearId, $level) {
                    $q->where('academic_year_id', $yearId)->where('level', $level);
                })
                ->get();

            $issuedCount = 0;

            foreach ($eligibleForms as $form) {
                $student = $form->student;
                if (!$student) continue;

                // Retrieve all results for the candidate in the active year
                $results = Result::where('student_id', $student->id)
                    ->where('academic_year_id', $yearId)
                    ->get();

                // Check if all results are entered, and none is failed
                if ($results->isEmpty()) continue;

                $allPassed = true;
                $totalMarks = 0;
                $obtainedMarks = 0;

                foreach ($results as $result) {
                    if ($result->status !== 'verified') {
                        $allPassed = false;
                        break;
                    }
                    if (!$result->is_pass) {
                        $allPassed = false;
                    }
                    $totalMarks += $result->total_marks;
                    $obtainedMarks += $result->marks_obtained;
                }

                if (!$allPassed || $totalMarks === 0) continue;

                $percentage = ($obtainedMarks / $totalMarks) * 100;
                
                // Compute Division
                if ($percentage >= 60) $division = 'First Division';
                elseif ($percentage >= 45) $division = 'Second Division';
                elseif ($percentage >= 33) $division = 'Third Division';
                else $division = 'Fail';

                // Compute Grade
                if ($percentage >= 80) $grade = 'A-1';
                elseif ($percentage >= 70) $grade = 'A';
                elseif ($percentage >= 60) $grade = 'B';
                elseif ($percentage >= 50) $grade = 'C';
                elseif ($percentage >= 40) $grade = 'D';
                elseif ($percentage >= 33) $grade = 'E';
                else $grade = 'F';

                if ($grade === 'F') continue;

                // Create certificate
                $token = bin2hex(random_bytes(16)); // Secure scannable token

                $certificate = Certificate::create([
                    'student_id'        => $student->id,
                    'academic_year_id'  => $yearId,
                    'level'             => $level,
                    'verification_token'=> $token,
                    'verification_url'  => url("/verify-certificate/{$token}"),
                    'total_percentage'  => $percentage,
                    'overall_grade'     => $grade,
                    'division'          => $division,
                    'issued_by'         => Auth::id(),
                    'issued_at'         => now(),
                    'is_issued'         => true,
                ]);

                // Create certificate subjects
                foreach ($results as $res) {
                    CertificateSubject::create([
                        'certificate_id' => $certificate->id,
                        'result_id'      => $res->id,
                        'subject_name'   => $res->subject_name,
                        'subject_code'   => $res->subject_code,
                        'total_marks'    => $res->total_marks,
                        'marks_obtained' => $res->marks_obtained,
                        'grade'          => $res->grade,
                    ]);
                }

                $issuedCount++;
            }

            return $issuedCount;
        });
    }
}
