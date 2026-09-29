<?php

namespace App\Modules\Admin\Controllers;

use App\Contracts\IPdfService;
use App\Contracts\IVerificationService;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\District;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChallanApprovalController extends Controller
{
    public function __construct(
        private IVerificationService $verificationService,
        private IPdfService $pdfService
    ) {}

    /**
     * Display the 2-panel Super Admin invoice verification workspace.
     */
    public function index(Request $request)
    {
        $districts = District::orderBy('name')->get();
        $activeYear = AcademicYear::current();

        $query = Invoice::with(['school.district', 'academicYear', 'approvedBy']);

        // Filter: District
        if ($request->filled('district_id')) {
            $query->whereHas('school', fn ($sq) => $sq->where('district_id', $request->district_id));
        }

        // Filter: Type
        if ($request->filled('type') && in_array(strtolower($request->type), ['enrollment', 'examination'])) {
            $query->where('invoice_type', strtolower($request->type));
        }

        // Filter: Status
        if ($request->filled('status')) {
            $status = strtolower($request->status);
            if ($status === 'pending') {
                $query->whereIn('status', ['submitted', 'pending', 'draft']);
            } elseif ($status === 'verified' || $status === 'paid') {
                $query->whereIn('status', ['confirmed', 'verified']);
            } else {
                $query->where('status', $status);
            }
        } else {
            // Default show pending submitted
            $query->whereIn('status', ['submitted', 'pending', 'draft']);
        }

        // Filter: Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('school', function ($sq) use ($search) {
                      $sq->where('name', 'ilike', "%{$search}%")
                         ->orWhere('username', 'ilike', "%{$search}%");
                  });
            });
        }

        $invoices = $query->latest('id')->paginate(20)->withQueryString();

        // Selected invoice (default to first invoice in list or query id)
        $selectedInvoiceId = $request->get('selected_id', $invoices->first()?->id);
        $selectedInvoice = null;

        if ($selectedInvoiceId) {
            $selectedInvoice = Invoice::with([
                'school.district',
                'academicYear',
                'approvedBy',
                'invoiceStudents.student',
                'invoiceStudents.studentAcademicRecord',
            ])->find($selectedInvoiceId);
        }

        // Check if request is AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'invoices'        => $invoices,
                'selectedInvoice' => $selectedInvoice,
            ]);
        }

        return view('superadmin.invoices.verify', [
            'invoices'        => $invoices,
            'selectedInvoice' => $selectedInvoice,
            'districts'       => $districts,
            'activeYear'      => $activeYear,
            'filters'         => $request->only(['district_id', 'type', 'status', 'search', 'selected_id']),
        ]);
    }

    public function transactions(Request $request)
    {
        $activeYear = AcademicYear::current();
        $query = Invoice::with(['school.district', 'academicYear'])
            ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id));

        if ($request->filled('district_id')) {
            $query->whereHas('school', fn ($q) => $q->where('district_id', $request->input('district_id')));
        }
        if ($request->filled('type')) {
            $query->where('invoice_type', $request->input('type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('school', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->latest()->paginate(25)->withQueryString();
        $districts = District::orderBy('name')->get();

        return view('superadmin.invoices.transactions', [
            'invoices'   => $invoices,
            'districts'  => $districts,
            'activeYear' => $activeYear,
        ]);
    }

    /**
     * Backward-compatible aliases for legacy routes
     */
    public function pendingEnrollment(Request $request)
    {
        return $this->transactions($request);
    }

    public function pendingExamination(Request $request)
    {
        $request->merge(['type' => 'examination']);
        return $this->transactions($request);
    }

    public function show(Invoice $challan)
    {
        return redirect()->route('superadmin.invoice-verification', ['selected_id' => $challan->id]);
    }

    /**
     * AJAX: Get full invoice detail for right panel.
     */
    public function getDetail(Invoice $challan)
    {
        $challan->load([
            'school.district',
            'academicYear',
            'approvedBy',
            'invoiceStudents.student',
            'invoiceStudents.studentAcademicRecord',
        ]);

        $includedStudents = [];
        $excludedStudents = [];

        foreach ($challan->invoiceStudents as $item) {
            $student = $item->student;
            $data = [
                'id'                => $student?->id,
                'full_name'         => $student?->full_name ?? 'Unknown',
                'father_name'       => $student?->father_name ?? '—',
                'cnic'              => $student?->cnic ?? $student?->b_form ?? '—',
                'enrollment_number' => $student?->enrollment_number ?? 'PENDING',
                'fee'               => $item->amount_paisas / 100,
                'is_included'       => (bool) $item->is_included,
            ];

            if ($item->is_included) {
                $includedStudents[] = $data;
            } else {
                $excludedStudents[] = $data;
            }
        }

        return response()->json([
            'id'                     => $challan->id,
            'invoice_number'         => $challan->invoice_number,
            'invoice_type'           => $challan->invoice_type,
            'class_level'            => strtoupper($challan->class_level ?? 'SSC'),
            'subject_group'          => ucfirst($challan->subject_group ?? 'General'),
            'student_type'           => ucfirst($challan->student_type ?? 'Regular'),
            'academic_session'       => $challan->academicYear?->label ?? '2026',
            'school_name'            => $challan->school->name,
            'school_code'            => $challan->school->username,
            'district_name'          => $challan->school->district?->name ?? 'Sukkur',
            'student_count'          => $challan->student_count,
            'total_amount'           => $challan->total_amount_paisas / 100,
            'fee_phase'              => $challan->fee_phase ?? 'normal',
            'status'                 => $challan->status,
            'rejection_reason'       => $challan->rejection_reason,
            'created_at'             => $challan->created_at?->format('d-M-Y H:i'),
            'approved_at'            => $challan->approved_at?->format('d-M-Y H:i'),
            'approved_by'            => $challan->approvedBy?->name,
            'included_students'      => $includedStudents,
            'excluded_students'      => $excludedStudents,
            'download_challan_url'   => route('superadmin.invoices.download-challan', $challan->id),
            'download_list_url'      => route('superadmin.invoices.download-list', $challan->id),
        ]);
    }

    /**
     * Confirm/Verify invoice and trigger allotment if enrollment.
     */
    public function confirm(Request $request, Invoice $challan)
    {
        try {
            $result = $this->verificationService->verifyInvoice($challan->id, Auth::id());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'        => true,
                    'allotted_count' => $result['allotted_count'],
                    'failures'       => $result['failures'],
                    'message'        => "Invoice {$challan->invoice_number} verified successfully." .
                        ($result['allotted_count'] > 0 ? " {$result['allotted_count']} enrollment numbers allotted." : ""),
                ]);
            }

            $msg = "Invoice {$challan->invoice_number} verified successfully.";
            if ($result['allotted_count'] > 0) {
                $msg .= " Issued {$result['allotted_count']} official enrollment numbers.";
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            Log::error("Failed to verify invoice {$challan->id}: " . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject invoice with mandatory reason.
     */
    public function reject(Request $request, Invoice $challan)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:20|max:1000',
        ]);

        try {
            $this->verificationService->rejectInvoice($challan->id, Auth::id(), $validated['rejection_reason']);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Invoice {$challan->invoice_number} rejected successfully.",
                ]);
            }

            return back()->with('success', "Invoice {$challan->invoice_number} rejected successfully.");
        } catch (\Throwable $e) {
            Log::error("Failed to reject invoice {$challan->id}: " . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk verify selected invoices.
     */
    public function bulkVerify(Request $request)
    {
        $validated = $request->validate([
            'invoice_ids'   => 'required|array|min:1',
            'invoice_ids.*' => 'required|integer',
        ]);

        $verifiedCount = 0;
        $allottedTotal = 0;
        $failedList = [];

        foreach ($validated['invoice_ids'] as $id) {
            try {
                $result = $this->verificationService->verifyInvoice($id, Auth::id());
                $verifiedCount++;
                $allottedTotal += $result['allotted_count'];
            } catch (\Throwable $e) {
                $failedList[] = [
                    'id'    => $id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success'        => true,
            'verified_count' => $verifiedCount,
            'allotted_total' => $allottedTotal,
            'failed_count'   => count($failedList),
            'failed_items'   => $failedList,
        ]);
    }

    /**
     * Download Challan PDF for Super Admin.
     */
    public function downloadChallan(Invoice $challan)
    {
        return $this->pdfService->downloadChallanPdf($challan);
    }

    /**
     * Download Student List PDF for Super Admin.
     */
    public function downloadStudentList(Invoice $challan)
    {
        return $this->pdfService->downloadStudentListPdf($challan);
    }
}
