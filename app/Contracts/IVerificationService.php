<?php

namespace App\Contracts;

use App\Models\Invoice;

interface IVerificationService
{
    /**
     * Atomically verify an invoice and trigger enrollment number allotment for enrollment invoices.
     *
     * @return array{success: bool, invoice: Invoice, allotted_count: int, failures: array}
     */
    public function verifyInvoice(int $invoiceId, int $verifiedByUserId): array;

    /**
     * Atomically reject an invoice with a required reason.
     */
    public function rejectInvoice(int $invoiceId, int $rejectedByUserId, string $reason): void;
}
