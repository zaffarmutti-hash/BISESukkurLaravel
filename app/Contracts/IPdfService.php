<?php

namespace App\Contracts;

use App\Models\Invoice;
use Symfony\Component\HttpFoundation\Response;

interface IPdfService
{
    /**
     * Generate the 3-part bank challan PDF (School, Bank, Board copies on A4 portrait).
     */
    public function generateChallanPdf(Invoice $invoice): string;

    /**
     * Generate the landscape student list PDF (A4 landscape with signatures and status).
     */
    public function generateStudentListPdf(Invoice $invoice): string;

    /**
     * Download bank challan PDF response.
     */
    public function downloadChallanPdf(Invoice $invoice): Response;

    /**
     * Download student list PDF response.
     */
    public function downloadStudentListPdf(Invoice $invoice): Response;
}
