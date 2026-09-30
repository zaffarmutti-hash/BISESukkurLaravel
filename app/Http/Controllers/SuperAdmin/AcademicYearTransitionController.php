<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Services\DashboardStatsService;
use App\Modules\Admin\Services\YearRolloverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcademicYearTransitionController extends Controller
{
    public function __construct(
        private DashboardStatsService $stats,
        private YearRolloverService $rolloverService
    ) {}

    public function show()
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        return view('superadmin.academic_years.transition', [
            'activeYear' => $year,
            'years'      => AcademicYear::orderByDesc('year_start')->get(),
            'preChecks'  => [
                'pending_enrollment_verification' => $this->stats->pendingEnrollmentVerification($yearId),
                'pending_exam_verification'       => $this->stats->pendingExamVerification($yearId),
                'pending_enrollment_numbers'      => $this->stats->pendingEnrollmentNumbers($yearId),
                'missing_exam_forms'              => $this->stats->missingExamForms($yearId),
                'total_students'                  => $this->stats->totalStudents($yearId),
            ],
        ]);
    }

    public function promote(Request $request)
    {
        $request->validate([
            'next_year_id' => 'required|exists:academic_years,id',
        ]);

        $current = AcademicYear::current();
        if (!$current) {
            return response()->json(['error' => 'No active academic year found.'], 400);
        }

        $count = $this->rolloverService->promoteStudents($current->id, (int) $request->next_year_id);

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    public function expire(Request $request)
    {
        $current = AcademicYear::current();
        if (!$current) {
            return response()->json(['error' => 'No active academic year found.'], 400);
        }

        $count = $this->rolloverService->expireStudents($current->id);

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    public function swap(Request $request)
    {
        $request->validate([
            'next_year_id' => 'required|exists:academic_years,id',
            'promoted_count' => 'required|integer',
            'expired_count' => 'required|integer',
        ]);

        $current = AcademicYear::current();
        $next = AcademicYear::findOrFail($request->next_year_id);

        if ($current && $current->id === $next->id) {
            return response()->json(['error' => 'The selected year is already active.'], 400);
        }

        DB::transaction(function () use ($current, $next) {
            if ($current) {
                $current->update([
                    'is_active'               => false,
                    'enrollment_window_open'  => false,
                    'examination_window_open' => false,
                ]);
            }

            $next->update(['is_active' => true]);
        });

        activity('academic_year')
            ->causedBy(Auth::user())
            ->withProperties([
                'from_year'      => $current?->label,
                'to_year'        => $next->label,
                'promoted_count' => $request->promoted_count,
                'expired_count'  => $request->expired_count,
            ])
            ->log("Academic year transition executed: {$current?->label} -> {$next->label}");

        // Return JSON response with redirect target
        return response()->json([
            'success' => true,
            'redirect' => route('superadmin.dashboard')
        ]);
    }
}
