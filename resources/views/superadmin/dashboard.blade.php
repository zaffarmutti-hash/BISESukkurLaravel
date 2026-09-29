@extends('layouts.superadmin')

@php
    $title             = 'Command Center';
    $breadcrumbSection = 'Overview';
    $breadcrumbCurrent = 'Dashboard';
@endphp

@section('content')
{{-- ════════════════════════════════════════════════════════════════════
     STATUS BAR — Active Session · Enrollment Window · Exam Window
     ════════════════════════════════════════════════════════════════════ --}}
<div class="sa-status-bar" id="dashStatusBar">
    <div class="sa-status-sections">
        {{-- Section 1: Active Year --}}
        <div class="sa-status-col">
            <span style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px;">Active Session</span>
            <span class="sa-chip sa-chip-gold">
                <span style="width:7px;height:7px;background:#C8960C;border-radius:50%;display:inline-block;"></span>
                {{ $activeYear?->label ?? 'No Active Year' }}
            </span>
        </div>

        <div class="sa-status-divider"></div>

        {{-- Section 2: Enrollment Window --}}
        <div class="sa-status-col">
            <span style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px;">Enrollment</span>
            @php $ep = $enrollmentPhase['phase'] ?? 'closed'; @endphp
            @if($ep === 'normal')
                <span class="sa-chip sa-chip-green">
                    <span style="width:7px;height:7px;background:#10b981;border-radius:50%;display:inline-block;animation:pulse 2s infinite;"></span>
                    Open (Normal)
                </span>
            @elseif($ep === 'grace')
                <span class="sa-chip sa-chip-amber">
                    <span style="width:7px;height:7px;background:#f59e0b;border-radius:50%;display:inline-block;"></span>
                    Grace Period
                </span>
            @else
                <span class="sa-chip sa-chip-red">
                    <span style="width:7px;height:7px;background:#ef4444;border-radius:50%;display:inline-block;"></span>
                    Closed
                </span>
            @endif
        </div>

        <div class="sa-status-divider"></div>

        {{-- Section 3: Exam Window --}}
        <div class="sa-status-col">
            <span style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px;">Examination</span>
            @php $xp = $examPhase['phase'] ?? 'closed'; @endphp
            @if($xp === 'normal')
                <span class="sa-chip sa-chip-green">
                    <span style="width:7px;height:7px;background:#10b981;border-radius:50%;display:inline-block;animation:pulse 2s infinite;"></span>
                    Open (Normal)
                </span>
            @elseif($xp === 'grace')
                <span class="sa-chip sa-chip-amber">
                    <span style="width:7px;height:7px;background:#f59e0b;border-radius:50%;display:inline-block;"></span>
                    Grace Period
                </span>
            @else
                <span class="sa-chip sa-chip-red">
                    <span style="width:7px;height:7px;background:#ef4444;border-radius:50%;display:inline-block;"></span>
                    Closed
                </span>
            @endif
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:12px;">
        <span id="lastUpdatedTs" style="font-size:11px;color:#94a3b8;"></span>
        <button onclick="window.location.reload()"
            style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;padding:6px 14px;font-size:12px;font-weight:600;color:#475569;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s ease;"
            onmouseover="this.style.background='#fff';this.style.borderColor='#cbd5e1';"
            onmouseout="this.style.background='#f1f5f9';this.style.borderColor='#e2e8f0';">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            Refresh
        </button>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════
     8 GRADIENT STAT CARDS
     ════════════════════════════════════════════════════════════════════ --}}
