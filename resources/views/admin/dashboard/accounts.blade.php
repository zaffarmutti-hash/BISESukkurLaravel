@extends('layouts.superadmin')

@php
    $title             = 'Accounts Officer Dashboard';
    $breadcrumbSection = 'Accounts';
    $breadcrumbCurrent = 'Reconciliation';
@endphp

@section('content')
<div class="sa-animate-fade-up" style="display: flex; flex-direction: column; gap: 24px;">
    <div style="background: linear-gradient(135deg, #1B3A6B 0%, #0f172a 100%); border-radius: 16px; padding: 24px 28px; color: #fff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 8px 24px rgba(27,58,107,0.3);">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(16,185,129,0.18); border: 1px solid rgba(16,185,129,0.55); color: #a7f3d0; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; padding: 3px 12px; border-radius: 9999px; margin-bottom: 8px;">
                Accounts & Reconciliation
            </div>
            <h1 style="font-size: 24px; font-weight: 800; margin: 0;">Accounts Dashboard</h1>
            <p style="font-size: 13.5px; color: rgba(255,255,255,0.7); margin: 4px 0 0 0;">
                Live fee collections, pending verification counts, and institutional participation from the database.
            </p>
        </div>
        <a href="{{ route('superadmin.dashboard') }}" class="sa-header-btn" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 10px 22px; font-weight: 800; text-decoration: none; border-radius: 8px;">
            Back to overview &rarr;
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
        <div class="sa-panel" style="border-left: 4px solid #10b981; padding: 22px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Verified collections</div>
            <div style="font-size: 34px; font-weight: 800; color: #10b981; margin-top: 4px;">Rs {{ number_format($verifiedPaymentsRs, 2) }}</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Confirmed invoice totals</div>
        </div>

        <div class="sa-panel" style="border-left: 4px solid #f59e0b; padding: 22px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Flagged challans</div>
            <div style="font-size: 34px; font-weight: 800; color: #f59e0b; margin-top: 4px;">{{ number_format($pendingVerifications) }}</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Awaiting confirmation and audit</div>
        </div>

        <div class="sa-panel" style="border-left: 4px solid #1B3A6B; padding: 22px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Active institutions</div>
            <div style="font-size: 34px; font-weight: 800; color: #1B3A6B; margin-top: 4px;">{{ number_format($totalSchools) }}</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Schools currently mapped to the active year</div>
        </div>
    </div>

    <div class="sa-panel">
        <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">Latest system activity</h3>
        <div style="overflow-x: auto;">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Action</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivity->take(8) as $entry)
                        <tr>
                            <td style="font-family: monospace; font-size: 12px; color: #64748b;">{{ $entry['created_at']?->format('d-M-Y H:i') ?? '—' }}</td>
                            <td style="font-weight: 600; color: #0f172a;">{{ $entry['description'] }}</td>
                            <td>{{ $entry['user'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8; padding: 24px;">No recent activity has been recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
