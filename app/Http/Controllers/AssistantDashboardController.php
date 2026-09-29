<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Invoice;
use Illuminate\Contracts\View\View;

class AssistantDashboardController extends Controller
{
    public function index(): View
    {
        $year = AcademicYear::current();
        abort_unless($year, 503, 'No active academic year is configured.');

        $pendingInvoicesQuery = Invoice::query()
            ->where('academic_year_id', $year->id)
            ->where('status', 'submitted');

        $pendingInvoiceCount = (clone $pendingInvoicesQuery)->count();
        $pendingInvoices = (clone $pendingInvoicesQuery)
            ->with('school:id,name')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $verifiedToday = Invoice::query()
            ->where('academic_year_id', $year->id)
            ->where('approved_by', auth()->id())
            ->whereIn('status', ['confirmed', 'verified'])
            ->whereDate('approved_at', today())
            ->count();

        return view('admin.dashboard.assistant', [
            'activeYear' => $year,
            'pendingInvoiceCount' => $pendingInvoiceCount,
            'pendingInvoices' => $pendingInvoices,
            'verifiedToday' => $verifiedToday,
        ]);
    }
}