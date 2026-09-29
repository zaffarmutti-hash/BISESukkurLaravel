<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Student;
use App\Services\EnrollmentNumberService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Queued batch enrollment number allotment.
 *
 * Dispatched after an invoice is confirmed OR triggered manually by SuperAdmin.
 * Processes ALL confirmed invoices missing enrollment numbers for a given year,
 * chunked in batches of 100 to prevent memory exhaustion at 20,000-student scale.
 *
 * Usage:
 *   ProcessEnrollmentNumbers::dispatch($invoiceId);          // single invoice
 *   ProcessEnrollmentNumbers::dispatch(null, $academicYearId); // full year sweep
 */
class ProcessEnrollmentNumbers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Max retry attempts before marking failed.
     * Set to 3 — retry on transient DB deadlocks.
     */
    public int $tries = 3;

    /**
     * Timeout per job run in seconds.
     * 300s (5 minutes) is safe for a 100-invoice chunk.
     */
    public int $timeout = 300;

    public function __construct(
        private readonly ?int $invoiceId     = null,
        private readonly ?int $academicYearId = null,
    ) {}

    public function handle(EnrollmentNumberService $service): void
    {
        // Scope: either a specific invoice or a full-year sweep
        $query = Invoice::with(['invoiceStudents.student', 'invoiceStudents.studentAcademicRecord'])
            ->where('invoice_type', 'enrollment')
            ->whereIn('status', ['confirmed', 'verified']);

        if ($this->invoiceId) {
            $query->where('id', $this->invoiceId);
        } elseif ($this->academicYearId) {
            $query->where('academic_year_id', $this->academicYearId);
        }

        $allotted = 0;
        $skipped  = 0;
        $errors   = 0;

        $query->chunk(100, function ($invoices) use ($service, &$allotted, &$skipped, &$errors) {
            foreach ($invoices as $invoice) {
                foreach ($invoice->invoiceStudents()->where('is_included', true)->get() as $pivot) {
                    $student = $pivot->student;
                    $record  = $pivot->studentAcademicRecord;

                    if (! $student || ! $record) {
                        $skipped++;
                        continue;
                    }

                    // Skip already allotted
                    if ($student->enrollment_number) {
                        $skipped++;
                        continue;
                    }

                    try {
                        DB::transaction(function () use ($service, $student, $record, $invoice) {
                            $service->allot(
                                student:     $student,
                                record:      $record,
                                academicYear: $invoice->academicYear,
                            );
                        });
                        $allotted++;
                    } catch (\Throwable $e) {
                        $errors++;
                        Log::error('ProcessEnrollmentNumbers: allotment failed', [
                            'student_id' => $student->id,
                            'invoice_id' => $invoice->id,
                            'error'      => $e->getMessage(),
                        ]);
                    }
                }
            }
        });

        Log::info('ProcessEnrollmentNumbers: batch complete', [
            'invoice_id'       => $this->invoiceId,
            'academic_year_id' => $this->academicYearId,
            'allotted'         => $allotted,
            'skipped'          => $skipped,
            'errors'           => $errors,
        ]);

        // Fail the job if errors exceeded 10% of total processed
        $total = $allotted + $skipped + $errors;
        if ($total > 0 && ($errors / $total) > 0.1) {
            $this->fail(new \RuntimeException(
                "ProcessEnrollmentNumbers had {$errors}/{$total} errors — above 10% threshold."
            ));
        }
    }
}
