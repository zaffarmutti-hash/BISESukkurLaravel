<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Challan — {{ $invoice->invoice_number }}</title>
<style>
    @page {
        size: A4 portrait;
        margin: 6mm 10mm;
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
        line-height: 1.25;
    }
    .challan-copy {
        height: 89mm;
        max-height: 89mm;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 2mm 0;
    }
    .cut-line {
        height: 6mm;
        text-align: center;
        font-size: 7.5pt;
        color: #64748b;
        letter-spacing: 2px;
        line-height: 6mm;
        border-top: 1px dashed #94a3b8;
        border-bottom: 1px dashed #94a3b8;
        margin: 1mm 0;
    }
    .header {
        text-align: center;
        border-bottom: 1.5px solid #1e3a8a;
        padding-bottom: 1.5mm;
        margin-bottom: 1.5mm;
    }
    .board-title {
        font-size: 11pt;
        font-weight: 800;
        letter-spacing: 0.5px;
        color: #1e3a8a;
        text-transform: uppercase;
    }
    .school-title {
        font-size: 8.5pt;
        font-weight: 600;
        color: #334155;
    }
    .copy-banner {
        display: inline-block;
        font-size: 7pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 1px 6px;
        border-radius: 3px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        margin-top: 1px;
    }
    .grid-info {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2mm;
    }
    .grid-info td {
        vertical-align: top;
        font-size: 8pt;
        padding: 1px 3px;
    }
    .col-left {
        width: 55%;
    }
    .col-right {
        width: 45%;
        text-align: right;
    }
    .info-label {
        color: #64748b;
        font-weight: 500;
    }
    .info-val {
        font-weight: 700;
        color: #0f172a;
    }
    .invoice-chip {
        font-family: monospace;
        font-size: 10pt;
        font-weight: 800;
        color: #1e3a8a;
        letter-spacing: 1px;
    }
    .phase-badge {
        font-size: 7pt;
        font-weight: 700;
        padding: 1px 4px;
        border-radius: 2px;
        display: inline-block;
    }
    .phase-normal { background: #dcfce7; color: #166534; }
    .phase-grace { background: #fef3c7; color: #92400e; }

    /* Student mini-table */
    table.mini-students {
        width: 100%;
        border-collapse: collapse;
        font-size: 7pt;
        margin-bottom: 1.5mm;
    }
    table.mini-students th {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        padding: 1.5px 3px;
        text-align: left;
        font-weight: 700;
        color: #475569;
    }
    table.mini-students td {
        border: 1px solid #e2e8f0;
        padding: 1px 3px;
    }
    table.mini-students tr:nth-child(even) {
        background: #fdfdfd;
    }

    /* Deposit Box */
    .deposit-box {
        border: 1.5px solid #1e3a8a;
        background: #f8fafc;
        border-radius: 4px;
        padding: 2mm 3mm;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5mm;
    }
    .deposit-label {
        font-size: 7.5pt;
        font-weight: 600;
        color: #475569;
        text-transform: uppercase;
    }
    .deposit-amount {
        font-size: 13pt;
        font-weight: 900;
        font-family: monospace;
        color: #1e3a8a;
    }

    .copy-footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        font-size: 7pt;
        color: #64748b;
        padding-top: 1mm;
    }
    .sign-line {
        border-top: 1px solid #94a3b8;
        width: 35mm;
        text-align: center;
        padding-top: 1px;
    }
    .bold-tag {
        font-weight: 800;
        font-size: 8pt;
        color: #1e293b;
    }
</style>
</head>
<body>

@php
    $copies = [
        ['label' => 'BANK COPY', 'sub' => 'For Bank Cashier / Record', 'color' => '#1e40af'],
        ['label' => 'BOARD COPY', 'sub' => 'To be returned to BISE Sukkur', 'color' => '#047857'],
        ['label' => 'SCHOOL COPY', 'sub' => 'Retain in Institution Office', 'color' => '#b45309'],
    ];

    $includedStudents = $invoice->invoiceStudents->where('is_included', true)->values();
    if ($includedStudents->isEmpty()) {
        $includedStudents = $invoice->invoiceStudents; // fallback
    }
    $totalCount = $includedStudents->count();
    $maxMini = 10;
    $displayedStudents = $includedStudents->take($maxMini);
    $remainingCount = max(0, $totalCount - $maxMini);
    $feePerStudent = $totalCount > 0 ? ($invoice->total_amount_paisas / 100 / $totalCount) : 0;
@endphp

@foreach($copies as $idx => $copy)
    <div class="challan-copy">
        <div>
            <!-- Header -->
            <div class="header">
                <div class="board-title">BOARD OF INTERMEDIATE &amp; SECONDARY EDUCATION, SUKKUR</div>
                <div class="school-title">{{ $invoice->school->name }} (Code: {{ $invoice->school->username }})</div>
                <div style="margin-top: 2px;">
                    <span class="copy-banner" style="border-color: {{ $copy['color'] }}; color: {{ $copy['color'] }}; font-weight: 800;">
                        {{ $copy['label'] }} — {{ strtoupper($invoice->invoice_type) }} CHALLAN
                    </span>
                </div>
            </div>

            <!-- Info Grid -->
            <table class="grid-info">
                <tr>
                    <td class="col-left">
                        <div><span class="info-label">District:</span> <span class="info-val">{{ $invoice->school->district->name ?? 'Sukkur' }}</span> | <span class="info-label">Session:</span> <span class="info-val">{{ $invoice->academicYear->label ?? '2026' }}</span></div>
                        <div><span class="info-label">Class:</span> <span class="info-val">{{ strtoupper($invoice->class_level ?? 'N/A') }}</span> | <span class="info-label">Group:</span> <span class="info-val">{{ ucfirst($invoice->subject_group ?? 'General') }}</span></div>
                        <div><span class="info-label">Student Type:</span> <span class="info-val">{{ ucfirst($invoice->student_type ?? 'Regular') }}</span> | <span class="info-label">Students:</span> <span class="info-val">{{ $totalCount }} Candidates</span></div>
                    </td>
                    <td class="col-right">
                        <div><span class="info-label">Challan Ref:</span> <span class="invoice-chip">{{ $invoice->invoice_number }}</span></div>
                        <div><span class="info-label">Generated:</span> <span class="info-val">{{ $invoice->created_at ? $invoice->created_at->format('d-M-Y') : now()->format('d-M-Y') }}</span></div>
                        <div>
                            <span class="info-label">Fee Phase:</span> 
                            @if(($invoice->fee_phase ?? 'normal') === 'grace')
                                <span class="phase-badge phase-grace">Grace / Late Fee</span>
                            @else
                                <span class="phase-badge phase-normal">Normal Phase</span>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Mini Table -->
            <table class="mini-students">
                <thead>
                    <tr>
                        <th style="width: 6%;">#</th>
                        <th style="width: 44%;">Candidate Name</th>
                        <th style="width: 32%;">Father Name</th>
                        <th style="width: 18%; text-align: right;">Amount (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($displayedStudents as $sIdx => $item)
                        <tr>
                            <td>{{ $sIdx + 1 }}</td>
                            <td><strong>{{ $item->student->full_name }}</strong></td>
                            <td>{{ $item->student->father_name }}</td>
                            <td style="text-align: right;">Rs {{ number_format($item->amount_paisas / 100, 2) }}</td>
                        </tr>
                    @endforeach
                    @if($remainingCount > 0)
                        <tr>
                            <td colspan="4" style="text-align: center; font-style: italic; background: #f8fafc; color: #475569;">
                                ... and {{ $remainingCount }} more candidate(s) — see attached comprehensive Student List PDF ...
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div>
            <!-- Deposit Box -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 2mm;">
                <tr>
                    <td style="border: 1.5px solid #1e3a8a; background: #eff6ff; padding: 2mm 3mm; width: 65%;">
                        <div class="deposit-label">Total Amount to Deposit at Designated Bank (ABL/HBL/NBP)</div>
                        <div style="font-size: 7pt; color: #64748b;">Deposit in A/C: BISE Sukkur General Exam Collection</div>
                    </td>
                    <td style="border: 1.5px solid #1e3a8a; background: #eff6ff; padding: 2mm 3mm; width: 35%; text-align: right;">
                        <div class="deposit-amount">PKR {{ number_format($invoice->total_amount_paisas / 100, 2) }}</div>
                    </td>
                </tr>
            </table>

            <!-- Footer Signatures -->
            <div class="copy-footer">
                <div>
                    <div>Challan: <strong style="font-family: monospace;">{{ $invoice->invoice_number }}</strong></div>
                    <div class="bold-tag">{{ $copy['label'] }}</div>
                </div>
                <div class="sign-line">
                    Depositor / Principal Sign
                </div>
                <div class="sign-line">
                    Authorized Bank Officer
                </div>
            </div>
        </div>
    </div>

    @if($idx < 2)
        <div class="cut-line">
            ✂ - - - - - - - - - - - - - - - - - - - - - - - - - CUT HERE - - - - - - - - - - - - - - - - - - - - - - - - - ✂
        </div>
    @endif
@endforeach

</body>
</html>
