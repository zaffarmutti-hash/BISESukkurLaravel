@extends('layouts.superadmin')

@section('title', 'Academic Year Management')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <!-- Header with Action -->
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Academic Years</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Manage academic sessions, phase transition windows, and cycle rollovers for all five BISE Sukkur districts.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="sa-btn sa-btn-gold" onclick="openCreateYearModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Create Next Academic Year
            </button>
        </div>
    </div>

    <!-- Active Year Hero Banner (Gold Card) -->
    @if($activeYear)
    <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid rgba(200, 150, 12, 0.4); border-radius: 16px; padding: 28px; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2);">
        <div style="position: absolute; right: -20px; top: -20px; width: 140px; height: 140px; border-radius: 50%; background: radial-gradient(circle, rgba(200,150,12,0.15) 0%, transparent 70%); pointer-events: none;"></div>
        
        <div class="d-flex justify-between align-center flex-wrap gap-4">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(200, 150, 12, 0.2); border: 1px solid rgba(200, 150, 12, 0.5); padding: 4px 12px; border-radius: 20px; color: #eab308; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #eab308; display: inline-block;"></span>
                    Current Active Board Session
                </div>
                <h2 style="font-size: 32px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: -0.5px;">
                    Academic Session {{ $activeYear->label ?? $activeYear->year_start.'-'.$activeYear->year_end }}
                </h2>
                <p style="color: rgba(255, 255, 255, 0.65); font-size: 14px; margin: 6px 0 0 0;">
                    All schools in Sukkur, Khairpur, Naushahro Feroze, Ghotki, and Shikarpur are bound to this session.
                </p>
            </div>

            <div class="d-flex gap-4 flex-wrap">
                <!-- Enrollment Window Status Pill -->
                <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 14px 20px; text-align: center; min-width: 140px;">
                    <div style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.5); text-transform: uppercase;">Enrollment Window</div>
                    <div style="font-size: 15px; font-weight: 700; color: {{ $activeYear->enrollment_window_open ? '#4ade80' : '#f87171' }}; margin-top: 4px;">
                        {{ $activeYear->enrollment_window_open ? 'OPEN' : 'CLOSED' }}
                    </div>
                    <div style="font-size: 11px; color: rgba(255, 255, 255, 0.4); margin-top: 2px;">
                        Closes: {{ $activeYear->enrollment_close_date ? \Carbon\Carbon::parse($activeYear->enrollment_close_date)->format('d M Y') : 'N/A' }}
                    </div>
                </div>

                <!-- Exam Window Status Pill -->
                <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 14px 20px; text-align: center; min-width: 140px;">
                    <div style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.5); text-transform: uppercase;">Examination Window</div>
                    <div style="font-size: 15px; font-weight: 700; color: {{ $activeYear->examination_window_open ? '#4ade80' : '#f87171' }}; margin-top: 4px;">
                        {{ $activeYear->examination_window_open ? 'OPEN' : 'CLOSED' }}
                    </div>
                    <div style="font-size: 11px; color: rgba(255, 255, 255, 0.4); margin-top: 2px;">
                        Closes: {{ $activeYear->examination_close_date ? \Carbon\Carbon::parse($activeYear->examination_close_date)->format('d M Y') : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Pre-Check Warnings / Readiness Bar -->
    <div class="sa-paper p-5" style="border-left: 4px solid #f59e0b;">
        <div class="d-flex align-center gap-3 mb-3">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Session Transition Pre-Check Warnings</h3>
        </div>
        <p class="text-sm text-slate-600 mb-4" style="margin-top: 0;">
            Review these indicators before initiating the transition to the next academic cycle. These are system advisories and will not block roll-forward.
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
            <!-- Warning 1: Unverified Invoices -->
            <div style="background: {{ $unverifiedCount > 0 ? '#fef2f2' : '#f0fdf4' }}; border: 1px solid {{ $unverifiedCount > 0 ? '#fecaca' : '#bbf7d0' }}; border-radius: 12px; padding: 14px 18px;">
                <div style="font-size: 12px; font-weight: 600; color: {{ $unverifiedCount > 0 ? '#b91c1c' : '#15803d' }}; text-transform: uppercase;">Unverified Invoices</div>
                <div style="font-size: 24px; font-weight: 800; color: {{ $unverifiedCount > 0 ? '#991b1b' : '#166534' }}; margin: 4px 0;">{{ number_format($unverifiedCount) }}</div>
                <div style="font-size: 12px; color: {{ $unverifiedCount > 0 ? '#7f1d1d' : '#14532d' }};">
                    {{ $unverifiedCount > 0 ? 'Pending bank payment approvals awaiting verification.' : 'All invoices verified.' }}
                </div>
            </div>

            <!-- Warning 2: Missing Enrollment Numbers -->
            <div style="background: {{ $missingEnrollmentCount > 0 ? '#fffbeb' : '#f0fdf4' }}; border: 1px solid {{ $missingEnrollmentCount > 0 ? '#fde68a' : '#bbf7d0' }}; border-radius: 12px; padding: 14px 18px;">
                <div style="font-size: 12px; font-weight: 600; color: {{ $missingEnrollmentCount > 0 ? '#b45309' : '#15803d' }}; text-transform: uppercase;">Missing Enrollment Numbers</div>
                <div style="font-size: 24px; font-weight: 800; color: {{ $missingEnrollmentCount > 0 ? '#92400e' : '#166534' }}; margin: 4px 0;">{{ number_format($missingEnrollmentCount) }}</div>
                <div style="font-size: 12px; color: {{ $missingEnrollmentCount > 0 ? '#78350f' : '#14532d' }};">
                    {{ $missingEnrollmentCount > 0 ? 'Students paid but pending enrollment number allotment.' : 'All students allotted.' }}
                </div>
            </div>

            <!-- Warning 3: Missing Exam Forms -->
            <div style="background: {{ $missingExamCount > 0 ? '#fef2f2' : '#f0fdf4' }}; border: 1px solid {{ $missingExamCount > 0 ? '#fecaca' : '#bbf7d0' }}; border-radius: 12px; padding: 14px 18px;">
                <div style="font-size: 12px; font-weight: 600; color: {{ $missingExamCount > 0 ? '#b91c1c' : '#15803d' }}; text-transform: uppercase;">Missing Exam Forms (Gap)</div>
                <div style="font-size: 24px; font-weight: 800; color: {{ $missingExamCount > 0 ? '#991b1b' : '#166534' }}; margin: 4px 0;">{{ number_format($missingExamCount) }}</div>
                <div style="font-size: 12px; color: {{ $missingExamCount > 0 ? '#7f1d1d' : '#14532d' }};">
                    {{ $missingExamCount > 0 ? 'Enrolled candidates with no examination registration.' : 'Zero registration gap.' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Academic Years Timeline / History -->
    <div class="sa-paper p-5">
        <div class="d-flex justify-between align-center mb-4">
            <div>
                <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">Academic Year Records</h3>
                <p class="text-xs text-slate-500" style="margin: 2px 0 0 0;">Historical archive of board examination sessions.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>Session Label</th>
                        <th>Period</th>
                        <th>Enrollment Window</th>
                        <th>Examination Window</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($years as $yr)
                    <tr>
                        <td>
                            <div class="font-bold text-slate-900" style="font-size: 14px;">{{ $yr->label ?? $yr->year_start.'-'.$yr->year_end }}</div>
                            <span class="text-xs text-slate-400">ID: #{{ $yr->id }}</span>
                        </td>
                        <td>
                            <div class="text-sm text-slate-700">{{ $yr->year_start }} &mdash; {{ $yr->year_end }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-center gap-2">
                                <span class="sa-badge {{ $yr->enrollment_window_open ? 'sa-badge-active' : 'sa-badge-inactive' }}">
                                    {{ $yr->enrollment_window_open ? 'Open' : 'Closed' }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    {{ $yr->enrollment_open_date ? \Carbon\Carbon::parse($yr->enrollment_open_date)->format('M d, Y') : '-' }} to 
                                    {{ $yr->enrollment_close_date ? \Carbon\Carbon::parse($yr->enrollment_close_date)->format('M d, Y') : '-' }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-center gap-2">
                                <span class="sa-badge {{ $yr->examination_window_open ? 'sa-badge-active' : 'sa-badge-inactive' }}">
                                    {{ $yr->examination_window_open ? 'Open' : 'Closed' }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    {{ $yr->examination_open_date ? \Carbon\Carbon::parse($yr->examination_open_date)->format('M d, Y') : '-' }} to 
                                    {{ $yr->examination_close_date ? \Carbon\Carbon::parse($yr->examination_close_date)->format('M d, Y') : '-' }}
                                </span>
                            </div>
                        </td>
                        <td>
                            @if($yr->is_active)
                                <span class="sa-badge sa-badge-gold">ACTIVE SESSION</span>
                            @else
                                <span class="sa-badge sa-badge-inactive">Archived</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="d-flex justify-end gap-2">
                                @if(!$yr->is_active)
                                <form method="POST" action="{{ route('superadmin.academic-years.activate', $yr->id) }}" onsubmit="return confirm('Activate academic session {{ $yr->label }}? This will switch the active session for all schools.');">
                                    @csrf
                                    <button type="submit" class="sa-btn sa-btn-outline" style="font-size: 12px; padding: 4px 10px;">
                                        Set Active
                                    </button>
                                </form>
                                @endif
                                
                                <form method="POST" action="{{ route($yr->enrollment_window_open ? 'superadmin.academic-years.enrollment.close' : 'superadmin.academic-years.enrollment.open', $yr->id) }}">
                                    @csrf
                                    <button type="submit" class="sa-btn {{ $yr->enrollment_window_open ? 'sa-btn-danger' : 'sa-btn-outline' }}" style="font-size: 12px; padding: 4px 10px;">
                                        {{ $yr->enrollment_window_open ? 'Close Enrollment' : 'Open Enrollment' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-slate-400">No academic years found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create Next Academic Year (Requires typing CONFIRM) -->
<div id="createYearModal" class="sa-modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); z-index: 500; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div style="padding: 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #0f172a; border-radius: 20px 20px 0 0;">
            <div>
                <h3 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">Create Next Academic Year</h3>
                <p style="font-size: 12px; color: rgba(255, 255, 255, 0.6); margin: 2px 0 0 0;">Initialize a new board cycle with automatic rollover checklist.</p>
            </div>
            <button type="button" onclick="closeCreateYearModal()" style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 20px;">&times;</button>
        </div>

        <form method="POST" action="{{ route('superadmin.academic-years.store') }}" style="padding: 24px;" id="createYearForm">
            @csrf

            <!-- Checklist Banner -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">System Rollover Checklist:</div>
                <ul style="font-size: 12px; color: #475569; padding-left: 20px; margin: 0; line-height: 1.6;">
                    <li>New AcademicYear record will be created in the registry.</li>
                    <li>Fee structures and rate matrix will be prepared for the new session.</li>
                    <li>Global enrollment and examination windows initialized for configuration.</li>
                    <li>Current active year history and student records will remain preserved.</li>
                    <li>District and school administrators will be notified upon activation.</li>
                </ul>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="sa-form-label">Start Year *</label>
                    <input type="number" name="year_start" id="year_start" class="sa-input" value="{{ date('Y') + 1 }}" required min="2020" max="2100" onchange="autoUpdateLabel()">
                </div>
                <div>
                    <label class="sa-form-label">End Year *</label>
                    <input type="number" name="year_end" id="year_end" class="sa-input" value="{{ date('Y') + 2 }}" required min="2021" max="2100" onchange="autoUpdateLabel()">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label class="sa-form-label">Session Label</label>
                <input type="text" name="label" id="session_label" class="sa-input" placeholder="e.g. 2027-2028">
            </div>

            <div style="margin-bottom: 16px;">
                <label class="sa-form-label">Enrollment Window (Normal Start & End) *</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <input type="date" name="enrollment_open_date" class="sa-input" required value="{{ date('Y-m-d') }}">
                    <input type="date" name="enrollment_close_date" class="sa-input" required value="{{ date('Y-m-d', strtotime('+60 days')) }}">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="sa-form-label">Examination Window (Normal Start & End) *</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <input type="date" name="examination_open_date" class="sa-input" required value="{{ date('Y-m-d', strtotime('+90 days')) }}">
                    <input type="date" name="examination_close_date" class="sa-input" required value="{{ date('Y-m-d', strtotime('+150 days')) }}">
                </div>
            </div>

            <!-- Mandatory Confirmation Input -->
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #991b1b; margin-bottom: 6px;">
                    Type <span style="font-family: monospace; background: #fee2e2; padding: 2px 6px; border-radius: 4px; font-size: 14px;">CONFIRM</span> to proceed:
                </label>
                <input type="text" id="confirmText" class="sa-input" placeholder="Type CONFIRM here..." oninput="checkConfirmText()" style="border-color: #fca5a5;">
                <span id="confirmHint" style="font-size: 11px; color: #b91c1c; display: block; margin-top: 4px;">Proceed button remains disabled until CONFIRM is typed.</span>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="sa-btn sa-btn-outline" onclick="closeCreateYearModal()">Cancel</button>
                <button type="submit" id="submitYearBtn" class="sa-btn sa-btn-gold" disabled style="opacity: 0.5; cursor: not-allowed;">
                    Proceed & Create Session
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateYearModal() {
    document.getElementById('createYearModal').style.display = 'flex';
    autoUpdateLabel();
}

function closeCreateYearModal() {
    document.getElementById('createYearModal').style.display = 'none';
}

function autoUpdateLabel() {
    const start = document.getElementById('year_start').value;
    const end = document.getElementById('year_end').value;
    if (start && end) {
        document.getElementById('session_label').value = `${start}-${end}`;
    }
}

function checkConfirmText() {
    const val = document.getElementById('confirmText').value.trim();
    const btn = document.getElementById('submitYearBtn');
    const hint = document.getElementById('confirmHint');

    if (val === 'CONFIRM') {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
        hint.textContent = 'Confirmation validated.';
        hint.style.color = '#15803d';
    } else {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
        hint.textContent = 'Proceed button remains disabled until CONFIRM is typed.';
        hint.style.color = '#b91c1c';
    }
}
</script>
@endsection
