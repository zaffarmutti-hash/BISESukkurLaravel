@extends('layouts.superadmin')

@php
    $title             = 'Data Entry Dashboard';
    $breadcrumbSection = 'Operations';
    $breadcrumbCurrent = 'Data Entry';
@endphp

@section('content')
<div class="sa-animate-fade-up" style="display: flex; flex-direction: column; gap: 24px;">
    <div style="background: linear-gradient(135deg, #1B3A6B 0%, #0f172a 100%); border-radius: 16px; padding: 24px 28px; color: #fff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 8px 24px rgba(27,58,107,0.3);">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(200, 150, 12, 0.2); border: 1px solid #C8960C; color: #f1c40f; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; padding: 3px 12px; border-radius: 9999px; margin-bottom: 8px;">
                Operations Console
            </div>
            <h1 style="font-size: 24px; font-weight: 800; margin: 0;">Data Entry Dashboard</h1>
            <p style="font-size: 13.5px; color: rgba(255,255,255,0.7); margin: 4px 0 0 0;">
                Registration, verification, and academic record activity drawn from the live system data.
            </p>
        </div>
        <a href="{{ route('superadmin.dashboard') }}" class="sa-header-btn" style="background: linear-gradient(135deg, #C8960C, #b08209); color: #fff; border: none; padding: 10px 20px; font-weight: 800; text-decoration: none; border-radius: 8px;">
            Open overview &rarr;
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
        <div class="sa-panel" style="border-left: 4px solid #10b981; padding: 22px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Students in scope</div>
            <div style="font-size: 34px; font-weight: 800; color: #10b981; margin-top: 4px;">{{ number_format($totalStudents) }}</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Current-year academic registrations</div>
        </div>

        <div class="sa-panel" style="border-left: 4px solid #1B3A6B; padding: 22px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Participating schools</div>
            <div style="font-size: 34px; font-weight: 800; color: #1B3A6B; margin-top: 4px;">{{ number_format($totalSchools) }}</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Schools active in the current cycle</div>
        </div>

        <div class="sa-panel" style="border-left: 4px solid #f59e0b; padding: 22px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Approvals waiting</div>
            <div style="font-size: 34px; font-weight: 800; color: #f59e0b; margin-top: 4px;">{{ number_format($pendingApprovalsCount) }}</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Pending submissions requiring review</div>
        </div>
    </div>

    <div class="sa-panel">
        <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">Latest action log</h3>
        <div style="overflow-x: auto;">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivity->take(8) as $entry)
                        <tr>
                            <td style="font-family: monospace; font-size: 12px; color: #64748b;">{{ $entry['created_at']?->format('d-M-Y H:i') ?? '—' }}</td>
                            <td>{{ $entry['user'] }}</td>
                            <td style="font-weight: 600; color: #0f172a;">{{ $entry['description'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8; padding: 24px;">No submission activity has been recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
