@extends('layouts.superadmin')

@section('title', 'System Activity Log')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">System Audit & Activity Log</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Immutable trail of administrative actions, fee adjustments, and security events.</p>
        </div>
        <div>
            <span class="sa-badge sa-badge-gold">AUDIT LOG</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="sa-paper p-4">
        <form method="GET" action="{{ route('superadmin.activity-log') }}" class="d-flex gap-3 flex-wrap align-end">
            <div style="flex: 1; min-width: 200px;">
                <label class="sa-form-label">Search Description</label>
                <input type="text" name="search" class="sa-input" placeholder="Keyword in action..." value="{{ request('search') }}">
            </div>
            <div style="flex: 1; min-width: 140px;">
                <label class="sa-form-label">Date From</label>
                <input type="date" name="date_from" class="sa-input" value="{{ request('date_from') }}">
            </div>
            <div style="flex: 1; min-width: 140px;">
                <label class="sa-form-label">Date To</label>
                <input type="date" name="date_to" class="sa-input" value="{{ request('date_to') }}">
            </div>
            <div>
                <button type="submit" class="sa-btn sa-btn-gold">Filter Logs</button>
            </div>
            @if(request()->anyFilled(['search', 'date_from', 'date_to']))
            <div>
                <a href="{{ route('superadmin.activity-log') }}" class="sa-btn sa-btn-outline">Reset</a>
            </div>
            @endif
        </form>
    </div>

    <!-- Log Table -->
    <div class="sa-paper p-5">
        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th style="width: 48px;">Type</th>
                        <th>Action Description</th>
                        <th>User</th>
                        <th>Target Model</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $act)
                    @php
                        $desc = strtolower($act['description'] ?? '');
                        $color = '#3b82f6'; // default blue
                        if (str_contains($desc, 'verif') || str_contains($desc, 'approv')) {
                            $color = '#10b981'; // green
                        } elseif (str_contains($desc, 'delete') || str_contains($desc, 'reject') || str_contains($desc, 'fail')) {
                            $color = '#ef4444'; // red
                        } elseif (str_contains($desc, 'setting') || str_contains($desc, 'override')) {
                            $color = '#f59e0b'; // amber
                        } elseif (str_contains($desc, 'year') || str_contains($desc, 'session')) {
                            $color = '#C8960C'; // gold
                        }
                    @endphp
                    <tr>
                        <td class="text-center">
                            <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: {{ $color }}; box-shadow: 0 0 6px {{ $color }}66;"></span>
                        </td>
                        <td>
                            <div class="font-semibold text-slate-800">{{ $act['description'] }}</div>
                        </td>
                        <td>
                            <div class="font-bold text-slate-700 font-mono text-xs">{{ $act['user'] }}</div>
                        </td>
                        <td>
                            <span class="sa-badge sa-badge-info">{{ $act['subject_type'] ?: 'General' }}</span>
                        </td>
                        <td class="text-xs text-slate-500 font-mono">
                            {{ $act['created_at'] ? \Carbon\Carbon::parse($act['created_at'])->format('M d, Y h:i A') : '-' }}
                            <div class="text-[10px] text-slate-400">{{ $act['created_at'] ? \Carbon\Carbon::parse($act['created_at'])->diffForHumans() : '' }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-slate-400">No activity log entries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $activities->links() }}
        </div>
    </div>
</div>
@endsection
