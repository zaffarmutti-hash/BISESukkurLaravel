<?php

namespace App\Contracts;

use App\Models\Student;
use App\Models\Invoice;

interface IEnrollmentNumberService
{
    /**
     * Generate and assign an official enrollment number to a student inside a locked sequence transaction.
     */
    public function assignEnrollmentNumber(
        Student $student,
        int $yearId,
        string $allotmentType = 'auto',
        ?string $reason = null,
        ?int $userId = null
    ): string;

    /**
     * Execute manual allotment for an edge case student with audit reason.
     */
    public function manualAllotment(int $studentId, int $yearId, string $reason, int $userId): string;

    /**
     * Allot enrollment numbers for all eligible included students in a verified invoice.
     *
     * @return array{allotted_count: int, failure_count: int, failures: array}
     */
    public function allotForInvoice(Invoice $invoice): array;
}
