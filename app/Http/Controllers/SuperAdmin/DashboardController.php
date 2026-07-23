<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Services\DashboardStatsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function __construct(private DashboardStatsService $stats) {}

    public function index(): Response
    {
        $yearId = AcademicYear::current()?->id;

        return Inertia::render('superadmin/Dashboard', [
            'activeYear' => AcademicYear::current(),
            'totalStudents' => Inertia::defer(fn () => $this->stats->totalStudents($yearId)),
            'schoolStats' => Inertia::defer(fn () => $this->stats->schoolStats()),
            'pendingEnrollmentVerification' => Inertia::defer(fn () => $this->stats->pendingEnrollmentVerification($yearId)),
            'pendingExamVerification' => Inertia::defer(fn () => $this->stats->pendingExamVerification($yearId)),
            'verifiedPayments' => Inertia::defer(fn () => $this->stats->verifiedPaymentsAmount($yearId)),
            'pendingEnrollmentNumbers' => Inertia::defer(fn () => $this->stats->pendingEnrollmentNumbers($yearId)),
            'missingExamForms' => Inertia::defer(fn () => $this->stats->missingExamForms($yearId)),
            'systemHealth' => Inertia::defer(fn () => $this->stats->systemHealth()),
            'districtBreakdown' => Inertia::defer(fn () => $this->stats->districtBreakdown($yearId)),
            'recentActivity' => Inertia::defer(fn () => Activity::with('causer:id,name,username')
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (Activity $a) => [
                    'id'          => $a->id,
                    'description' => $a->description,
                    'user'        => $a->causer?->name ?? 'System',
                    'username'    => $a->causer?->username,
                    'created_at'  => $a->created_at?->toDateTimeString(),
                    'subject_type'=> class_basename($a->subject_type ?? ''),
                    'properties'  => $a->properties,
                ])),
        ]);
    }
}
