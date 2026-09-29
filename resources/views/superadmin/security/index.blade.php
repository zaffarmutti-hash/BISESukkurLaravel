@extends('layouts.superadmin')

@php
    $title             = 'Security Dashboard';
    $breadcrumbSection = 'System';
    $breadcrumbCurrent = 'Security Dashboard';
@endphp

@section('content')
<div class="sa-animate-fade-up">

    {{-- Page Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
        <div>
            <h1 style="font-size:24px;font-weight:800;color:#0f172a;margin:0 0 4px 0;display:flex;align-items:center;gap:10px;">
                <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#ef4444,#dc2626);display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                Security Dashboard
            </h1>
            <p style="font-size:13px;color:#64748b;margin:0;">Monitor login failures, locked accounts, and suspicious activity across the system.</p>
        </div>
        <div style="display:flex;gap:10px;">
            <button onclick="window.location.reload()"
                style="background:linear-gradient(135deg,#1B3A6B,#2d5a9e);color:#fff;border:none;border-radius:10px;padding:10px 20px;font-weight:700;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                Refresh
            </button>
        </div>
    </div>

    {{-- Summary Cards Row --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px;margin-bottom:28px;">

        {{-- Card: Failed Logins Today --}}
        <div style="background:linear-gradient(135deg,#ef4444,#dc2626);border-radius:16px;padding:22px;color:#fff;position:relative;overflow:hidden;box-shadow:0 8px 24px rgba(239,68,68,.3);">
            <div style="position:absolute;right:-15px;top:-15px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.1);pointer-events:none;"></div>
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div style="font-size:36px;font-weight:800;line-height:1;margin-bottom:4px;" data-counter-target="{{ $failedLoginsToday }}">{{ number_format($failedLoginsToday) }}</div>
            <div style="font-size:12px;font-weight:600;opacity:.85;">Failed Logins Today</div>
        </div>

        {{-- Card: Locked Accounts --}}
        <div style="background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:16px;padding:22px;color:#fff;position:relative;overflow:hidden;box-shadow:0 8px 24px rgba(245,158,11,.3);">
            <div style="position:absolute;right:-15px;top:-15px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.1);pointer-events:none;"></div>
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div style="font-size:36px;font-weight:800;line-height:1;margin-bottom:4px;" data-counter-target="{{ $lockedAccounts }}">{{ number_format($lockedAccounts) }}</div>
            <div style="font-size:12px;font-weight:600;opacity:.85;">Locked Accounts</div>
        </div>

        {{-- Card: Expiring Passwords --}}
        <div style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);border-radius:16px;padding:22px;color:#fff;position:relative;overflow:hidden;box-shadow:0 8px 24px rgba(139,92,246,.3);">
            <div style="position:absolute;right:-15px;top:-15px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.1);pointer-events:none;"></div>
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
            </div>
            <div style="font-size:36px;font-weight:800;line-height:1;margin-bottom:4px;" data-counter-target="{{ $expiringPasswords }}">{{ number_format($expiringPasswords) }}</div>
            <div style="font-size:12px;font-weight:600;opacity:.85;">Inactive Users (60+ days)</div>
        </div>

        {{-- Card: Inactive Board Users --}}
        <div style="background:linear-gradient(135deg,#1B3A6B,#2d5a9e);border-radius:16px;padding:22px;color:#fff;position:relative;overflow:hidden;box-shadow:0 8px 24px rgba(27,58,107,.3);">
            <div style="position:absolute;right:-15px;top:-15px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.1);pointer-events:none;"></div>
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            </div>
            <div style="font-size:36px;font-weight:800;line-height:1;margin-bottom:4px;" data-counter-target="{{ $inactiveBoardUsers }}">{{ number_format($inactiveBoardUsers) }}</div>
            <div style="font-size:12px;font-weight:600;opacity:.85;">Inactive Board Users (30d)</div>
        </div>

    </div>

    {{-- Two Column Layout --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">

        {{-- Locked Accounts Table --}}
        <div class="sa-panel">
            <div class="sa-panel-header">
                <h3 class="sa-panel-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Currently Locked Accounts
                </h3>
                @if($lockedAccounts > 0)
                    <span style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:9999px;font-size:11px;font-weight:700;padding:2px 10px;">{{ $lockedAccounts }} locked</span>
                @endif
            </div>
            @if($lockedUsers->isEmpty())
                <div style="text-align:center;padding:36px;color:#94a3b8;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 8px;display:block;color:#10b981;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <div style="font-size:13px;font-weight:600;color:#10b981;">No locked accounts</div>
                </div>
            @else
                <table class="sa-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Attempts</th>
                            <th>Locked Until</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lockedUsers as $lu)
                        <tr>
                            <td>
                                <div style="font-weight:700;color:#0f172a;">{{ $lu->name }}</div>
                                <div style="font-size:11px;font-family:monospace;color:#64748b;">{{ $lu->username }}</div>
                            </td>
                            <td><span style="background:#fef3c7;color:#92400e;border-radius:6px;font-size:11px;font-weight:700;padding:2px 8px;">{{ ucwords(str_replace('_',' ',$lu->role)) }}</span></td>
                            <td style="color:#ef4444;font-weight:700;">{{ $lu->failed_login_attempts ?? 0 }}</td>
                            <td style="font-size:12px;color:#64748b;">{{ $lu->locked_until?->format('d-M-Y H:i') ?? '—' }}</td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('superadmin.security.unlock', $lu->id) }}" onsubmit="return confirm('Unlock account for {{ $lu->name }}?');">
                                    @csrf
                                    <button type="submit" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:6px;padding:4px 12px;font-size:11px;font-weight:700;cursor:pointer;">Unlock</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Recent Auth Activity --}}
        <div class="sa-panel">
            <div class="sa-panel-header">
                <h3 class="sa-panel-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                    Recent Auth Activity
                </h3>
                <a href="{{ route('superadmin.activity-log') }}" style="font-size:12px;color:#1B3A6B;font-weight:600;text-decoration:none;">View All →</a>
            </div>
            <div style="max-height:380px;overflow-y:auto;">
                @forelse($recentAuthActivity as $act)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                    <div style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:{{ $act['success'] ? '#10b981' : '#ef4444' }};box-shadow:0 0 6px {{ $act['success'] ? '#10b981' : '#ef4444' }};"></div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12.5px;color:#334155;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $act['description'] }}</div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:1px;">
                            <strong>{{ $act['user'] }}</strong> · IP: {{ $act['ip'] }}
                        </div>
                    </div>
                    <div style="font-size:11px;color:#94a3b8;white-space:nowrap;">
                        @if($act['created_at']){{ \Carbon\Carbon::parse($act['created_at'])->diffForHumans() }}@endif
                    </div>
                </div>
                @empty
                <div style="text-align:center;padding:36px;color:#94a3b8;font-size:13px;">No auth activity recorded.</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Inactive Board Users --}}
    @if($inactiveBoardUsersList->isNotEmpty())
    <div class="sa-panel" style="margin-top:24px;">
        <div class="sa-panel-header">
            <h3 class="sa-panel-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                Inactive Board Users (30+ days without login)
            </h3>
            <a href="{{ route('superadmin.users.index') }}" style="font-size:12px;color:#1B3A6B;font-weight:600;text-decoration:none;">Manage Users →</a>
        </div>
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inactiveBoardUsersList as $iu)
                <tr>
                    <td><code style="font-size:12px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $iu->username }}</code></td>
                    <td style="font-weight:600;">{{ $iu->name }}</td>
                    <td><span style="background:#ede9fe;color:#5b21b6;border-radius:6px;font-size:11px;font-weight:700;padding:2px 8px;">{{ ucwords(str_replace('_',' ',$iu->role)) }}</span></td>
                    <td style="font-size:12px;color:{{ $iu->last_login_at ? '#f59e0b' : '#ef4444' }};">
                        {{ $iu->last_login_at ? \Carbon\Carbon::parse($iu->last_login_at)->format('d-M-Y') : 'Never logged in' }}
                    </td>
                    <td class="text-center">
                        <a href="{{ route('superadmin.users.index') }}?search={{ $iu->username }}" style="font-size:11px;font-weight:700;color:#1B3A6B;border:1px solid #1B3A6B;border-radius:6px;padding:3px 10px;text-decoration:none;">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>

@push('styles')
<style>
    .text-center { text-align: center; }
    @media (max-width: 1024px) {
        div[style*="grid-template-columns:1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush
@endsection
