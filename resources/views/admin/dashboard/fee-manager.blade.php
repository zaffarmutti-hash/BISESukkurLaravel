@extends('layouts.superadmin')

@php
    $title             = 'Finance Officer Dashboard';
    $breadcrumbSection = 'Finance';
    $breadcrumbCurrent = 'Fee Monitoring';
@endphp

@section('content')
<div class="sa-animate-fade-up" style="display: flex; flex-direction: column; gap: 24px;">
    <div style="background: linear-gradient(135deg, #1B3A6B 0%, #0f172a 100%); border-radius: 16px; padding: 24px 28px; color: #fff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 8px 24px rgba(27,58,107,0.3);">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(200, 150, 12, 0.2); border: 1px solid #C8960C; color: #f1c40f; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; padding: 3px 12px; border-radius: 9999px; margin-bottom: 8px;">
                Finance & Fee Monitoring
            </div>
            <h1 style="font-size: 24px; font-weight: 800; margin: 0;">Finance Dashboard</h1>
            <p style="font-size: 13.5px; color: rgba(255,255,255,0.7); margin: 4px 0 0 0;">
                Verified collections, pending confirmations, and recent transaction activity from the system ledger.
            </p>
        </div>
        <a href="{{ route('superadmin.dashboard') }}" class="sa-header-btn" style="background: linear-gradient(135deg, #C8960C, #b08209); color: #fff; border: none; padding: 10px 20px; font-weight: 800; text-decoration: none; border-radius: 8px;">
            Return to overview &rarr;
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
        <div class="sa-panel" style="border-left: 4px solid #10b981; padding: 22px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Verified revenue</div>
            <div style="font-size: 36px; font-weight: 800; color: #10b981; margin-top: 4px;">Rs {{ number_format($verifiedPaymentsRs, 2) }}</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Confirmed receipts in the current year</div>
        </div>

        <div class="sa-panel" style="border-left: 4px solid #f59e0b; padding: 22px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Pending confirmations</div>
            <div style="font-size: 36px; font-weight: 800; color: #f59e0b; margin-top: 4px;">{{ number_format($pendingVerifications) }}</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Challans awaiting verification</div>
        </div>

        <div class="sa-panel" style="border-left: 4px solid #1B3A6B; padding: 22px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Open approvals</div>
            <div style="font-size: 36px; font-weight: 800; color: #1B3A6B; margin-top: 4px;">{{ number_format($pendingApprovalsCount) }}</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Invoices awaiting action</div>
        </div>
    </div>

    <div class="sa-panel">
        <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">Recent ledger activity</h3>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($recentActivity->take(8) as $entry)
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 12px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <div>
                        <div style="font-size: 13.5px; font-weight: 700; color: #0f172a;">{{ $entry['description'] }}</div>
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 3px;">By {{ $entry['user'] }}</div>
                    </div>
                    <span style="font-size: 11.5px; font-family: monospace; color: #94a3b8;">{{ $entry['created_at']?->diffForHumans() ?? 'recently' }}</span>
                </div>
            @empty
                <div style="text-align: center; color: #94a3b8; padding: 28px;">No payment ledger events have been recorded.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