<div class="sa-stat-grid" style="margin-bottom:28px;">

    {{-- Card 1: Total Students --}}
    <a href="{{ route('superadmin.enrollment.reports') }}" class="sa-stat-card sa-card-1">
        <div class="sa-card-circle-1"></div>
        <div class="sa-card-circle-2"></div>
        <div class="sa-card-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
            <div class="sa-card-value" data-counter-target="{{ $totalStudents }}">{{ number_format($totalStudents) }}</div>
            <div class="sa-card-label">Total Students</div>
        </div>
    </a>

    {{-- Card 2: Total Schools --}}
    <a href="{{ route('superadmin.schools') }}" class="sa-stat-card sa-card-2">
        <div class="sa-card-circle-1"></div>
        <div class="sa-card-circle-2"></div>
        <div class="sa-card-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M3 21h18M3 7v14M21 7v14M6 11h2M6 15h2M11 11h2M11 15h2M16 11h2M16 15h2M3 7l9-4 9 4"/></svg>
        </div>
        <div>
            <div class="sa-card-value" data-counter-target="{{ $totalSchools }}">{{ number_format($totalSchools) }}</div>
            <div class="sa-card-label">Total Schools</div>
        </div>
    </a>

    {{-- Card 3: Pending Verifications (urgent when > 0) --}}
    <a href="{{ route('superadmin.invoices.verify') }}" class="sa-stat-card {{ $pendingVerifications > 0 ? 'sa-pulse' : '' }} {{ $pendingVerifications > 0 ? 'sa-card-3' : 'sa-card-3 zero' }}">
        <div class="sa-card-circle-1"></div>
        <div class="sa-card-circle-2"></div>
        <div class="sa-card-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
        </div>
        <div>
            <div class="sa-card-value" data-counter-target="{{ $pendingVerifications }}">{{ number_format($pendingVerifications) }}</div>
            <div class="sa-card-label">Pending Verifications</div>
        </div>
    </a>

    {{-- Card 4: Verified Payments --}}
    <a href="{{ route('superadmin.reports.fees') }}" class="sa-stat-card sa-card-4">
        <div class="sa-card-circle-1"></div>
        <div class="sa-card-circle-2"></div>
        <div class="sa-card-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div>
            <div class="sa-card-value" style="font-size:28px;" data-counter-target="{{ round($verifiedPaymentsRs) }}" data-currency="true">Rs {{ number_format($verifiedPaymentsRs) }}</div>
            <div class="sa-card-label">Verified Payments</div>
        </div>
    </a>

    {{-- Card 5: Pending Enrollment Numbers (urgent when > 0) --}}
    <a href="{{ route('superadmin.enrollment.allotment') }}" class="sa-stat-card {{ $pendingEnrollmentNumbers > 0 ? 'sa-card-5 sa-pulse' : 'sa-card-5' }}">
        <div class="sa-card-circle-1"></div>
        <div class="sa-card-circle-2"></div>
        <div class="sa-card-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
        </div>
        <div>
            <div class="sa-card-value" data-counter-target="{{ $pendingEnrollmentNumbers }}">{{ number_format($pendingEnrollmentNumbers) }}</div>
            <div class="sa-card-label">Pending Enrollment Nos.</div>
        </div>
    </a>

    {{-- Card 6: Enrollment-to-Exam Gap (urgent when > 0) --}}
    <a href="{{ route('superadmin.enrollment.reports') }}" class="sa-stat-card {{ $enrollmentToExamGap > 0 ? 'sa-card-6 sa-pulse' : 'sa-card-6' }}">
        <div class="sa-card-circle-1"></div>
        <div class="sa-card-circle-2"></div>
        <div class="sa-card-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
        </div>
        <div>
            <div class="sa-card-value" data-counter-target="{{ $enrollmentToExamGap }}">{{ number_format($enrollmentToExamGap) }}</div>
            <div class="sa-card-label">Enrollment-to-Exam Gap</div>
        </div>
    </a>

    {{-- Card 7: District Admins Active --}}
    <a href="{{ route('superadmin.users.index') }}" class="sa-stat-card sa-card-7">
        <div class="sa-card-circle-1"></div>
        <div class="sa-card-circle-2"></div>
        <div class="sa-card-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
        </div>
        <div>
            <div class="sa-card-value" data-counter-target="{{ $districtAdminsActive }}">{{ number_format($districtAdminsActive) }}</div>
            <div class="sa-card-label">District Admins Active</div>
        </div>
    </a>

    {{-- Card 8: System Status --}}
    <a href="{{ route('superadmin.health') }}" class="sa-stat-card {{ ($systemHealth['healthy'] ?? true) ? 'sa-card-8-ok' : 'sa-card-8-err' }}">
        <div class="sa-card-circle-1"></div>
        <div class="sa-card-circle-2"></div>
        <div class="sa-card-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div class="sa-card-value" style="font-size:26px;letter-spacing:0;">
                {{ ($systemHealth['healthy'] ?? true) ? 'Healthy' : 'Issues' }}
            </div>
            <div class="sa-card-label">System Status</div>
        </div>
    </a>

</div>

