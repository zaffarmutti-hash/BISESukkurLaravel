<?php

namespace App\Modules\Admin\Services;

use App\Models\Invoice;
use App\Models\StudentAcademicRecord;
use App\Models\ExamForm;
use App\Services\EnrollmentNumberService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Confirms invoices and triggers enrollment number assignment inside DB transaction
 */
class ChallanApprovalService
{
    private EnrollmentNumberService $enrollmentService;

    public function __construct()
    {
        $this->enrollmentService = new EnrollmentNumberService();
    }

    public function confirm(Invoice $invoice)
    {
        DB::transaction(function () use ($invoice) {
            $invoice->update([
                'status' => 'confirmed',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            if ($invoice->invoice_type === 'enrollment') {
                foreach ($invoice->invoiceStudents as $cs) {
                    $student = $cs->student;
                    
                    // Assign enrollment number using our safe sequence transaction logic
                    $this->enrollmentService->assignEnrollmentNumber($student, $invoice->academic_year_id);
                }
            } elseif ($invoice->invoice_type === 'examination') {
                // Confirm exam forms
                foreach ($invoice->invoiceStudents as $cs) {
                    $academicRecord = $cs->studentAcademicRecord;
                    if ($academicRecord) {
                        // Find the ExamForm for this student academic record
                        $examForm = ExamForm::where('student_academic_record_id', $academicRecord->id)
                            ->where('academic_year_id', $invoice->academic_year_id)
                            ->first();

                        if ($examForm) {
                            $examForm->update([
                                'status' => ExamForm::STATUS_CONFIRMED,
                                'confirmed_at' => now(),
                            ]);
                        }
                    }
                }
            }
        });
    }

    public function reject(Invoice $invoice, string $reason)
    {
        DB::transaction(function () use ($invoice, $reason) {
            $invoice->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            if ($invoice->invoice_type === 'enrollment') {
                foreach ($invoice->invoiceStudents as $cs) {
                    $cs->studentAcademicRecord->update([
                        'status' => StudentAcademicRecord::STATUS_PENDING_CHALLAN,
                    ]);
                }
            } elseif ($invoice->invoice_type === 'examination') {
                foreach ($invoice->invoiceStudents as $cs) {
                    $academicRecord = $cs->studentAcademicRecord;
                    if ($academicRecord) {
                        $examForm = ExamForm::where('student_academic_record_id', $academicRecord->id)
                            ->where('academic_year_id', $invoice->academic_year_id)
                            ->first();

                        if ($examForm) {
                            $examForm->update([
                                'status' => ExamForm::STATUS_REJECTED,
                                'rejection_reason' => $reason,
                            ]);
                        }
                    }
                }
            }
        });
    }
}
