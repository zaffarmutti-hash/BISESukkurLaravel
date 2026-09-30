<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\District;
use App\Services\DashboardStatsService;
use Illuminate\Http\Request;

class ReportsAnalyticsController extends Controller
{
    public function __construct(private DashboardStatsService $stats) {}

    public function index(Request $request)
    {
        $years = AcademicYear::orderByDesc('year_start')->get(['id', 'label', 'is_active']);
        $yearId = $request->integer('academic_year_id') ?: AcademicYear::current()?->id;

        return view('superadmin.reports.analytics', [
            'years'             => $years,
            'districts'         => District::orderBy('name')->get(['id', 'name']),
            'selectedYearId'    => $yearId,
            'districtBreakdown' => $this->stats->districtBreakdown($yearId),
            'summary'           => [
                'total_students'     => $this->stats->totalStudents($yearId),
                'verified_payments'  => $this->stats->verifiedPaymentsAmount($yearId),
                'pending_verification' => $this->stats->pendingVerification($yearId),
                'missing_exam_forms'   => $this->stats->missingExamForms($yearId),
            ],
        ]);
    }
}
