<?php

namespace App\Services;

use App\Contracts\IPdfService;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PdfService implements IPdfService
{
    /**
     * Generate the 3-part bank challan PDF (School, Bank, Board copies on A4 portrait).
     */
    public function generateChallanPdf(Invoice $invoice): string
    {
        $invoice->loadMissing(['school.district', 'academicYear', 'invoiceStudents.student']);

        $pdf = Pdf::loadView('print.bank-challan-three-copy', [
            'invoice' => $invoice,
        ]);
        $pdf->setPaper('a4', 'portrait');

        $schoolCode = preg_replace('/[^A-Za-z0-9_-]/', '', $invoice->school->username ?? 'SCH');
        $invoiceNo = preg_replace('/[^A-Za-z0-9_-]/', '', $invoice->invoice_number);
        $date = ($invoice->created_at ?? now())->format('Ymd');
        $fileName = "Challan_{$schoolCode}_{$invoiceNo}_{$date}.pdf";
        $filePath = "challans/{$fileName}";

        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
    }

    /**
     * Generate the landscape student list PDF (A4 landscape with signatures and status).
     */
    public function generateStudentListPdf(Invoice $invoice): string
    {
        $invoice->loadMissing(['school.district', 'academicYear', 'invoiceStudents.student', 'invoiceStudents.studentAcademicRecord']);

        $pdf = Pdf::loadView('print.student-list-landscape', [
            'invoice' => $invoice,
        ]);
        $pdf->setPaper('a4', 'landscape');

        $schoolCode = preg_replace('/[^A-Za-z0-9_-]/', '', $invoice->school->username ?? 'SCH');
        $invoiceNo = preg_replace('/[^A-Za-z0-9_-]/', '', $invoice->invoice_number);
        $date = ($invoice->created_at ?? now())->format('Ymd');
        $fileName = "List_{$schoolCode}_{$invoiceNo}_{$date}.pdf";
        $filePath = "challans/{$fileName}";

        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
    }

    /**
     * Download bank challan PDF response.
     */
    public function downloadChallanPdf(Invoice $invoice): Response
    {
        $invoice->loadMissing(['school.district', 'academicYear', 'invoiceStudents.student']);

        $pdf = Pdf::loadView('print.bank-challan-three-copy', [
            'invoice' => $invoice,
        ]);
        $pdf->setPaper('a4', 'portrait');

        $schoolCode = preg_replace('/[^A-Za-z0-9_-]/', '', $invoice->school->username ?? 'SCH');
        $invoiceNo = preg_replace('/[^A-Za-z0-9_-]/', '', $invoice->invoice_number);
        $date = ($invoice->created_at ?? now())->format('Ymd');
        $fileName = "Challan_{$schoolCode}_{$invoiceNo}_{$date}.pdf";

        return $pdf->download($fileName);
    }

    /**
     * Download student list PDF response.
     */
    public function downloadStudentListPdf(Invoice $invoice): Response
    {
        $invoice->loadMissing(['school.district', 'academicYear', 'invoiceStudents.student', 'invoiceStudents.studentAcademicRecord']);

        $pdf = Pdf::loadView('print.student-list-landscape', [
            'invoice' => $invoice,
        ]);
        $pdf->setPaper('a4', 'landscape');

        $schoolCode = preg_replace('/[^A-Za-z0-9_-]/', '', $invoice->school->username ?? 'SCH');
        $invoiceNo = preg_replace('/[^A-Za-z0-9_-]/', '', $invoice->invoice_number);
        $date = ($invoice->created_at ?? now())->format('Ymd');
        $fileName = "List_{$schoolCode}_{$invoiceNo}_{$date}.pdf";

        return $pdf->download($fileName);
    }
}
