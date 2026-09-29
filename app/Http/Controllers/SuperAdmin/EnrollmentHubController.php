<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\District;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\School;
use App\Models\SchoolException;
use App\Models\Student;
use App\Models\WindowOverride;
use App\Services\DashboardStatsService;
use App\Services\WindowPhaseService;
use Illuminate\Http\Request;

class EnrollmentHubController extends Controller
{
    public function __construct(
        private DashboardStatsService $stats,
        private WindowPhaseService $windowPhaseService
    ) {}

    public function index(Request $request)
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;
        $activeTab = $request->get('tab', 'window');

        // Tab 1 Data: Window Settings
        $globalPhase = $this->windowPhaseService->resolvePhase(null, 'enrollment');
        $districts = District::orderBy('name')->get();
        $districtOverrides = WindowOverride::where('window_type', 'enrollment')
            ->where('academic_year_id', $yearId)
            ->where('scope_type', 'district')
            ->get();
        $schoolOverrides = WindowOverride::where('window_type', 'enrollment')
            ->where('academic_year_id', $yearId)
            ->where('scope_type', 'school')
            ->with('school')
            ->get();

        // Tab 2 Data: Fee Configuration
        $fees = FeeStructure::where('academic_year_id', $yearId)
            ->where('fee_type', 'enrollment')
            ->orderBy('class_level')
            ->get();

        // Tab 3 Data: Pending Verifications
        $pendingInvoices = Invoice::with(['school.district', 'invoiceStudents.student'])
            ->where('invoice_type', 'enrollment')
            ->where('status', 'submitted')
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->latest()
            ->get();

        // Tab 4 Data: Allotment History — summary stats + recent 50
        $totalAllotted   = Student::whereNotNull('enrollment_number')->count();
        $pendingAllotment = Student::whereNull('enrollment_number')
            ->whereHas('invoiceStudents.invoice', fn ($q) => $q
                ->where('invoice_type', 'enrollment')
                ->where('status', 'confirmed')
                ->when($yearId, fn ($q2) => $q2->where('academic_year_id', $yearId))
            )->count();
        $allotmentStudents = Student::with(['school.district'])
            ->whereNotNull('enrollment_number')
            ->when($yearId, fn ($q) => $q->whereHas('academicRecords', fn ($r) => $r->where('academic_year_id', $yearId)))
            ->latest('enrollment_number_issued_at')
            ->limit(50)
            ->get();

        // Tab 5 Data: Enrollment Reports
        $districtStats = $this->stats->districtBreakdown($yearId);

        // Tab 6 Data: Special Permissions
        $activeExceptions = SchoolException::with(['school:id,name,username', 'grantedBy:id,name'])
            ->where('is_active', true)
            ->whereNull('revoked_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->latest()
            ->get();

        return view('superadmin.enrollment.hub', [
            'activeYear'         => $year,
            'activeTab'          => $activeTab,
            'globalPhase'        => $globalPhase,
            'districts'          => $districts,
            'districtOverrides'  => $districtOverrides,
            'schoolOverrides'    => $schoolOverrides,
            'fees'               => $fees,
            'pendingInvoices'    => $pendingInvoices,
            'allotmentStudents'  => $allotmentStudents,
            'totalAllotted'      => $totalAllotted,
            'pendingAllotment'   => $pendingAllotment,
            'districtStats'      => $districtStats,
            'activeExceptions'   => $activeExceptions,
            'stats'              => [
                'pending_verification'       => $this->stats->pendingEnrollmentVerification($yearId),
                'pending_enrollment_numbers' => $this->stats->pendingEnrollmentNumbers($yearId),
                'verified_payments'          => $this->stats->verifiedPaymentsAmount($yearId),
                'total_students'             => $this->stats->totalStudents($yearId),
            ],
        ]);
    }
}
