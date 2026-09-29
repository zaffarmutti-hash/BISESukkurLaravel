<?php

namespace App\Services;

use App\Contracts\IVerificationService;
use App\Models\ExamForm;
use App\Models\Invoice;
use App\Models\StudentAcademicRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificationService implements IVerificationService
{
    public function __construct(
        private EnrollmentNumberService $enrollmentNumberService
    ) {}

    /**
     * Atomically verify an invoice and trigger enrollment number allotment for enrollment invoices.
     *
     * @return array{success: bool, invoice: Invoice, allotted_count: int, failures: array}
     */
    public function verifyInvoice(int $invoiceId, int $verifiedByUserId): array
    {
        return DB::transaction(function () use ($invoiceId, $verifiedByUserId) {
            // Step 1: Load invoice with all necessary relationships
            $invoice = Invoice::with(['school.district', 'academicYear', 'invoiceStudents.student'])
                ->lockForUpdate()
                ->findOrFail($invoiceId);

            if (in_array($invoice->status, ['confirmed', 'verified'])) {
                throw new \RuntimeException("Invoice {$invoice->invoice_number} is already verified.");
            }

            // Step 2: Update invoice record
            $invoice->update([
                'status'      => 'confirmed', // 'confirmed' represents verified state in system
                'approved_by' => $verifiedByUserId,
                'approved_at' => now(),
            ]);

            $allottedCount = 0;
            $failures = [];

            // Step 3 & 4: Check invoice type and execute appropriate trigger
            if ($invoice->invoice_type === 'enrollment') {
                // Run automatic enrollment number allotment for all included students
                $allotmentResult = $this->enrollmentNumberService->allotForInvoice($invoice);
                $allottedCount = $allotmentResult['allotted_count'];
                $failures = $allotmentResult['failures'];
            } elseif ($invoice->invoice_type === 'examination') {
                // Confirm examination forms without triggering enrollment allotment
                foreach ($invoice->invoiceStudents->where('is_included', true) as $item) {
                    ExamForm::where('student_academic_record_id', $item->student_academic_record_id)
                        ->where('academic_year_id', $invoice->academic_year_id)
                        ->update([
                            'status'       => 'confirmed',
                            'confirmed_at' => now(),
                        ]);
                }
            }

            // Step 5 & 6: Log activity
            $causer = User::find($verifiedByUserId);
            if (function_exists('activity')) {
                activity('invoice_verification')
                    ->causedBy($causer)
                    ->performedOn($invoice)
                    ->withProperties([
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_type'   => $invoice->invoice_type,
                        'allotted_count' => $allottedCount,
                        'total_students' => $invoice->student_count,
                        'total_amount'   => $invoice->total_amount_paisas / 100,
                    ])
                    ->log("Verified invoice {$invoice->invoice_number} with {$allottedCount} enrollment numbers allotted.");
            }

            return [
                'success'        => true,
                'invoice'        => $invoice,
                'allotted_count' => $allottedCount,
                'failures'       => $failures,
            ];
        });
    }

    /**
     * Atomically reject an invoice with a required reason.
     */
    public function rejectInvoice(int $invoiceId, int $rejectedByUserId, string $reason): void
    {
        if (strlen(trim($reason)) < 20) {
            throw new \InvalidArgumentException("A detailed rejection reason of at least 20 characters is required.");
        }

        DB::transaction(function () use ($invoiceId, $rejectedByUserId, $reason) {
            $invoice = Invoice::with(['invoiceStudents'])->lockForUpdate()->findOrFail($invoiceId);

            if (in_array($invoice->status, ['confirmed', 'verified'])) {
                throw new \RuntimeException("Cannot reject invoice {$invoice->invoice_number} because it has already been verified.");
            }

            $invoice->update([
                'status'           => 'rejected',
                'rejection_reason' => $reason,
                'approved_by'      => $rejectedByUserId,
                'approved_at'      => now(),
            ]);

            // Revert included students back to 'final' status so they can be included in future challans
            if ($invoice->invoice_type === 'enrollment') {
                foreach ($invoice->invoiceStudents as $item) {
                    if ($item->is_included) {
                        StudentAcademicRecord::where('id', $item->student_academic_record_id)
                            ->update(['status' => StudentAcademicRecord::STATUS_FINAL]);
                    }
                }
            } elseif ($invoice->invoice_type === 'examination') {
                foreach ($invoice->invoiceStudents as $item) {
                    if ($item->is_included) {
                        ExamForm::where('student_academic_record_id', $item->student_academic_record_id)
                            ->where('academic_year_id', $invoice->academic_year_id)
                            ->update(['status' => 'final', 'rejection_reason' => $reason]);
                    }
                }
            }

            $causer = User::find($rejectedByUserId);
            if (function_exists('activity')) {
                activity('invoice_verification')
                    ->causedBy($causer)
                    ->performedOn($invoice)
                    ->withProperties([
                        'invoice_number' => $invoice->invoice_number,
                        'reason'         => $reason,
                    ])
                    ->log("Rejected invoice {$invoice->invoice_number}: {$reason}");
            }
        });
    }
}
