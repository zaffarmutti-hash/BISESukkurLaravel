<?php

namespace App\Http\Controllers\DistrictAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Invoice;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $district = $user->district;
        $yearId = AcademicYear::current()?->id;

        $totalSchools = $district->schools()->count();
        $activeSchools = $district->schools()->where('is_active', true)->count();
        $totalStudents = $district->schools()->withCount('students')->get()->sum('students_count');

        $verifiedAmount = Invoice::whereHas('school', fn ($q) => $q->where('district_id', $district->id))
            ->whereIn('status', ['confirmed', 'verified'])
            ->sum('total_amount_paisas') / 100;

        $pendingVerifications = Invoice::whereHas('school', fn ($q) => $q->where('district_id', $district->id))
            ->whereIn('status', ['submitted', 'pending', 'paid'])
            ->count();

        $schools = $district->schools()->with([
            'students',
            'invoices'
        ])->get()->map(function ($school) {
            $totalStudents = $school->students->count();
            $examFormCount = $school->students->whereNotNull('exam_form_submitted_at')->count();
            $pendingVerification = $school->invoices()->whereIn('status', ['submitted', 'pending', 'paid'])->count();
            $verifiedAmount = $school->invoices()->whereIn('status', ['confirmed', 'verified'])->sum('total_amount_paisas') / 100;

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
        $missingExamForms = max(0, $totalStudents - $schools->sum('exam_form_count'));

        $announcements = [
            [
                'id' => 1,
                'title' => 'Enrollment Window Open',
                'content' => 'Enrollment window is active for the current academic session. Please ensure all district schools submit on time.',
                'created_at' => now()->subDays(2)->format('Y-m-d H:i:s'),
                'is_unread' => true,
            ],
            [
                'id' => 2,
                'title' => 'Examination Form Submission Deadline',
                'content' => 'Remind schools that normal examination fee window closure is approaching.',
                'created_at' => now()->subDays(5)->format('Y-m-d H:i:s'),
                'is_unread' => false,
            ],
        ];

        return view('district.dashboard', [
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

    public function schools(Request $request)
    {
        $district = $request->user()->district;

        $query = $district->schools()->withCount(['students', 'invoices']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active') && $request->get('is_active') !== '') {
            $query->where('is_active', (bool)$request->get('is_active'));
        }

        $schools = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('district.schools', compact('schools', 'district'));
    }

    public function schoolShow(Request $request, School $school)
    {
        $district = $request->user()->district;
        abort_if($school->district_id !== $district->id, 403, 'Unauthorized access to school outside your district.');

        $school->loadCount(['students', 'invoices']);
        $recentInvoices = $school->invoices()->latest()->limit(10)->get();
        $recentStudents = $school->students()->latest()->limit(10)->get();

        return view('district.schools_show', compact('school', 'district', 'recentInvoices', 'recentStudents'));
    }

    public function reports(Request $request)
    {
        $district = $request->user()->district;
        $activeYear = AcademicYear::current();

        $schools = $district->schools()->withCount('students')->get();
        $totalStudents = $schools->sum('students_count');

        $verifiedPaisas = Invoice::whereHas('school', fn ($q) => $q->where('district_id', $district->id))
            ->whereIn('status', ['confirmed', 'verified'])
            ->sum('total_amount_paisas');

        $pendingCount = Invoice::whereHas('school', fn ($q) => $q->where('district_id', $district->id))
            ->whereIn('status', ['submitted', 'pending', 'paid'])
            ->count();

        $stats = [
            'total_schools' => $schools->count(),
            'total_students' => $totalStudents,
            'verified_amount' => $verifiedPaisas / 100,
            'pending_invoices' => $pendingCount,
        ];

        return view('district.reports', compact('district', 'stats', 'schools', 'activeYear'));
    }

    public function announcements(Request $request)
    {
        $district = $request->user()->district;

        $announcements = Announcement::where(function ($q) use ($district) {
            $q->where('target_scope', 'all')
              ->orWhere(function ($sub) use ($district) {
                  $sub->where('target_scope', 'district')
                      ->where(function ($d) use ($district) {
                          $d->whereNull('district_id')
                            ->orWhere('district_id', $district?->id);
                      });
              });
        })->latest()->paginate(15);

        return view('district.announcements', compact('district', 'announcements'));
    }
}
