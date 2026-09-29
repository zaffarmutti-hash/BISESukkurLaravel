@extends('layouts.superadmin')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Enrollment Central Hub</h1>
            <p style="font-size: 13.5px; color: #64748b; margin: 4px 0 0 0;">Unified control center for enrollment registration windows, fee schedules, invoice verifications, and number allotment.</p>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('superadmin.invoices.verify') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; font-weight: 600;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Full Verification Desk</span>
            </a>
            <a href="{{ route('superadmin.enrollment.allotment') }}" class="sa-header-btn" style="background: #C8960C; color: #fff; border: none; font-weight: 600;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
                <span>Allotment Console</span>
            </a>
        </div>
    </div>

    <!-- Quick Hub Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px;">
        <div class="sa-panel" style="padding: 18px 20px;">
            <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Pending Invoice Verifications</div>
            <div style="font-size: 26px; font-weight: 800; color: {{ ($stats['pending_verification'] ?? 0) > 0 ? '#ef4444' : '#0f172a' }}; margin-top: 4px;">
                {{ number_format($stats['pending_verification'] ?? 0) }}
            </div>
        </div>

        <div class="sa-panel" style="padding: 18px 20px;">
            <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Pending Enrollment Numbers</div>
            <div style="font-size: 26px; font-weight: 800; color: {{ ($stats['pending_enrollment_numbers'] ?? 0) > 0 ? '#f59e0b' : '#0f172a' }}; margin-top: 4px;">
                {{ number_format($stats['pending_enrollment_numbers'] ?? 0) }}
            </div>
        </div>

        <div class="sa-panel" style="padding: 18px 20px;">
            <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Total Verified Collections</div>
            <div style="font-size: 26px; font-weight: 800; color: #10b981; margin-top: 4px;">
                Rs {{ number_format($stats['verified_payments'] ?? 0) }}
            </div>
        </div>

        <div class="sa-panel" style="padding: 18px 20px;">
            <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Active Academic Session</div>
            <div style="font-size: 20px; font-weight: 800; color: #C8960C; margin-top: 6px;">
                {{ $activeYear->name ?? '2026-2027' }}
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 8px; border-bottom: 2px solid #e2e8f0; overflow-x: auto; padding-bottom: 2px;">
        <button type="button" onclick="switchTab('window')" id="btn-tab-window" class="sa-tab-btn {{ ($activeTab ?? 'window') === 'window' ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span>Window Settings</span>
        </button>

        <button type="button" onclick="switchTab('fees')" id="btn-tab-fees" class="sa-tab-btn {{ ($activeTab ?? '') === 'fees' ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <span>Fee Configuration</span>
        </button>

        <button type="button" onclick="switchTab('verify')" id="btn-tab-verify" class="sa-tab-btn {{ ($activeTab ?? '') === 'verify' ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
            <span>Pending Verifications ({{ count($pendingInvoices) }})</span>
        </button>

        <button type="button" onclick="switchTab('allotment')" id="btn-tab-allotment" class="sa-tab-btn {{ ($activeTab ?? '') === 'allotment' ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
            <span>Allotment History</span>
        </button>

        <button type="button" onclick="switchTab('reports')" id="btn-tab-reports" class="sa-tab-btn {{ ($activeTab ?? '') === 'reports' ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <span>Enrollment Reports</span>
        </button>

        <button type="button" onclick="switchTab('permissions')" id="btn-tab-permissions" class="sa-tab-btn {{ ($activeTab ?? '') === 'permissions' ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-1.5 6.1L12.9 4.7a6.5 6.5 0 1 0-8.2 8.2l5.4 4.6 2-2 2 2 2-2 2 2 2.5-2.5"/></svg>
            <span>Special Permissions ({{ count($activeExceptions) }})</span>
        </button>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 1: WINDOW SETTINGS
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="tab-window" class="sa-tab-pane {{ ($activeTab ?? 'window') === 'window' ? 'active' : '' }}">
        <!-- Hero Status Card -->
        @php
            $phaseName = $globalPhase->phase ?? 'closed';
            $isGrace = ($phaseName === 'grace');
            $isOpen = ($phaseName === 'normal');
        @endphp
        <div style="border-radius: 16px; padding: 24px 28px; color: #fff; margin-bottom: 24px;
            background: {{ $isOpen ? 'linear-gradient(135deg, #059669, #10b981)' : ($isGrace ? 'linear-gradient(135deg, #d97706, #f59e0b)' : 'linear-gradient(135deg, #dc2626, #ef4444)') }}; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.9;">Current Global Enrollment Status</div>
            <div style="font-size: 32px; font-weight: 800; margin: 4px 0 8px 0;">
                {{ $isOpen ? 'Enrollment Window is OPEN (Normal Phase)' : ($isGrace ? 'Grace Period ACTIVE (Late Fee Applies)' : 'Enrollment Window is CLOSED') }}
            </div>
            <div style="font-size: 14px; opacity: 0.95;">
                {{ $globalPhase->nextTransitionLabel ?? 'No active extension configured.' }}
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- Global Window Dates Form -->
            <div class="sa-panel">
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                    Global Enrollment Window Dates
                </h3>
                <form method="POST" action="{{ route('superadmin.academic-years.window-dates', $activeYear->id ?? 1) }}">
                    @csrf
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Window Open Switch</label>
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; cursor: pointer;">
                                <input type="checkbox" name="enrollment_window_open" value="1" {{ ($activeYear->enrollment_window_open ?? false) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #10b981;">
                                <span>Enrollment Window Open</span>
                            </label>
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Normal Period Start</label>
                            <input type="datetime-local" name="enrollment_start" value="{{ $activeYear->enrollment_start ? $activeYear->enrollment_start->format('Y-m-d\TH:i') : '' }}" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Normal Period End</label>
                            <input type="datetime-local" name="enrollment_normal_end" value="{{ $activeYear->enrollment_normal_end ? $activeYear->enrollment_normal_end->format('Y-m-d\TH:i') : '' }}" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Grace Period End (Late Fee)</label>
                            <input type="datetime-local" name="enrollment_grace_end" value="{{ $activeYear->enrollment_grace_end ? $activeYear->enrollment_grace_end->format('Y-m-d\TH:i') : '' }}" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        </div>

                        <button type="submit" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; justify-content: center; font-weight: 700; padding: 10px 0; margin-top: 8px;">
                            Save Global Window Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- District & School Overrides Summary -->
            <div class="sa-panel">
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                    District & School Overrides
                </h3>
                <div style="font-size: 13px; color: #64748b; margin-bottom: 14px;">
                    Specific districts or individual schools can have independent deadlines that override global board settings.
                </div>

                <table class="sa-table">
                    <thead>
                        <tr>
                            <th>District</th>
                            <th>Effective Status</th>
                            <th>Overrides</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($districts as $d)
                            @php
                                $dOverride = $districtOverrides->firstWhere('scope_id', $d->id);
                            @endphp
                            <tr>
                                <td><strong>{{ $d->name }}</strong></td>
                                <td>
                                    @if($dOverride)
                                        <span class="sa-chip sa-chip-amber">District Override</span>
                                    @else
                                        <span style="color: #94a3b8; font-size: 12px;">Follows Global</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('superadmin.window-overrides.index') }}" class="sa-header-btn" style="padding: 4px 8px; font-size: 11px;">Configure</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 2: FEE CONFIGURATION
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="tab-fees" class="sa-tab-pane {{ ($activeTab ?? '') === 'fees' ? 'active' : '' }}">
        <div class="sa-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 class="sa-panel-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C8960C" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    <span>Enrollment Fee Rates Matrix (Session {{ $activeYear->name ?? '2026' }})</span>
                </h3>
                <a href="{{ route('superadmin.fee-rates.index') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; font-size: 12px; font-weight: 600;">
                    Manage All Fee Structures &rarr;
                </a>
            </div>

            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Class Level</th>
                        <th>Student Category</th>
                        <th style="text-align: right;">Standard Rate (Rs)</th>
                        <th style="text-align: right;">Late Surcharge (Rs)</th>
                        <th style="text-align: right;">Total Grace Fee (Rs)</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fees as $f)
                        @php
                            $std = $f->amount_paisas / 100;
                            $late = ($f->late_fee_surcharge_paisas ?? 0) / 100;
                        @endphp
                        <tr>
                            <td><strong style="color: #0f172a;">{{ strtoupper(str_replace('_', ' ', $f->class_level)) }}</strong></td>
                            <td><span class="sa-chip sa-chip-blue">{{ ucfirst($f->student_type) }}</span></td>
                            <td style="text-align: right; font-family: monospace; font-weight: 700; color: #0f172a;">Rs {{ number_format($std, 2) }}</td>
                            <td style="text-align: right; font-family: monospace; color: #f59e0b;">+ Rs {{ number_format($late, 2) }}</td>
                            <td style="text-align: right; font-family: monospace; font-weight: 700; color: #10b981;">Rs {{ number_format($std + $late, 2) }}</td>
                            <td style="text-align: center;">
                                <span class="sa-chip {{ $f->is_active ? 'sa-chip-green' : 'sa-chip-red' }}">{{ $f->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 24px;">No enrollment fees configured for this session.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 3: PENDING VERIFICATIONS
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="tab-verify" class="sa-tab-pane {{ ($activeTab ?? '') === 'verify' ? 'active' : '' }}">
        <div class="sa-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 class="sa-panel-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C8960C" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                    <span>Invoices Awaiting Board Verification</span>
                </h3>
                <a href="{{ route('superadmin.invoices.verify') }}" class="sa-header-btn" style="background: #10b981; color: #fff; border: none; font-weight: 700;">
                    Open Split-Panel Verification Desk &rarr;
                </a>
            </div>

            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Invoice Number</th>
                        <th>School Institution</th>
                        <th>District</th>
                        <th>Program / Level</th>
                        <th style="text-align: center;">Candidates</th>
                        <th style="text-align: right;">Total Amount</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingInvoices as $inv)
                        <tr>
                            <td>
                                <span style="font-family: monospace; font-weight: 700; color: #1B3A6B; background: #eff6ff; padding: 3px 8px; border-radius: 4px;">
                                    {{ $inv->invoice_number }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $inv->school->name ?? '—' }}</strong>
                                <div style="font-size: 11px; color: #64748b;">{{ $inv->school->username ?? '' }}</div>
                            </td>
                            <td>{{ $inv->school->district->name ?? '—' }}</td>
                            <td>
                                <span class="sa-chip sa-chip-blue">{{ strtoupper($inv->class_level ?? 'SSC') }}</span>
                                <span style="font-size: 12px; color: #64748b;">{{ $inv->subject_group ?? '' }}</span>
                            </td>
                            <td style="text-align: center; font-weight: 700;">{{ $inv->student_count }}</td>
                            <td style="text-align: right; font-family: monospace; font-weight: 700; color: #047857;">
                                Rs {{ number_format($inv->total_amount_paisas / 100, 2) }}
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('superadmin.invoices.verify', ['invoice_id' => $inv->id]) }}" class="sa-header-btn" style="padding: 4px 10px; font-size: 12px; background: #1B3A6B; color: #fff; border: none;">
                                    Inspect & Verify
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #94a3b8; padding: 36px;">
                                All submitted enrollment invoices have been processed and verified.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 4: ALLOTMENT HISTORY
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="tab-allotment" class="sa-tab-pane {{ ($activeTab ?? '') === 'allotment' ? 'active' : '' }}">
        <div class="sa-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 class="sa-panel-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C8960C" stroke-width="2.5"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
                    <span>Recent Issued Enrollment Numbers</span>
                </h3>
                <a href="{{ route('superadmin.enrollment.allotment') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; font-size: 12px; font-weight: 600;">
                    Open Full Registry & Manual Console &rarr;
                </a>
            </div>

            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Enrollment No.</th>
                        <th>Student Name</th>
                        <th>Father Name</th>
                        <th>School / Institute</th>
                        <th>District</th>
                        <th>Allotment Type</th>
                        <th>Issued At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allotmentStudents as $st)
                        <tr>
                            <td>
                                <span style="font-family: monospace; font-weight: 800; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 3px 8px; border-radius: 6px;">
                                    {{ $st->enrollment_number }}
                                </span>
                            </td>
                            <td><strong>{{ $st->full_name }}</strong></td>
                            <td>{{ $st->father_name }}</td>
                            <td>{{ $st->school->name ?? '—' }}</td>
                            <td>{{ $st->school->district->name ?? '—' }}</td>
                            <td>
                                <span class="sa-chip {{ $st->allotment_type === 'manual' ? 'sa-chip-amber' : 'sa-chip-green' }}">
                                    {{ ucfirst($st->allotment_type ?? 'auto') }}
                                </span>
                            </td>
                            <td style="font-size: 12px; color: #64748b;">
                                {{ $st->enrollment_number_issued_at ? $st->enrollment_number_issued_at->format('d-M-Y H:i') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #94a3b8; padding: 24px;">No enrollment numbers issued yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 5: ENROLLMENT REPORTS
         ═════════════════════════════════════════════ -->
    <div id="tab-reports" class="sa-tab-pane {{ ($activeTab ?? '') === 'reports' ? 'active' : '' }}">
        <div class="sa-panel">
            <h3 class="sa-panel-title" style="margin-bottom: 16px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C8960C" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <span>Enrollment Statistics by District</span>
            </h3>

            <table class="sa-table">
                <thead>
                    <tr>
                        <th>District</th>
                        <th style="text-align: center;">Schools</th>
                        <th style="text-align: right;">Total Candidates</th>
                        <th style="text-align: right;">Verified Revenue</th>
                        <th style="text-align: center;">Pending Challans</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($districtStats as $d)
                        <tr>
                            <td><strong>{{ $d['name'] }}</strong></td>
                            <td style="text-align: center;">{{ number_format($d['school_count']) }}</td>
                            <td style="text-align: right; font-weight: 700;">{{ number_format($d['student_count']) }}</td>
                            <td style="text-align: right; color: #047857; font-family: monospace; font-weight: 700;">Rs {{ number_format($d['verified_amount'], 2) }}</td>
                            <td style="text-align: center;">
                                <span class="sa-chip {{ $d['pending_invoices'] > 0 ? 'sa-chip-amber' : 'sa-chip-green' }}">{{ $d['pending_invoices'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 6: SPECIAL PERMISSIONS
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="tab-permissions" class="sa-tab-pane {{ ($activeTab ?? '') === 'permissions' ? 'active' : '' }}">
        <div class="sa-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 class="sa-panel-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C8960C" stroke-width="2.5"><path d="M21 2l-2 2m-1.5 6.1L12.9 4.7a6.5 6.5 0 1 0-8.2 8.2l5.4 4.6 2-2 2 2 2-2 2 2 2.5-2.5"/></svg>
                    <span>Active School Special Exceptions & Overrides</span>
                </h3>
                <a href="{{ route('superadmin.special-permissions.index') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; font-size: 12px; font-weight: 600;">
                    Grant New Permission &rarr;
                </a>
            </div>

            <table class="sa-table">
                <thead>
                    <tr>
                        <th>School</th>
                        <th>Permission Type</th>
                        <th>Granted By</th>
                        <th>Reason / Notes</th>
                        <th>Expiry</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeExceptions as $exc)
                        <tr>
                            <td>
                                <strong>{{ $exc->school->name ?? 'All Schools' }}</strong>
                                <div style="font-size: 11px; color: #64748b;">{{ $exc->school->username ?? '' }}</div>
                            </td>
                            <td><span class="sa-chip sa-chip-gold">{{ strtoupper(str_replace('_', ' ', $exc->exception_type)) }}</span></td>
                            <td>{{ $exc->grantedBy->name ?? 'Super Admin' }}</td>
                            <td style="font-size: 13px; color: #334155;">{{ $exc->reason ?? 'Official approval granted.' }}</td>
                            <td>
                                <span class="sa-chip sa-chip-green">{{ $exc->expires_at ? $exc->expires_at->format('d-M-Y') : 'Permanent' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94a3b8; padding: 24px;">No active special permissions active.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('styles')
<style>
    .sa-tab-btn {
        background: transparent;
        border: none;
        padding: 10px 18px;
        font-size: 13.5px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        border-radius: 8px 8px 0 0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        border-bottom: 3px solid transparent;
        white-space: nowrap;
    }
    .sa-tab-btn:hover {
        color: #1B3A6B;
        background: #f1f5f9;
    }
    .sa-tab-btn.active {
        color: #1B3A6B;
        border-bottom-color: #C8960C;
        background: #ffffff;
    }
    .sa-tab-pane {
        display: none;
    }
    .sa-tab-pane.active {
        display: block;
    }
</style>
@endpush

@push('scripts')
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.sa-tab-pane').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.sa-tab-btn').forEach(el => el.classList.remove('active'));

        const targetPane = document.getElementById('tab-' + tabId);
        const targetBtn = document.getElementById('btn-tab-' + tabId);

        if (targetPane) targetPane.classList.add('active');
        if (targetBtn) targetBtn.classList.add('active');

        // Update URL query param without reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    }
</script>
@endpush
@endsection
