<?php

namespace App\Http\Controllers\DistrictAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $district = $user->district;
        $yearId = AcademicYear::current()?->id;

        $totalSchools = $district->schools()->count();
        $activeSchools = $district->schools()->where('is_active', true)->count();

        $totalStudents = $district->schools()->withCount('students')->get()->sum('students_count');

        $verifiedAmount = 0;
        $pendingVerifications = 0;

        $schools = $district->schools()->with([
            'students',
            'invoices'
        ])->get()->map(function ($school) use ($yearId) {
            $totalStudents = $school->students->count();
            $examFormCount = $school->students->whereNotNull('exam_form_submitted_at')->count();
            $pendingVerification = $school->invoices()->where('status', 'paid')->count();
            $verifiedAmount = $school->invoices()->where('status', 'verified')->sum('amount');

            return [
                'id' => $school->id,
                'name' => $school->name,
                'semis_code' => $school->username,
                'total_students' => $totalStudents,
                'exam_form_count' => $examFormCount,
                'verified_amount' => $verifiedAmount,
                'pending_verification' => $pendingVerification,
                'enrollment_open' => true,
                'exam_open' => true,
            ];
        });

        $schoolAdminCount = User::where('district_id', $district->id)->where('role', 'school_admin')->count();
        $missingExamForms = $totalStudents - $schools->sum('exam_form_count');

        $announcements = [
            [
                'id' => 1,
                'title' => 'Enrollment Window Open',
                'content' => 'Enrollment window is now open for the current academic year. Please ensure all schools complete their enrollments on time.',
                'created_at' => '2024-06-01 10:00:00',
                'is_unread' => true,
            ],
            [
                'id' => 2,
                'title' => 'Exam Form Submission Deadline',
                'content' => 'The deadline for exam form submission is approaching. Please remind schools to submit all forms by the due date.',
                'created_at' => '2024-05-28 14:30:00',
                'is_unread' => false,
            ],
        ];

        return Inertia::render('district/Dashboard', [
            'district' => ['name' => $district->name],
            'total_schools' => $totalSchools,
            'active_schools' => $activeSchools,
            'total_students' => $totalStudents,
            'verified_amount' => $verifiedAmount,
            'pending_verifications' => $pendingVerifications,
            'missing_exam_forms' => $missingExamForms,
            'school_admin_count' => $schoolAdminCount,
            'schools' => $schools,
            'announcements' => $announcements,
        ]);
    }
}