{{-- ════════════════════════════════════════════════════════════════════
     BOTTOM ROW: District Table + Quick Actions + Activity Feed
     ════════════════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">

    {{-- LEFT: District Breakdown Table --}}
    <div style="display:flex;flex-direction:column;gap:24px;">
        <div class="sa-panel">
            <div class="sa-panel-header">
                <h3 class="sa-panel-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2.2"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/></svg>
                    District Overview
                </h3>
                <span style="font-size:12px;color:#94a3b8;">{{ $activeYear?->label ?? 'Current Session' }}</span>
            </div>
            <div style="max-height:420px;overflow-y:auto;">
                <table class="sa-table">
                    <thead style="position:sticky;top:0;z-index:1;">
                        <tr>
                            <th>District</th>
                            <th class="text-right">Schools</th>
                            <th class="text-right">Students</th>
                            <th class="text-right">Verified Rs.</th>
                            <th class="text-right">Pending</th>
                            <th class="text-right">Gap</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totals = ['schools'=>0,'students'=>0,'amount'=>0,'pending'=>0,'gap'=>0]; @endphp
                        @forelse($districtStats as $d)
                        @php
                            $totals['schools']  += $d['school_count'] ?? 0;
                            $totals['students'] += $d['student_count'] ?? 0;
                            $totals['amount']   += $d['verified_amount'] ?? 0;
                            $totals['pending']  += $d['pending_invoices'] ?? 0;
                            $totals['gap']      += $d['exam_gap'] ?? 0;
                            $pendCount = $d['pending_invoices'] ?? 0;
                            $gapCount  = $d['exam_gap'] ?? 0;
                            $dotClass  = $pendCount === 0 ? 'sa-dot-green' : ($pendCount < 10 ? 'sa-dot-amber' : 'sa-dot-red');
                        @endphp
                        <tr>
                            <td>
                                <span class="sa-dot {{ $dotClass }}"></span>
                                <strong>{{ $d['name'] ?? 'N/A' }}</strong>
                                <small style="color:#94a3b8;margin-left:4px;">{{ $d['code'] ?? '' }}</small>
                            </td>
                            <td class="text-right">{{ number_format($d['school_count'] ?? 0) }}</td>
                            <td class="text-right">{{ number_format($d['student_count'] ?? 0) }}</td>
                            <td class="text-right">{{ 'Rs ' . number_format($d['verified_amount'] ?? 0) }}</td>
                            <td class="text-right" style="{{ $pendCount > 0 ? 'color:#ef4444;font-weight:700;' : '' }}">
                                {{ number_format($pendCount) }}
                            </td>
                            <td class="text-right" style="{{ $gapCount > 0 ? 'color:#ef4444;font-weight:700;' : '' }}">
                                {{ number_format($gapCount) }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('superadmin.districts') }}"
                                    style="font-size:11px;font-weight:700;color:#1B3A6B;border:1px solid #1B3A6B;border-radius:6px;padding:3px 10px;text-decoration:none;white-space:nowrap;transition:all .2s;"
                                    onmouseover="this.style.background='#1B3A6B';this.style.color='#fff';"
                                    onmouseout="this.style.background='transparent';this.style.color='#1B3A6B';">
                                    View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">No district data available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($districtStats) > 0)
                    <tfoot>
                        <tr style="background:#f8fafc;font-weight:800;border-top:2px solid #e2e8f0;">
                            <td style="padding:12px 16px;color:#0f172a;">TOTALS</td>
                            <td class="text-right" style="padding:12px 16px;color:#0f172a;">{{ number_format($totals['schools']) }}</td>
                            <td class="text-right" style="padding:12px 16px;color:#0f172a;">{{ number_format($totals['students']) }}</td>
                            <td class="text-right" style="padding:12px 16px;color:#0f172a;">Rs {{ number_format($totals['amount']) }}</td>
                            <td class="text-right" style="padding:12px 16px;color:{{ $totals['pending'] > 0 ? '#ef4444' : '#0f172a' }};font-weight:800;">{{ number_format($totals['pending']) }}</td>
                            <td class="text-right" style="padding:12px 16px;color:{{ $totals['gap'] > 0 ? '#ef4444' : '#0f172a' }};font-weight:800;">{{ number_format($totals['gap']) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Recent Activity Feed --}}
        <div class="sa-panel">
            <div class="sa-panel-header">
                <h3 class="sa-panel-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                    Recent Activity
                </h3>
                <a href="{{ route('superadmin.activity-log') }}" style="font-size:12px;color:#1B3A6B;font-weight:600;text-decoration:none;">View All →</a>
            </div>
            <div style="max-height:320px;overflow-y:auto;display:flex;flex-direction:column;gap:0;">
                @forelse($recentActivity as $act)
                @php
                    $logName = $act['log_name'] ?? 'general';
                    $dotColors = [
                        'user' => '#8b5cf6', 'school' => '#3b82f6', 'invoice' => '#10b981',
                        'enrollment' => '#f59e0b', 'examination' => '#6366f1',
                        'system_settings' => '#ef4444', 'general' => '#94a3b8',
                    ];
                    $dotColor = $dotColors[$logName] ?? '#94a3b8';
                @endphp
                <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #f1f5f9;">
                    <div style="width:32px;height:32px;border-radius:50%;background:{{ $dotColor }}22;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $dotColor }};"></div>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;color:#334155;font-weight:500;line-height:1.4;">{{ $act['description'] }}</div>
                        <div style="display:flex;align-items:center;gap:8px;margin-top:3px;">
                            <span style="font-size:11px;color:#64748b;font-weight:600;">{{ $act['user'] }}</span>
                            <span style="color:#e2e8f0;font-size:10px;">•</span>
                            <span style="font-size:11px;color:#94a3b8;">
                                @if($act['created_at'])
                                    {{ \Carbon\Carbon::parse($act['created_at'])->diffForHumans() }}
                                @else
                                    Just now
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div style="text-align:center;padding:40px 0;color:#94a3b8;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 8px;display:block;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                    <div style="font-size:13px;">No activity recorded yet.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- RIGHT: Quick Actions Panel --}}
    <div class="sa-panel" style="position:sticky;top:88px;">
        <div class="sa-panel-header" style="border-bottom:1px solid #f1f5f9;margin-bottom:16px;padding-bottom:12px;">
            <h3 class="sa-panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                Quick Actions
            </h3>
        </div>
        <div style="display:flex;flex-direction:column;gap:10px;">

            @php
            $qas = [
                ['label' => 'Verify Pending Payments', 'icon' => '<polyline points="22 4 12 14.01 9 11.01"/><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>', 'route' => 'superadmin.invoices.verify', 'grad' => 'linear-gradient(135deg,#10b981,#059669)', 'count' => $pendingVerifications],
                ['label' => 'Run Enrollment Allotment', 'icon' => '<line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/>', 'route' => 'superadmin.enrollment.allotment', 'grad' => 'linear-gradient(135deg,#f7971e,#ffd200)', 'count' => $pendingEnrollmentNumbers],
                ['label' => 'Manage Academic Years', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>', 'route' => 'superadmin.academic-years.index', 'grad' => 'linear-gradient(135deg,#1B3A6B,#2d5a9e)', 'count' => null],
                ['label' => 'Send Announcement', 'icon' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>', 'route' => 'superadmin.announcements', 'grad' => 'linear-gradient(135deg,#a18cd1,#fbc2eb)', 'count' => null],
                ['label' => 'System Settings', 'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 6a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 1.6a1.65 1.65 0 0 0 1-1.51V0a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 6a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>', 'route' => 'superadmin.settings.system', 'grad' => 'linear-gradient(135deg,#667eea,#764ba2)', 'count' => null],
                ['label' => 'Activity Log', 'icon' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/>', 'route' => 'superadmin.activity-log', 'grad' => 'linear-gradient(135deg,#4facfe,#00f2fe)', 'count' => null],
            ];
            @endphp

            @foreach($qas as $qa)
            <a href="{{ route($qa['route']) }}"
                style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;background:#f8fafc;border:1px solid #e2e8f0;text-decoration:none;color:#0f172a;transition:all .2s ease;position:relative;"
                onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(0,0,0,.1)';this.style.borderColor='#cbd5e1';"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';this.style.borderColor='#e2e8f0';">
                <div style="width:36px;height:36px;border-radius:10px;background:{{ $qa['grad'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">{!! $qa['icon'] !!}</svg>
                </div>
                <span style="font-size:13px;font-weight:600;flex:1;">{{ $qa['label'] }}</span>
                @if(isset($qa['count']) && $qa['count'] > 0)
                    <span style="background:#ef4444;color:#fff;font-size:10px;font-weight:800;padding:2px 7px;border-radius:9999px;">{{ $qa['count'] }}</span>
                @endif
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            @endforeach
        </div>
    </div>

</div>

@push('scripts')
<script>
    // Set last updated timestamp
    document.getElementById('lastUpdatedTs').textContent = 'Updated ' + new Date().toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit'});

    // Auto-refresh every 5 minutes
    setTimeout(() => window.location.reload(), 300000);
</script>
@endpush

@push('styles')
<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    @media (max-width: 1024px) {
        div[style*="grid-template-columns:1fr 300px"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush
@endsection
