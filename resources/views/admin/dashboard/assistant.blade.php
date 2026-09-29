@extends('layouts.assistant')

@section('title', 'Assistant Controller Dashboard')

@section('content')
<div class="sa-animate-fade-up" style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Status Banner -->
    <div style="background: linear-gradient(135deg, #1B3A6B 0%, #0f172a 100%); border-radius: 16px; padding: 24px 28px; color: #ffffff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 8px 24px rgba(27,58,107,0.3);">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(200, 150, 12, 0.2); border: 1px solid #C8960C; color: #f1c40f; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; padding: 3px 12px; border-radius: 9999px; margin-bottom: 8px;">
                DESK VERIFICATION OFFICER
            </div>
            <h1 style="font-size: 24px; font-weight: 800; margin: 0;">Assistant Controller Workspace</h1>
            <p style="font-size: 13.5px; color: rgba(255,255,255,0.7); margin: 4px 0 0 0;">
                Review institution challans, verify student batches, and submit endorsements for statutory Controller sign-off.
            </p>
        </div>

        <div>
            <a href="#pending-invoices" class="sa-header-btn" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 10px 22px; font-weight: 800; text-decoration: none; border-radius: 8px; box-shadow: 0 4px 12px rgba(16,185,129,0.35);">
                Review Submitted Invoices &rarr;
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
        <div class="sa-panel" style="padding: 24px; background: linear-gradient(135deg, #effcf7, #fbfefd);">
            <div style="font-size: 12px; font-weight: 800; color: #64748b; text-transform: uppercase;">Invoices confirmed today</div>
            <div style="font-size: 36px; font-weight: 800; color: #0f172a; margin-top: 6px;">{{ number_format($verifiedToday) }}</div>
            <div style="font-size: 13px; color: #64748b; margin-top: 4px;">For {{ $activeYear->label }}, under your account</div>
        </div>
        <div class="sa-panel" style="padding: 24px; background: linear-gradient(135deg, #eff6ff, #fbfcff);">
            <div style="font-size: 12px; font-weight: 800; color: #64748b; text-transform: uppercase;">Submitted invoices awaiting review</div>
            <div style="font-size: 36px; font-weight: 800; color: #0f172a; margin-top: 6px;">{{ number_format($pendingInvoiceCount) }}</div>
            <div style="font-size: 13px; color: #64748b; margin-top: 4px;">For active academic year {{ $activeYear->label }}</div>
        </div>
    </div>

    <div class="sa-panel" id="pending-invoices">
        <h2 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">Submitted Invoices Awaiting Review</h2>

        <table class="sa-table">
                <thead>
                    <tr>
                        <th>Challan / Invoice</th>
                        <th>Institution</th>
                        <th class="text-right">Amount</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
            <tbody>
                @forelse ($pendingInvoices as $invoice)
                    <tr>
                        <td><code>{{ $invoice->invoice_number }}</code></td>
                        <td>{{ $invoice->school?->name ?? 'School not assigned' }}</td>
                        <td class="text-right" style="font-weight: 700;">Rs {{ number_format($invoice->total_amount_paisas / 100, 2) }}</td>
                        <td class="text-center"><span class="sa-chip sa-chip-gold">Submitted</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding: 24px; color: #64748b; text-align: center;">No submitted invoices are awaiting review for this academic year.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
