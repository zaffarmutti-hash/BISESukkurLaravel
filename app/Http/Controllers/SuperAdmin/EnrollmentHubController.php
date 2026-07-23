<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolException;
use App\Services\DashboardStatsService;
use Inertia\Inertia;
use Inertia\Response;

class EnrollmentHubController extends Controller
{
    public function __construct(private DashboardStatsService $stats) {}

    public function index(): Response
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        return Inertia::render('superadmin/hubs/EnrollmentHub', [
            'activeYear' => $year,
            'stats'      => [
                'pending_verification'       => $this->stats->pendingEnrollmentVerification($yearId),
                'pending_enrollment_numbers' => $this->stats->pendingEnrollmentNumbers($yearId),
                'verified_payments'        => $this->stats->verifiedPaymentsAmount($yearId),
                'total_students'           => $this->stats->totalStudents($yearId),
            ],
            'active_exceptions' => SchoolException::with(['school:id,name,username', 'grantedBy:id,name'])
                ->where('is_active', true)
                ->whereNull('revoked_at')
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
                ->whereIn('exception_type', [
                    'extend_enrollment_deadline',
                    'allow_record_edit_after_lock',
                    'allow_challan_resubmission',
                ])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
