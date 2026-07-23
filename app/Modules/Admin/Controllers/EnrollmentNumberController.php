<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Modules\Admin\Services\EnrollmentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class EnrollmentNumberController extends Controller
{
    public function __construct(private EnrollmentNumberService $allotmentService) {}

    public function index(Request $request): Response
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        $stats = [
            'total_students' => 0,
            'allotted'       => 0,
            'pending'        => 0,
        ];

        if ($yearId) {
            $stats['total_students'] = Student::whereHas('academicRecords', function($q) use ($yearId) {
                $q->where('academic_year_id', $yearId);
            })->count();

            $stats['allotted'] = Student::whereHas('academicRecords', function($q) use ($yearId) {
                $q->where('academic_year_id', $yearId);
            })->whereNotNull('enrollment_number')->count();

            $stats['pending'] = Student::whereNull('enrollment_number')
                ->whereHas('challanStudents.challan', function ($q) use ($yearId) {
                    $q->where('challan_type', 'enrollment')
                      ->where('status', 'confirmed')
                      ->where('academic_year_id', $yearId);
                })
                ->count();
        }

        $history = Activity::where('log_name', 'enrollment_allotment')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'description' => $a->description,
                'user'        => $a->causer?->name ?? 'System',
                'created_at'  => $a->created_at?->toDateTimeString(),
                'properties'  => $a->properties,
            ]);

        return Inertia::render('superadmin/AllotmentHistory', [
            'stats'      => $stats,
            'history'    => $history,
            'activeYear' => $year,
        ]);
    }

    public function runAllotment(Request $request)
    {
        $year = AcademicYear::current();
        if (!$year) {
            return back()->with('error', 'No active academic year configured.');
        }

        $count = $this->allotmentService->allotForYear($year->id);

        if ($count === 0) {
            return back()->with('warning', 'No pending paid candidates found awaiting enrollment numbers.');
        }

        activity('enrollment_allotment')
            ->causedBy(Auth::user())
            ->withProperties([
                'count' => $count,
                'year'  => $year->label,
            ])
            ->log("Allotted enrollment numbers to {$count} candidates for year {$year->label}");

        return back()->with('success', "Enrollment number allotment run complete. Issued {$count} new enrollment numbers.");
    }
}
