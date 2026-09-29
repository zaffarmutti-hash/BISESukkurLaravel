<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student List — {{ $invoice->invoice_number }}</title>
<style>
    @page {
        size: A4 landscape;
        margin: 12mm 14mm 16mm 14mm;
    }
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    body {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-size: 8.5pt;
        color: #0f172a;
        background: #ffffff;
        line-height: 1.3;
    }
    .header-box {
        border-bottom: 2px solid #1e3a8a;
        padding-bottom: 3mm;
        margin-bottom: 4mm;
    }
    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .board-title {
        font-size: 13pt;
        font-weight: 800;
        color: #1e3a8a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .sub-title {
        font-size: 9pt;
        font-weight: 600;
        color: #475569;
    }
    .invoice-tag {
        font-family: monospace;
        font-size: 11pt;
        font-weight: 800;
        color: #1e3a8a;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        padding: 2px 8px;
        border-radius: 4px;
    }
    .meta-bar {
        display: flex;
        justify-content: space-between;
        font-size: 8pt;
        color: #334155;
        margin-top: 2mm;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 2mm 3mm;
        border-radius: 4px;
    }
    .meta-item strong {
        color: #0f172a;
    }

    table.student-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
        margin-top: 3mm;
    }
    table.student-table th {
        background: #1e3a8a;
        color: #ffffff;
        font-weight: 700;
        padding: 2.5mm 3mm;
        text-align: left;
        border: 1px solid #1e3a8a;
        font-size: 7.5pt;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    table.student-table td {
        padding: 2mm 3mm;
        border: 1px solid #cbd5e1;
        vertical-align: middle;
    }
    table.student-table tr:nth-child(even) td {
        background: #f8fafc;
    }

    /* Excluded styling */
    tr.row-excluded td {
        background: #fef2f2 !important;
        color: #94a3b8;
    }
    tr.row-excluded .student-name-text {
        text-decoration: line-through;
        color: #94a3b8;
    }

    .badge-included {
        display: inline-block;
        font-size: 6.5pt;
        font-weight: 700;
        padding: 1px 5px;
        background: #dcfce7;
        color: #166534;
        border-radius: 3px;
        text-transform: uppercase;
    }
    .badge-excluded {
        display: inline-block;
        font-size: 6.5pt;
        font-weight: 700;
        padding: 1px 5px;
        background: #fee2e2;
        color: #991b1b;
        border-radius: 3px;
        text-transform: uppercase;
    }

    .total-row td {
        background: #eff6ff !important;
        font-weight: 800;
        border-top: 2px solid #1e3a8a;
        font-size: 8.5pt;
    }

    .footer-signatures {
        margin-top: 8mm;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        page-break-inside: avoid;
    }
    .sig-box {
        border-top: 1.5px solid #0f172a;
        width: 55mm;
        text-align: center;
        padding-top: 2mm;
        font-size: 8pt;
        font-weight: 600;
        color: #334155;
    }
</style>
</head>
<body>

@php
    $items = $invoice->invoiceStudents;
    $includedCount = $items->where('is_included', true)->count();
    $excludedCount = $items->where('is_included', false)->count();
    $totalCount = $items->count();
@endphp

<!-- Header Block -->
<div class="header-box">
    <div class="header-top">
        <div>
            <div class="board-title">Board of Intermediate &amp; Secondary Education, Sukkur</div>
            <div class="sub-title">Official Student Candidate Manifest — {{ strtoupper($invoice->invoice_type) }} CHALLAN</div>
        </div>
        <div>
            <span class="invoice-tag">INVOICE: {{ $invoice->invoice_number }}</span>
        </div>
    </div>

    <div class="meta-bar">
        <div class="meta-item">Institution: <strong>{{ $invoice->school->name }} ({{ $invoice->school->username }})</strong></div>
        <div class="meta-item">District: <strong>{{ $invoice->school->district->name ?? 'Sukkur' }}</strong></div>
        <div class="meta-item">Academic Session: <strong>{{ $invoice->academicYear->label ?? '2026' }}</strong></div>
        <div class="meta-item">Class: <strong>{{ strtoupper($invoice->class_level ?? 'N/A') }}</strong></div>
        <div class="meta-item">Group: <strong>{{ ucfirst($invoice->subject_group ?? 'General') }}</strong></div>
        <div class="meta-item">Type: <strong>{{ ucfirst($invoice->student_type ?? 'Regular') }}</strong></div>
        <div class="meta-item">Date: <strong>{{ $invoice->created_at ? $invoice->created_at->format('d-M-Y') : now()->format('d-M-Y') }}</strong></div>
    </div>
</div>

<!-- Student List Table -->
<table class="student-table">
    <thead>
        <tr>
            <th style="width: 4%;">S#</th>
            <th style="width: 20%;">Student Name</th>
            <th style="width: 18%;">Father's Name</th>
            <th style="width: 10%;">Surname</th>
            <th style="width: 13%;">CNIC / B-Form</th>
            <th style="width: 14%;">Enrollment No.</th>
            <th style="width: 9%; text-align: center;">Challan Status</th>
            <th style="width: 12%; text-align: right;">Fee (PKR)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $idx => $item)
            @php
                $isInc = $item->is_included;
                $student = $item->student;
            @endphp
            <tr class="{{ $isInc ? '' : 'row-excluded' }}">
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>
                    <span class="student-name-text"><strong>{{ $student->full_name }}</strong></span>
                </td>
                <td>{{ $student->father_name }}</td>
                <td>{{ $student->surname ?? '—' }}</td>
                <td style="font-family: monospace;">{{ $student->cnic ?? $student->b_form ?? '—' }}</td>
                <td style="font-family: monospace;">{{ $student->enrollment_number ?? 'PENDING' }}</td>
                <td style="text-align: center;">
                    @if($isInc)
                        <span class="badge-included">Included</span>
                    @else
                        <span class="badge-excluded">Excluded</span>
                    @endif
                </td>
                <td style="text-align: right; font-family: monospace;">
                    @if($isInc)
                        Rs {{ number_format($item->amount_paisas / 100, 2) }}
                    @else
                        <span style="text-decoration: line-through; color: #94a3b8;">Rs {{ number_format($item->amount_paisas / 100, 2) }}</span>
                    @endif
                </td>
            </tr>
        @endforeach

        <!-- Summary Row -->
        <tr class="total-row">
            <td colspan="6" style="text-align: right;">
                TOTAL SUMMARY: Included Candidates: {{ $includedCount }} | Excluded Candidates: {{ $excludedCount }} | Total Due:
            </td>
            <td style="text-align: center;">
                <span class="badge-included">{{ $includedCount }} Active</span>
            </td>
            <td style="text-align: right; color: #1e3a8a;">
                PKR {{ number_format($invoice->total_amount_paisas / 100, 2) }}
            </td>
        </tr>
    </tbody>
</table>

<!-- Signatures & Verification -->
<div class="footer-signatures">
    <div class="sig-box">
        School In-Charge / Data Entry Operator
    </div>
    <div class="sig-box">
        Head of Institution (Principal Signature &amp; Stamp)
    </div>
    <div class="sig-box">
        BISE Sukkur Verification Officer
    </div>
</div>

<div style="margin-top: 4mm; font-size: 7pt; color: #94a3b8; text-align: right;">
    Generated on {{ now()->format('d-M-Y H:i:s') }} | Official BISE Sukkur Examination &amp; Enrollment Registry System
</div>

</body>
</html>
