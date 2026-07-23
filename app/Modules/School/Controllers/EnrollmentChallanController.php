<?php

namespace App\Modules\School\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesSchoolScope;
use App\Models\Invoice;
use App\Models\AcademicYear;
use App\Modules\School\Services\ChallanGenerationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EnrollmentChallanController extends Controller
{
    use ResolvesSchoolScope;

    private ChallanGenerationService $generationService;

    public function __construct(ChallanGenerationService $generationService)
    {
        $this->generationService = $generationService;
    }

    public function index(Request $request): Response
    {
        $schoolId = $this->schoolScopeId();
        $activeYear = AcademicYear::current();

        $query = Invoice::where('school_id', $schoolId)->where('invoice_type', 'enrollment');

        if ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        // Transform paginated results to match the Invoice interface structure expected by InvoiceList.tsx
        $invoices->through(function ($invoice) {
            return [
                'id'             => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount'         => $invoice->total_amount_paisas / 100,
                'status'         => $invoice->status,
                'created_at'     => $invoice->created_at->toIso8601String(),
                'challan_id'     => $invoice->id,
                'fee_phase'      => $invoice->fee_phase,
                'late_fee_surcharge_paisas' => $invoice->late_fee_surcharge_paisas,
                'challan'        => [
                    'id'             => $invoice->id,
                    'challan_number' => $invoice->invoice_number,
                    'type'           => $invoice->invoice_type,
                    'total_students' => $invoice->student_count,
                    'amount'         => $invoice->total_amount_paisas / 100,
                    'status'         => $invoice->status,
                    'fee_phase'      => $invoice->fee_phase,
                    'late_fee_surcharge_paisas' => $invoice->late_fee_surcharge_paisas,
                ]
            ];
        });

        return Inertia::render('school/Challan/InvoiceList', [
            'invoices'   => $invoices,
            'activeYear' => $activeYear,
            'filters'    => $request->only(['status', 'type']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('school/Challan/InvoiceList');
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_level'  => 'required|string',
            'group'        => 'required|string',
            'student_type' => 'required|string',
            'student_ids'  => 'required|array',
        ]);

        $schoolId = $this->schoolScopeId();

        // Read the phase injected by the EnrollmentWindowOpen middleware
        $phase = $request->attributes->get('enrollment_phase', 'normal');

        try {
            $invoice = $this->generationService->generate(
                $schoolId,
                $request->input('class_level'),
                $request->input('group'),
                $request->input('student_type'),
                0,
                $request->input('student_ids'),
                $phase
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'    => true,
                    'id'         => $invoice->id,
                    'challan_no' => $invoice->invoice_number,
                    'fee_phase'  => $invoice->fee_phase,
                ]);
            }

            $message = 'Enrollment challan generated successfully.';
            if ($invoice->fee_phase === 'grace') {
                $message .= ' Late fee surcharge has been applied (grace period).';
            }

            return redirect()->route('school.enrollment.challans')->with('success', $message);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Invoice $challan): Response
    {
        if ($challan->school_id !== $this->schoolScopeId()) {
            abort(403);
        }

        return Inertia::render('school/Challan/InvoiceList', [
            'challan' => $challan,
        ]);
    }

    public function submit(Request $request, Invoice $challan)
    {
        if ($challan->school_id !== $this->schoolScopeId()) {
            abort(403);
        }

        $validated = $request->validate([
            'bank_name'      => 'required|string|max:100',
            'bank_branch'    => 'required|string|max:100',
            'bank_reference' => 'required|string|max:100',
            'payment_date'   => 'required|date',
        ]);

        $challan->update(array_merge($validated, [
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]));

        return back()->with('success', 'Challan payment details submitted successfully for board verification.');
    }
}
