<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ExamForm;
use App\Models\Invoice;
use App\Models\StudentAcademicRecord;

class DashboardController extends Controller
{
    public function index(): \Illuminate\Contracts\View\View
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        // 1. Total Enrollment — real count for this year only (null if no active year)
        $totalEnrollment = $yearId
            ? StudentAcademicRecord::where('academic_year_id', $yearId)->count()
            : null;

        // 2. Total Exams — real count for this year only (null if no active year)
        $totalExams = $yearId
            ? ExamForm::where('academic_year_id', $yearId)->count()
            : null;

        // 3. Fees Paid
        $feesPaidPaisas = Invoice::whereIn('status', ['confirmed', 'verified'])
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->sum('total_amount_paisas');
        $feesPaid = (float) ($feesPaidPaisas / 100);

        // 4. Fees Payable
        $feesPayablePaisas = Invoice::whereIn('status', ['submitted', 'pending', 'draft', 'unpaid'])
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->sum('total_amount_paisas');
        $feesPayable = (float) ($feesPayablePaisas / 100);

        return view('superadmin.dashboard', [
            'totalEnrollment' => $totalEnrollment,
            'totalExams'      => $totalExams,
            'feesPaid'        => $feesPaid,
            'feesPayable'     => $feesPayable,
            'activeYear'      => $year,
        ]);
    }
}
