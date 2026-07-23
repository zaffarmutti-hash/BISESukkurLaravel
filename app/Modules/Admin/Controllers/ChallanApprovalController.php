<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Modules\Admin\Services\ChallanApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ChallanApprovalController extends Controller
{
    public function __construct(private ChallanApprovalService $approvalService) {}

    public function pendingEnrollment(Request $request): Response
    {
        $query = Invoice::with(['school.district', 'approvedBy'])
            ->where('invoice_type', 'enrollment');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->where('status', 'submitted');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('school', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        return Inertia::render('admin/enrollment/PendingChallans', [
            'challans' => $query->latest()->paginate(15)->withQueryString(),
            'filters'  => $request->only(['status', 'search']),
        ]);
    }

    public function pendingExamination(Request $request): Response
    {
        $query = Invoice::with(['school.district', 'approvedBy'])
            ->where('invoice_type', 'examination');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->where('status', 'submitted');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('school', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        return Inertia::render('admin/examination/PendingExamChallans', [
            'challans' => $query->latest()->paginate(15)->withQueryString(),
            'filters'  => $request->only(['status', 'search']),
        ]);
    }

    public function show(Invoice $challan): Response
    {
        $challan->load([
            'school.district', 
            'invoiceStudents.student', 
            'invoiceStudents.studentAcademicRecord',
            'approvedBy'
        ]);

        return Inertia::render('admin/enrollment/ChallanDetail', [
            'challan' => $challan,
        ]);
    }

    public function confirm(Request $request, Invoice $challan)
    {
        $this->approvalService->confirm($challan);

        activity('challan')
            ->causedBy(Auth::user())
            ->performedOn($challan)
            ->withProperties(['challan_number' => $challan->invoice_number])
            ->log("Invoice {$challan->invoice_number} confirmed");

        $redirectRoute = $challan->invoice_type === 'enrollment' 
            ? 'superadmin.invoice-verification' 
            : 'superadmin.exam-invoice-verification';

        return redirect()->route($redirectRoute)->with('success', 'Payment verified and records updated successfully.');
    }

    public function reject(Request $request, Invoice $challan)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $this->approvalService->reject($challan, $request->rejection_reason);

        activity('challan')
            ->causedBy(Auth::user())
            ->performedOn($challan)
            ->withProperties([
                'challan_number' => $challan->invoice_number,
                'reason' => $request->rejection_reason
            ])
            ->log("Invoice {$challan->invoice_number} rejected");

        $redirectRoute = $challan->invoice_type === 'enrollment' 
            ? 'superadmin.invoice-verification' 
            : 'superadmin.exam-invoice-verification';

        return redirect()->route($redirectRoute)->with('success', 'Invoice payment rejected successfully.');
    }
}
