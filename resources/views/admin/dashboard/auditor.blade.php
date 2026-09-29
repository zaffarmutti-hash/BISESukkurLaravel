@extends('layouts.superadmin')

@php
    $title             = 'Audit Officer Dashboard';
    $breadcrumbSection = 'Audit';
    $breadcrumbCurrent = 'Compliance';
    $systemHealthy = $systemHealth['healthy'] ?? false;
@endphp

@section('content')
<div class="sa-animate-fade-up" style="display: flex; flex-direction: column; gap: 24px;">
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 16px; padding: 24px 28px; color: #fff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1);">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #fca5a5; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; padding: 3px 12px; border-radius: 9999px; margin-bottom: 8px;">
                Internal Audit & Compliance
            </div>
            <h1 style="font-size: 24px; font-weight: 800; margin: 0;">Audit Dashboard</h1>
            <p style="font-size: 13.5px; color: rgba(255,255,255,0.7); margin: 4px 0 0 0;">
                Live monitoring of financial confirmations, enrollment gaps, and system health from the database.
            </p>
        </div>
        <a href="{{ route('superadmin.dashboard') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: 1px solid rgba(255,255,255,0.2); padding: 10px 20px; font-weight: 800; text-decoration: none; border-radius: 8px;">
            Review overview &rarr;
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px;">
        <div class="sa-panel" style="border-left: 4px solid #10b981; padding: 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">System health</div>
            <div style="font-size: 28px; font-weight: 800; color: #10b981; margin-top: 4px;">{{ $systemHealthy ? 'Healthy' : 'Review' }}</div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Database, queue, and storage checks</div>
        </div>

        <div class="sa-panel" style="border-left: 4px solid #ef4444; padding: 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Enrollment gaps</div>
            <div style="font-size: 28px; font-weight: 800; color: #ef4444; margin-top: 4px;">{{ number_format($enrollmentToExamGap) }}</div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Students without registered exam forms</div>
        </div>

        <div class="sa-panel" style="border-left: 4px solid #f59e0b; padding: 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Pending verifications</div>
            <div style="font-size: 28px; font-weight: 800; color: #f59e0b; margin-top: 4px;">{{ number_format($pendingVerifications) }}</div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Items awaiting approval or audit clearance</div>
        </div>

        <div class="sa-panel" style="border-left: 4px solid #3b82f6; padding: 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Current year</div>
            <div style="font-size: 28px; font-weight: 800; color: #3b82f6; margin-top: 4px;">{{ $activeYear?->name ?? 'N/A' }}</div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Active academic year in scope</div>
        </div>
    </div>

    <div class="sa-panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Recent activity stream</h3>
        </div>

        <div style="overflow-x: auto;">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivity->take(10) as $ra)
                        <tr>
                            <td style="font-family: monospace; font-size: 12px; color: #64748b;">{{ $ra['created_at']?->format('d-M-Y H:i:s') ?? '—' }}</td>
                            <td style="font-weight: 700; color: #0f172a;">{{ $ra['user'] }}</td>
                            <td><span class="sa-chip sa-chip-blue">{{ strtoupper($ra['log_name']) }}</span></td>
                            <td style="color: #334155;">{{ $ra['description'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #94a3b8; padding: 24px;">No activity logs are available for the current year.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
