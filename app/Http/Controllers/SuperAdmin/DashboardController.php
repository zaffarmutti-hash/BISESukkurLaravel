<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\School;
use App\Models\StudentAcademicRecord;
use App\Services\DashboardStatsService;
use App\Services\WindowPhaseService;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardStatsService $stats,
        private WindowPhaseService $windowPhaseService
    ) {}

    public function index(): \Illuminate\Contracts\View\View
    {
        $year   = AcademicYear::current();
        $yearId = $year?->id;

        // ── Card 1: Total Students (enrolled/verified this year) ─────────────
        $totalStudents = $yearId
            ? StudentAcademicRecord::where('academic_year_id', $yearId)->count()
            : 0;

        // ── Card 2: Total Active Schools ──────────────────────────────────────
        $totalSchools = School::where('is_active', true)->count();

        // ── Card 3: Pending Verifications (Paid status invoices) ──────────────
        $pendingVerifications = $yearId
            ? Invoice::where('status', 'paid')
                ->where('academic_year_id', $yearId)
                ->count()
            : Invoice::where('status', 'paid')->count();

        // ── Card 4: Verified Payments (sum in paisas → rupees) ────────────────
        $verifiedPaymentsRs = $this->stats->verifiedPaymentsAmount($yearId);

        // ── Card 5: Pending Enrollment Numbers ───────────────────────────────
        $pendingEnrollmentNumbers = $this->stats->pendingEnrollmentNumbers($yearId);

        // ── Card 6: Enrollment-to-Exam Gap ───────────────────────────────────
        $enrollmentToExamGap = $this->stats->missingExamForms($yearId);

        // ── Card 7: District Admins Active ───────────────────────────────────
        $districtAdminsActive = $this->stats->districtAdminCount();

        // ── Card 8: System Health ─────────────────────────────────────────────
        $systemHealth = $this->stats->systemHealth();

        // ── Status Bar: Window Phases ─────────────────────────────────────────
        try {
            $enrollmentPhase = $this->windowPhaseService->resolvePhase(null, 'enrollment');
            $examPhase       = $this->windowPhaseService->resolvePhase(null, 'examination');
        } catch (\Throwable $e) {
            Log::error('[DashboardController] Window phase resolution failed: ' . $e->getMessage());
            $enrollmentPhase = ['phase' => 'closed', 'countdown' => null];
            $examPhase       = ['phase' => 'closed', 'countdown' => null];
        }

        // ── District Breakdown Table ──────────────────────────────────────────
        $districtStats = $this->stats->districtBreakdown($yearId);

        // ── Recent Activity Feed (last 20 entries) ────────────────────────────
        try {
            $recentActivity = Activity::with('causer:id,name,username')
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (Activity $a) => [
                    'id'          => $a->id,
                    'description' => $a->description,
                    'user'        => $a->causer?->name ?? 'System',
                    'log_name'    => $a->log_name ?? 'general',
                    'created_at'  => $a->created_at,
                ]);
        } catch (\Throwable $e) {
            Log::error('[DashboardController] Activity log query failed: ' . $e->getMessage());
            $recentActivity = collect();
        }

        // ── Pending Approvals Count (for badge) ──────────────────────────────
        $pendingApprovalsCount = $yearId
            ? Invoice::where('status', 'submitted')->where('academic_year_id', $yearId)->count()
            : 0;

        return view('superadmin.dashboard', [
            'activeYear'               => $year,
            // 8 stat cards
            'totalStudents'            => $totalStudents,
            'totalSchools'             => $totalSchools,
            'pendingVerifications'     => $pendingVerifications,
            'verifiedPaymentsRs'       => $verifiedPaymentsRs,
            'pendingEnrollmentNumbers' => $pendingEnrollmentNumbers,
            'enrollmentToExamGap'      => $enrollmentToExamGap,
            'districtAdminsActive'     => $districtAdminsActive,
            'systemHealth'             => $systemHealth,
            // Status bar
            'enrollmentPhase'          => $enrollmentPhase,
            'examPhase'                => $examPhase,
            // Lower sections
            'districtStats'            => $districtStats,
            'recentActivity'           => $recentActivity,
            'pendingApprovalsCount'    => $pendingApprovalsCount,
        ]);
    }
}
