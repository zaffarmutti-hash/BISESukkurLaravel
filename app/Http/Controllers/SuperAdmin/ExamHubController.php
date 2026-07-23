<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolException;
use App\Services\DashboardStatsService;
use Inertia\Inertia;
use Inertia\Response;

class ExamHubController extends Controller
{
    public function __construct(private DashboardStatsService $stats) {}

    public function index(): Response
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        return Inertia::render('superadmin/hubs/ExamHub', [
            'activeYear' => $year,
            'stats'      => [
                'pending_verification' => $this->stats->pendingExamVerification($yearId),
                'missing_exam_forms' => $this->stats->missingExamForms($yearId),
                'total_students'     => $this->stats->totalStudents($yearId),
            ],
            'active_exceptions' => SchoolException::with(['school:id,name,username', 'grantedBy:id,name'])
                ->where('is_active', true)
                ->whereNull('revoked_at')
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
                ->where('exception_type', 'extend_examination_deadline')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
