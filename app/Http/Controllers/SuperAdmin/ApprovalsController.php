<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class ApprovalsController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $activeTab = $request->get('tab', 'verifications');

        try {
            // ── Tab 1: Pending Invoice Verifications ───────────────────────
            // Invoices in 'submitted' status awaiting controller approval
            $pendingVerifications = Invoice::with(['school:id,name,username', 'school.district:id,name'])
                ->where('status', 'submitted')
                ->latest()
                ->paginate(20, ['*'], 'ver_page')
                ->withQueryString();

            // ── Total Pending Count for Badge ──────────────────────────────
            $totalPending = Invoice::where('status', 'submitted')->count();

        } catch (\Throwable $e) {
            Log::error('[ApprovalsController] Failed to load approvals: ' . $e->getMessage());
            $pendingVerifications = collect();
            $totalPending = 0;
        }

        return view('superadmin.approvals.index', compact(
            'activeTab',
            'pendingVerifications',
            'totalPending'
        ));
    }
}
