@extends('layouts.school')

@section('content')
<style>
    /* ── Challan View Styles ── */
    .chl-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
    }
    .kpi-card {
        background: linear-gradient(135deg, #eff6ff 0%, #f8faff 100%);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.5rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-subtle);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .kpi-card:nth-child(2) { background: linear-gradient(135deg, #fff7e8 0%, #fffdf7 100%); }
    .kpi-card:nth-child(3) { background: linear-gradient(135deg, #effcf7 0%, #f9fdfb 100%); }
    .kpi-card:nth-child(4) { background: linear-gradient(135deg, #f4f1ff 0%, #fbfaff 100%); }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    .kpi-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .kpi-icon-navy { background: #eff6ff; color: #1e40af; }
    .kpi-icon-amber { background: #fffbeb; color: #d97706; }
    .kpi-icon-green { background: #ecfdf5; color: #059669; }
    .kpi-icon-teal { background: #f0fdfa; color: #0d9488; }
    .kpi-meta { display: flex; flex-direction: column; min-width: 0; }
    .kpi-title { font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
    .kpi-value { font-size: 1.45rem; font-weight: 800; color: var(--text-main); margin-top: 2px; }

    /* Action & Filter Bar */
    .bar-card {
        background: linear-gradient(135deg, #f2f8ff 0%, #fbfdff 100%);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-subtle);
        padding: 1.25rem 1.5rem;
    }
    .filter-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.85rem;
    }
    .filter-select, .filter-input {
        padding: 0.55rem 0.85rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        font-size: 0.84rem;
        font-weight: 500;
        color: var(--text-main);
        background: #f8fafc;
        outline: none;
        transition: border-color 0.15s ease;
    }
    .filter-select:focus, .filter-input:focus {
        border-color: var(--primary);
        background: #ffffff;
    }

    /* Badges */
    .chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .chip-navy { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .chip-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .chip-green { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .chip-red { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .chip-slate { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

    /* Table */
    .table-responsive {
        overflow-x: auto;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        background: linear-gradient(135deg, #f4fbf8 0%, #ffffff 100%);
        box-shadow: var(--shadow-subtle);
    }
    table.chl-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.85rem;
    }
    table.chl-table th {
        background: #f8fafc;
        padding: 0.85rem 1rem;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #475569;
        border-bottom: 1px solid var(--border);
    }
    table.chl-table td {
        padding: 0.95rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    table.chl-table tr:hover td {
        background: #fafafa;
    }

    /* Modal Backdrop */
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        z-index: 100;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }
    .modal-backdrop.active {
        display: flex;
    }
    .modal-box {
        background: #ffffff;
        border-radius: var(--radius-xl);
        max-width: 850px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
    }
    .modal-header {
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }
    .modal-title { font-size: 1.15rem; font-weight: 800; color: var(--text-main); }
    .modal-body { padding: 1.75rem; flex: 1; }
    .modal-footer {
        padding: 1rem 1.75rem;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        background: #fafafa;
    }

    /* Stepper */
    .stepper {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
        margin-bottom: 1.75rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--border);
    }
    .step-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        font-weight: 700;
        color: #94a3b8;
    }
    .step-item.active { color: var(--primary); }
    .step-item.completed { color: #059669; }
    .step-num {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #64748b;
        font-size: 0.75rem;
    }
    .step-item.active .step-num {
        background: var(--primary);
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(30, 64, 175, 0.25);
    }
    .step-item.completed .step-num {
        background: #10b981;
        color: #ffffff;
    }
</style>

<div class="chl-container">
    <!-- Top Header & Action Row -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.02em;">Fee Invoices &amp; Challans</h1>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                Generate official bank challans, monitor clearing status, and download manifests for {{ $school->name }}
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <button type="button" onclick="openChallanDialog('Enrollment')" class="sl-btn-primary" style="background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Generate Enrollment Challan</span>
            </button>
            <button type="button" onclick="openChallanDialog('Examination')" class="sl-btn-primary" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Generate Exam Challan</span>
            </button>
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon-wrap kpi-icon-navy">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <div class="kpi-meta">
                <span class="kpi-title">Total Invoices</span>
                <span class="kpi-value">{{ number_format($kpis['total_invoices']) }}</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-wrap kpi-icon-amber">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="kpi-meta">
                <span class="kpi-title">Pending Payment</span>
                <span class="kpi-value" style="color: #d97706;">{{ number_format($kpis['pending_payment']) }}</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-wrap kpi-icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="kpi-meta">
                <span class="kpi-title">Verified Invoices</span>
                <span class="kpi-value" style="color: #059669;">{{ number_format($kpis['verified']) }}</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-wrap kpi-icon-teal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="kpi-meta">
                <span class="kpi-title">Total Verified Fees</span>
                <span class="kpi-value" style="color: #0d9488;">Rs {{ number_format($kpis['total_collected']) }}</span>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bar-card">
        <form method="GET" action="{{ route('school.enrollment.challans') }}" class="filter-row">
            <!-- Type -->
            <select name="type" class="filter-select" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="enrollment" {{ request('type') === 'enrollment' ? 'selected' : '' }}>Enrollment Challan</option>
                <option value="examination" {{ request('type') === 'examination' ? 'selected' : '' }}>Examination Challan</option>
            </select>

            <!-- Status -->
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending / Submitted</option>
                <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified / Confirmed</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>

            <!-- Class -->
            <select name="class_level" class="filter-select" onchange="this.form.submit()">
                <option value="">All Classes</option>
                @foreach($allowedLevels as $lvl)
                    <option value="{{ $lvl }}" {{ request('class_level') === $lvl ? 'selected' : '' }}>{{ strtoupper($lvl) }}</option>
                @endforeach
            </select>

            <!-- Search -->
            <div style="flex: 1; min-width: 200px; position: relative;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Invoice Number..." class="filter-input" style="width: 100%;" />
            </div>

            <button type="submit" class="sl-btn-primary" style="padding: 0.55rem 1rem;">
                Filter
            </button>
            @if(request()->anyFilled(['type', 'status', 'class_level', 'search']))
                <a href="{{ route('school.enrollment.challans') }}" class="filter-select" style="text-decoration: none; display: flex; align-items: center; color: #ef4444;">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Table Section -->
    <div class="table-responsive">
        <table class="chl-table">
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Type &amp; Program</th>
                    <th>Student Type</th>
                    <th style="text-align: center;">Candidates</th>
                    <th style="text-align: right;">Total Amount</th>
                    <th style="text-align: center;">Fee Phase</th>
                    <th style="text-align: center;">Status</th>
                    <th>Generated Date</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                @if($inv->invoice_type === 'enrollment')
                                    <span class="chip chip-navy" style="font-family: monospace; font-size: 0.82rem;">{{ $inv->invoice_number }}</span>
                                @else
                                    <span class="chip chip-amber" style="font-family: monospace; font-size: 0.82rem;">{{ $inv->invoice_number }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-main);">
                                {{ strtoupper($inv->class_level ?? 'ALL') }} — {{ ucfirst($inv->subject_group ?? 'General') }}
                            </div>
                            <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: capitalize;">
                                {{ $inv->invoice_type }} Fee Challan
                            </div>
                        </td>
                        <td>
                            <span style="font-size: 0.78rem; font-weight: 600; text-transform: capitalize; color: #475569;">
                                {{ $inv->student_type ?? 'Regular' }}
                            </span>
                        </td>
                        <td style="text-align: center; font-weight: 700;">
                            {{ $inv->student_count }}
                        </td>
                        <td style="text-align: right; font-weight: 800; font-family: monospace; color: #1e3a8a;">
                            Rs {{ number_format($inv->total_amount_paisas / 100, 2) }}
                        </td>
                        <td style="text-align: center;">
                            @if(($inv->fee_phase ?? 'normal') === 'grace')
                                <span class="chip chip-amber">Late Fee (Grace)</span>
                            @else
                                <span class="chip chip-slate">Standard Fee</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if(in_array($inv->status, ['confirmed', 'verified']))
                                <span class="chip chip-green">Verified</span>
                            @elseif($inv->status === 'rejected')
                                <span class="chip chip-red">Rejected</span>
                            @else
                                <span class="chip chip-amber">Submitted (Pending)</span>
                            @endif
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.8rem;">
                            {{ $inv->created_at ? $inv->created_at->format('d-M-Y H:i') : '—' }}
                        </td>
                        <td style="text-align: center;">
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                <!-- View Detail -->
                                <button type="button" onclick="viewInvoiceDetail({{ $inv->id }})" title="View Complete Manifest &amp; Detail" style="padding: 5px; border-radius: 6px; border: 1px solid var(--border); background: #ffffff; cursor: pointer; color: #1e40af;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <!-- Download Challan PDF -->
                                <a href="{{ route('school.challan.download-pdf', $inv->id) }}" target="_blank" title="Download 3-Part Bank Challan PDF" style="padding: 5px; border-radius: 6px; border: 1px solid #bfdbfe; background: #eff6ff; color: #1e40af; display: inline-flex;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </a>
                                <!-- Download Student List PDF -->
                                <a href="{{ route('school.challan.download-list', $inv->id) }}" target="_blank" title="Download Official Student Manifest PDF" style="padding: 5px; border-radius: 6px; border: 1px solid #cbd5e1; background: #f8fafc; color: #475569; display: inline-flex;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @if($inv->status === 'rejected' && $inv->rejection_reason)
                        <tr style="background: #fef2f2;">
                            <td colspan="9" style="padding: 0.6rem 1.5rem; border-bottom: 1.5px solid #fecaca;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: #b91c1c;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <strong>Rejection Reason:</strong>
                                    <span>{{ $inv->rejection_reason }}</span>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                            <div style="font-size: 1.1rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">No Invoices Found</div>
                            <p style="font-size: 0.85rem;">There are no generated challans matching the selected filter criteria.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
        <div style="font-size: 0.82rem; color: var(--text-muted);">
            Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }} records
        </div>
        <div>
            {{ $invoices->links() }}
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- CHALLAN DIALOG MODAL (PART TWO SPECIFICATION)                             -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="challanDialogModal">
    <div class="modal-box">
        <!-- Modal Header -->
        <div class="modal-header">
            <div>
                <span class="chip chip-navy" id="dlgTypeBadge">Enrollment Fee Challan</span>
                <h3 class="modal-title" style="margin-top: 4px;">Generate Fee Payment Challan</h3>
            </div>
            <button type="button" onclick="closeChallanDialog()" style="background: none; border: none; font-size: 1.5rem; line-height: 1; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <!-- Stepper Indicator -->
        <div class="stepper" style="margin: 1.25rem 1.75rem 0;">
            <div class="step-item active" id="stepIndicator1">
                <span class="step-num">1</span>
                <span>Parameters</span>
            </div>
            <div style="width: 40px; height: 2px; background: #e2e8f0;"></div>
            <div class="step-item" id="stepIndicator2">
                <span class="step-num">2</span>
                <span>Select Students</span>
            </div>
            <div style="width: 40px; height: 2px; background: #e2e8f0;"></div>
            <div class="step-item" id="stepIndicator3">
                <span class="step-num">3</span>
                <span>Confirmation</span>
            </div>
        </div>

        <!-- Modal Body Container -->
        <div class="modal-body" id="dlgBody">
            <!-- ── STEP 1: PARAMETER SELECTION ── -->
            <div id="step1View">
                <!-- Window Phase Notice Banner -->
                <div id="phaseAlertBox" style="margin-bottom: 1.25rem;"></div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <!-- Class Level -->
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Class Level *</label>
                        <select id="dlgClass" class="filter-select" style="width: 100%;" onchange="onClassSelected()">
                            <option value="">Loading classes...</option>
                        </select>
                    </div>

                    <!-- Subject Group -->
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Subject Group *</label>
                        <select id="dlgGroup" class="filter-select" style="width: 100%;" disabled onchange="onGroupSelected()">
                            <option value="">Select Class First</option>
                        </select>
                    </div>

                    <!-- Student Type -->
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Candidate Type *</label>
                        <select id="dlgStudentType" class="filter-select" style="width: 100%;" disabled onchange="onStudentTypeSelected()">
                            <option value="">Select Group First</option>
                        </select>
                    </div>

                    <!-- Fee Per Student -->
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Board Fee Per Student</label>
                        <input type="text" id="dlgFeeAmount" class="filter-input" readonly value="Calculating..." style="width: 100%; font-weight: 800; color: #1e3a8a; background: #f8fafc;" />
                        <div id="feeNote" style="font-size: 0.72rem; color: #64748b; margin-top: 3px;"></div>
                    </div>
                </div>

                <div id="step1ErrorAlert" style="display: none; padding: 0.75rem 1rem; border-radius: var(--radius-md); background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 0.82rem; margin-bottom: 1rem;"></div>
            </div>

            <!-- ── STEP 2: STUDENT SELECTION ── -->
            <div id="step2View" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <div>
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">Available Candidates for Challan</span>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Toggle switch to include or exclude specific students</div>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" onclick="bulkToggleStudents(true)" style="padding: 4px 10px; border-radius: 6px; border: 1px solid #bfdbfe; background: #eff6ff; color: #1e40af; font-size: 0.76rem; font-weight: 700; cursor: pointer;">Include All</button>
                        <button type="button" onclick="bulkToggleStudents(false)" style="padding: 4px 10px; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #475569; font-size: 0.76rem; font-weight: 700; cursor: pointer;">Exclude All</button>
                    </div>
                </div>

                <!-- Student Table -->
                <div style="max-height: 320px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--radius-md); margin-bottom: 1rem;">
                    <table class="chl-table" style="font-size: 0.8rem;">
                        <thead>
                            <tr style="position: sticky; top: 0; z-index: 10;">
                                <th style="width: 12%; text-align: center;">Include</th>
                                <th style="width: 6%;">#</th>
                                <th style="width: 32%;">Candidate Name</th>
                                <th style="width: 25%;">CNIC / B-Form</th>
                                <th style="width: 25%;">Notice</th>
                            </tr>
                        </thead>
                        <tbody id="studentSelectionTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Live Summary Bottom Box -->
                <div style="background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); border: 1.5px solid #bfdbfe; border-radius: var(--radius-md); padding: 0.85rem 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                        <div>
                            <div style="font-size: 0.82rem; font-weight: 700; color: #1e40af;" id="liveSelectedCount">Selected: 0 of 0 candidates</div>
                            <div style="font-size: 0.75rem; color: #64748b;" id="liveFeeRate">Fee per student: Rs 0.00</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b;">Total Challan Amount</div>
                            <div style="font-size: 1.35rem; font-weight: 900; font-family: monospace; color: #1e3a8a;" id="liveTotalAmount">PKR 0.00</div>
                        </div>
                    </div>
                    <div id="liveGraceNotice" style="display: none; margin-top: 0.5rem; font-size: 0.74rem; color: #b45309; font-weight: 600;">
                        ⚠️ Late fee surcharge is applied per student during this grace period.
                    </div>
                </div>
            </div>

            <!-- ── STEP 3: CONFIRMATION & GENERATE ── -->
            <div id="step3View" style="display: none;">
                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 1.25rem;">
                    <h4 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">
                        Confirm Challan Parameters
                    </h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.85rem; margin-bottom: 1.25rem;">
                        <div><span style="color: #64748b;">Challan Type:</span> <strong id="cfmType" style="color: #1e40af;">—</strong></div>
                        <div><span style="color: #64748b;">Class Level:</span> <strong id="cfmClass">—</strong></div>
                        <div><span style="color: #64748b;">Subject Group:</span> <strong id="cfmGroup">—</strong></div>
                        <div><span style="color: #64748b;">Candidate Type:</span> <strong id="cfmStudentType">—</strong></div>
                        <div><span style="color: #64748b;">Candidates Included:</span> <strong id="cfmIncluded" style="color: #059669;">0</strong></div>
                        <div><span style="color: #64748b;">Candidates Excluded:</span> <strong id="cfmExcluded" style="color: #b91c1c;">0</strong></div>
                        <div><span style="color: #64748b;">Fee Per Student:</span> <strong id="cfmFeePerStudent">Rs 0.00</strong></div>
                        <div><span style="color: #64748b;">Window Phase:</span> <strong id="cfmPhase">Normal</strong></div>
                    </div>

                    <div style="border-top: 2px dashed #cbd5e1; padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a;">Final Total Amount Due to Deposit:</div>
                            <div style="font-size: 0.75rem; color: #64748b;">Payable at any designated branch of ABL / HBL / NBP</div>
                        </div>
                        <div style="font-size: 1.6rem; font-weight: 900; font-family: monospace; color: #1e3a8a;" id="cfmTotal">
                            PKR 0.00
                        </div>
                    </div>
                </div>

                <div id="cfmGraceAlert" style="display: none; padding: 0.75rem 1rem; border-radius: var(--radius-md); background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: 0.82rem; margin-bottom: 1rem;">
                    <strong>Notice:</strong> This challan is being generated during the grace period. The late fee surcharge applies and will be audited.
                </div>

                <div id="step3ErrorAlert" style="display: none; padding: 0.75rem 1rem; border-radius: var(--radius-md); background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 0.82rem; margin-bottom: 1rem;"></div>
            </div>

            <!-- ── SUCCESS STATE ── -->
            <div id="successView" style="display: none; text-align: center; padding: 1.5rem 1rem;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: #ecfdf5; color: #059669; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <h3 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-bottom: 0.25rem;">Challan Generated Successfully!</h3>
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1.25rem;">
                    The payment challan has been created and registered in the BISE Sukkur central audit repository.
                </p>

                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: var(--radius-lg); padding: 1.25rem; max-width: 480px; margin: 0 auto 1.5rem; text-align: left;">
                    <div style="margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                        <span style="font-size: 0.8rem; color: #64748b;">Challan Reference:</span>
                        <strong style="font-family: monospace; font-size: 1.05rem; color: #1e3a8a;" id="sucInvoiceNo">—</strong>
                    </div>
                    <div style="margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                        <span style="font-size: 0.8rem; color: #64748b;">Total Candidates:</span>
                        <strong id="sucCount">0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-size: 0.8rem; color: #64748b;">Total Amount Due:</span>
                        <strong style="color: #059669; font-size: 1.1rem; font-family: monospace;" id="sucAmount">PKR 0.00</strong>
                    </div>
                </div>

                <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <a id="sucBtnChallan" href="#" target="_blank" class="sl-btn-primary" style="background: #1e40af;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>Download Bank Challan PDF (3-Copy)</span>
                    </a>
                    <a id="sucBtnList" href="#" target="_blank" class="sl-btn-primary" style="background: #0d9488;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span>Download Student List PDF</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="modal-footer" id="dlgFooter">
            <!-- Step 1 Buttons -->
            <div id="step1Actions" style="display: flex; gap: 0.75rem;">
                <button type="button" onclick="closeChallanDialog()" class="filter-select" style="cursor: pointer;">Cancel</button>
                <button type="button" id="btnFetchStudents" onclick="fetchStudentsAndProceed()" class="sl-btn-primary" disabled>
                    <span>Fetch Students</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>

            <!-- Step 2 Buttons -->
            <div id="step2Actions" style="display: none; gap: 0.75rem;">
                <button type="button" onclick="goToStep(1)" class="filter-select" style="cursor: pointer;">&larr; Back</button>
                <button type="button" id="btnProceedToConfirm" onclick="goToStep(3)" class="sl-btn-primary">
                    <span>Proceed to Confirm</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>

            <!-- Step 3 Buttons -->
            <div id="step3Actions" style="display: none; gap: 0.75rem;">
                <button type="button" onclick="goToStep(2)" class="filter-select" style="cursor: pointer;">&larr; Back</button>
                <button type="button" id="btnGenerateChallan" onclick="submitChallanGeneration()" class="sl-btn-primary" style="background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);">
                    <span id="btnGenerateText">Generate Challan</span>
                </button>
            </div>

            <!-- Success Actions -->
            <div id="successActions" style="display: none;">
                <button type="button" onclick="window.location.reload()" class="sl-btn-primary" style="background: #1e293b;">
                    Close and Refresh Invoices
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- INVOICE DETAIL MODAL                                                      -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="invoiceDetailModal">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <span class="chip chip-navy" id="dtlNumberChip">INV-000</span>
                <h3 class="modal-title" style="margin-top: 4px;" id="dtlModalTitle">Invoice Manifest &amp; Breakdown</h3>
            </div>
            <button type="button" onclick="closeDetailModal()" style="background: none; border: none; font-size: 1.5rem; line-height: 1; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <div class="modal-body" id="dtlBody">
            <div id="dtlLoadingSpinner" style="text-align: center; padding: 2rem;">
                <div style="font-size: 0.9rem; font-weight: 700; color: #1e40af;">Loading invoice manifest details...</div>
            </div>

            <div id="dtlContent" style="display: none;">
                <!-- Summary Card -->
                <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.25rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; font-size: 0.82rem;">
                        <div><span style="color: #64748b;">Program:</span> <strong id="dtlProgram">—</strong></div>
                        <div><span style="color: #64748b;">Total Due:</span> <strong id="dtlAmount" style="color: #1e3a8a; font-family: monospace;">Rs 0.00</strong></div>
                        <div><span style="color: #64748b;">Candidates:</span> <strong id="dtlCount">0</strong></div>
                        <div><span style="color: #64748b;">Status:</span> <span id="dtlStatusBadge" class="chip chip-navy">Submitted</span></div>
                    </div>
                </div>

                <!-- Verified Alert if enrollment numbers were issued -->
                <div id="dtlVerifiedBanner" style="display: none; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: var(--radius-md); padding: 0.75rem 1rem; margin-bottom: 1rem; color: #065f46; font-size: 0.82rem; font-weight: 600;">
                    ✓ Super Admin has verified this challan. <span id="dtlAllottedCountText">0 enrollment numbers</span> have been issued and registered.
                </div>

                <!-- Student List -->
                <h4 style="font-size: 0.9rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">Associated Candidates</h4>
                <div style="max-height: 280px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--radius-md); margin-bottom: 1rem;">
                    <table class="chl-table" style="font-size: 0.78rem;">
                        <thead>
                            <tr style="position: sticky; top: 0; z-index: 10;">
                                <th>#</th>
                                <th>Candidate Name</th>
                                <th>Father Name</th>
                                <th>CNIC / B-Form</th>
                                <th>Enrollment No.</th>
                                <th>Status</th>
                                <th style="text-align: right;">Fee (Rs)</th>
                            </tr>
                        </thead>
                        <tbody id="dtlStudentTableBody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Action Download Buttons -->
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <a id="dtlBtnChallan" href="#" target="_blank" class="sl-btn-primary" style="background: #1e40af; font-size: 0.78rem;">
                        Download Bank Challan PDF
                    </a>
                    <a id="dtlBtnList" href="#" target="_blank" class="sl-btn-primary" style="background: #0d9488; font-size: 0.78rem;">
                        Download Student Manifest PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ═════════════════════════════════════════════════════════════════════════
    // CHALLAN DIALOG INTERACTIVE LOGIC (PART TWO)
    // ═════════════════════════════════════════════════════════════════════════
    let currentChallanType = 'Enrollment';
    let currentFeeRate = 0;
    let currentPhase = 'normal';
    let eligibleStudentsList = [];
    let studentSelections = []; // array of {id, is_included}

    function openChallanDialog(type) {
        currentChallanType = type || 'Enrollment';
        document.getElementById('dlgTypeBadge').innerText = currentChallanType === 'Enrollment' 
            ? 'Enrollment Fee Challan' 
            : 'Examination Fee Challan';
        document.getElementById('dlgTypeBadge').className = currentChallanType === 'Enrollment' 
            ? 'chip chip-navy' 
            : 'chip chip-amber';

        // Reset step
        goToStep(1);
        document.getElementById('challanDialogModal').classList.add('active');

        // Load classes for this type
        loadEligibleClasses();
    }

    function closeChallanDialog() {
        document.getElementById('challanDialogModal').classList.remove('active');
    }

    function goToStep(step) {
        // Toggle view containers
        document.getElementById('step1View').style.display = (step === 1) ? 'block' : 'none';
        document.getElementById('step2View').style.display = (step === 2) ? 'block' : 'none';
        document.getElementById('step3View').style.display = (step === 3) ? 'block' : 'none';
        document.getElementById('successView').style.display = (step === 4) ? 'block' : 'none';

        // Toggle action buttons
        document.getElementById('step1Actions').style.display = (step === 1) ? 'flex' : 'none';
        document.getElementById('step2Actions').style.display = (step === 2) ? 'flex' : 'none';
        document.getElementById('step3Actions').style.display = (step === 3) ? 'flex' : 'none';
        document.getElementById('successActions').style.display = (step === 4) ? 'block' : 'none';

        // Update step indicator
        [1, 2, 3].forEach(s => {
            const el = document.getElementById('stepIndicator' + s);
            if (!el) return;
            el.classList.remove('active', 'completed');
            if (s === step) {
                el.classList.add('active');
            } else if (s < step) {
                el.classList.add('completed');
            }
        });

        if (step === 3) {
            prepareStep3Summary();
        }
    }

    async function loadEligibleClasses() {
        const sel = document.getElementById('dlgClass');
        sel.innerHTML = '<option value="">Loading eligible classes...</option>';
        sel.disabled = true;

        try {
            const res = await fetch(`{{ route('school.challan.eligible-classes') }}?type=${currentChallanType}`);
            const data = await res.json();

            if (data.classes && data.classes.length > 0) {
                sel.innerHTML = '<option value="">-- Select Class --</option>' + 
                    data.classes.map(c => `<option value="${c}">${c.toUpperCase()}</option>`).join('');
                sel.disabled = false;
                document.getElementById('step1ErrorAlert').style.display = 'none';
            } else {
                sel.innerHTML = '<option value="">No eligible classes found</option>';
                document.getElementById('step1ErrorAlert').style.display = 'block';
                document.getElementById('step1ErrorAlert').innerText = 
                    'No eligible candidates found for any class. Either all eligible students already have an active challan, or student forms are in draft status.';
            }
        } catch (e) {
            sel.innerHTML = '<option value="">Error loading classes</option>';
        }
    }

    async function onClassSelected() {
        const classLevel = document.getElementById('dlgClass').value;
        const grp = document.getElementById('dlgGroup');
        const stType = document.getElementById('dlgStudentType');

        grp.innerHTML = '<option value="">Loading groups...</option>';
        grp.disabled = true;
        stType.innerHTML = '<option value="">Select Group First</option>';
        stType.disabled = true;
        document.getElementById('btnFetchStudents').disabled = true;

        if (!classLevel) return;

        try {
            const res = await fetch(`{{ route('school.challan.eligible-groups') }}?type=${currentChallanType}&class_level=${classLevel}`);
            const data = await res.json();

            if (data.groups && data.groups.length > 0) {
                grp.innerHTML = '<option value="">-- Select Group --</option>' + 
                    data.groups.map(g => `<option value="${g}">${g.charAt(0).toUpperCase() + g.slice(1)}</option>`).join('');
                grp.disabled = false;
            } else {
                grp.innerHTML = '<option value="">No groups with eligible candidates</option>';
            }
        } catch (e) {
            grp.innerHTML = '<option value="">Error loading groups</option>';
        }
    }

    async function onGroupSelected() {
        const classLevel = document.getElementById('dlgClass').value;
        const group = document.getElementById('dlgGroup').value;
        const stType = document.getElementById('dlgStudentType');

        stType.innerHTML = '<option value="">Loading student types...</option>';
        stType.disabled = true;
        document.getElementById('btnFetchStudents').disabled = true;

        if (!group) return;

        try {
            const res = await fetch(`{{ route('school.challan.eligible-types') }}?type=${currentChallanType}&class_level=${classLevel}&group=${group}`);
            const data = await res.json();

            if (data.student_types && data.student_types.length > 0) {
                stType.innerHTML = '<option value="">-- Select Type --</option>' + 
                    data.student_types.map(t => `<option value="${t}">${t.charAt(0).toUpperCase() + t.slice(1)}</option>`).join('');
                stType.disabled = false;
            } else {
                stType.innerHTML = '<option value="">No matching student types</option>';
            }
        } catch (e) {
            stType.innerHTML = '<option value="">Error loading types</option>';
        }
    }

    async function onStudentTypeSelected() {
        const classLevel = document.getElementById('dlgClass').value;
        const studentType = document.getElementById('dlgStudentType').value;
        const feeInput = document.getElementById('dlgFeeAmount');
        const feeNote = document.getElementById('feeNote');
        const alertBox = document.getElementById('phaseAlertBox');

        if (!studentType) {
            document.getElementById('btnFetchStudents').disabled = true;
            return;
        }

        feeInput.value = 'Loading board fee...';

        try {
            const res = await fetch(`{{ route('school.challan.fee-rate') }}?type=${currentChallanType}&class_level=${classLevel}&student_type=${studentType}`);
            const data = await res.json();

            if (!data.configured) {
                feeInput.value = 'Not Configured';
                alertBox.innerHTML = `
                    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: var(--radius-md); font-size: 0.82rem;">
                        <strong>Fee Not Configured:</strong> Fee rate has not been configured by Super Admin for this combination. Cannot generate challan.
                    </div>
                `;
                document.getElementById('btnFetchStudents').disabled = true;
                return;
            }

            currentFeeRate = data.applicable_fee;
            currentPhase = data.phase;

            feeInput.value = `PKR ${data.applicable_fee.toFixed(2)}`;

            if (data.phase === 'grace') {
                feeNote.innerHTML = `<span style="color: #b45309; font-weight: 700;">Includes late fee surcharge of Rs ${data.late_surcharge.toFixed(2)} (Standard was Rs ${data.base_fee.toFixed(2)})</span>`;
                alertBox.innerHTML = `
                    <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 0.75rem 1rem; border-radius: var(--radius-md); font-size: 0.82rem;">
                        <strong>Grace Period Active:</strong> Late fee applies. Deadline: ${data.grace_end || 'Closing Soon'}.
                    </div>
                `;
            } else if (data.phase === 'normal') {
                feeNote.innerText = 'Standard board examination & enrollment fee';
                alertBox.innerHTML = `
                    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 0.75rem 1rem; border-radius: var(--radius-md); font-size: 0.82rem;">
                        <strong>Normal Phase:</strong> Standard fee applies. Regular window closes ${data.normal_end || 'at scheduled deadline'}.
                    </div>
                `;
            } else {
                alertBox.innerHTML = `
                    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: var(--radius-md); font-size: 0.82rem;">
                        <strong>Window Closed:</strong> The deadline has passed. Cannot generate challan.
                    </div>
                `;
                document.getElementById('btnFetchStudents').disabled = true;
                return;
            }

            document.getElementById('btnFetchStudents').disabled = false;
        } catch (e) {
            feeInput.value = 'Error';
        }
    }

    async function fetchStudentsAndProceed() {
        const btn = document.getElementById('btnFetchStudents');
        btn.disabled = true;
        btn.innerHTML = '<span>Fetching Candidates...</span>';

        const classLevel = document.getElementById('dlgClass').value;
        const group = document.getElementById('dlgGroup').value;
        const studentType = document.getElementById('dlgStudentType').value;

        try {
            const res = await fetch(`{{ route('school.challan.eligible-students') }}?type=${currentChallanType}&class_level=${classLevel}&group=${group}&student_type=${studentType}`);
            const data = await res.json();

            btn.disabled = false;
            btn.innerHTML = '<span>Fetch Students</span> <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>';

            if (!data.students || data.students.length === 0) {
                document.getElementById('step1ErrorAlert').style.display = 'block';
                document.getElementById('step1ErrorAlert').innerText = 
                    'All eligible candidates for this combination already have an active challan, or no candidates match.';
                return;
            }

            eligibleStudentsList = data.students;

            // Initialize all toggles to ON by default
            studentSelections = eligibleStudentsList.map(s => ({ id: s.student_id, is_included: true }));

            renderStudentSelectionTable();
            updateLiveSummary();
            goToStep(2);

        } catch (e) {
            btn.disabled = false;
            btn.innerHTML = '<span>Fetch Students</span>';
        }
    }

    function renderStudentSelectionTable() {
        const tbody = document.getElementById('studentSelectionTableBody');
        tbody.innerHTML = eligibleStudentsList.map((st, i) => {
            const isInc = studentSelections.find(s => s.id === st.student_id)?.is_included ?? true;
            return `
                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" id="chk_st_${st.student_id}" ${isInc ? 'checked' : ''} onchange="toggleStudent(${st.student_id}, this.checked)" style="width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer;" />
                    </td>
                    <td>${i + 1}</td>
                    <td>
                        <strong style="color: #0f172a;">${st.full_name}</strong>
                        <div style="font-size: 0.72rem; color: #64748b;">S/D/O ${st.father_name}</div>
                    </td>
                    <td style="font-family: monospace;">${st.cnic || '—'}</td>
                    <td>
                        ${st.previously_excluded ? '<span class="chip chip-amber">Previously Excluded</span>' : '<span style="color: #94a3b8; font-size: 0.75rem;">Eligible</span>'}
                    </td>
                </tr>
            `;
        }).join('');
    }

    function toggleStudent(studentId, isChecked) {
        const item = studentSelections.find(s => s.id === studentId);
        if (item) {
            item.is_included = isChecked;
        }
        updateLiveSummary();
    }

    function bulkToggleStudents(includeAll) {
        studentSelections.forEach(s => s.is_included = includeAll);
        eligibleStudentsList.forEach(st => {
            const chk = document.getElementById('chk_st_' + st.student_id);
            if (chk) chk.checked = includeAll;
        });
        updateLiveSummary();
    }

    function updateLiveSummary() {
        const includedCount = studentSelections.filter(s => s.is_included).length;
        const totalCount = studentSelections.length;
        const totalAmount = includedCount * currentFeeRate;

        document.getElementById('liveSelectedCount').innerText = `Selected: ${includedCount} of ${totalCount} candidates`;
        document.getElementById('liveFeeRate').innerText = `Fee per candidate: Rs ${currentFeeRate.toFixed(2)}`;
        document.getElementById('liveTotalAmount').innerText = `PKR ${totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;

        document.getElementById('btnProceedToConfirm').disabled = (includedCount === 0);
        document.getElementById('liveGraceNotice').style.display = (currentPhase === 'grace') ? 'block' : 'none';
    }

    function prepareStep3Summary() {
        const includedCount = studentSelections.filter(s => s.is_included).length;
        const excludedCount = studentSelections.length - includedCount;
        const totalAmount = includedCount * currentFeeRate;

        document.getElementById('cfmType').innerText = currentChallanType + ' Fee';
        document.getElementById('cfmClass').innerText = document.getElementById('dlgClass').value.toUpperCase();
        document.getElementById('cfmGroup').innerText = document.getElementById('dlgGroup').value;
        document.getElementById('cfmStudentType').innerText = document.getElementById('dlgStudentType').value;
        document.getElementById('cfmIncluded').innerText = includedCount + ' Candidates';
        document.getElementById('cfmExcluded').innerText = excludedCount + ' Candidates';
        document.getElementById('cfmFeePerStudent').innerText = `Rs ${currentFeeRate.toFixed(2)}`;
        document.getElementById('cfmPhase').innerText = (currentPhase === 'grace') ? 'Grace Period (Late Fee)' : 'Normal Phase';
        document.getElementById('cfmTotal').innerText = `PKR ${totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;

        document.getElementById('cfmGraceAlert').style.display = (currentPhase === 'grace') ? 'block' : 'none';
        document.getElementById('step3ErrorAlert').style.display = 'none';

        document.getElementById('btnGenerateText').innerText = `Generate Challan (PKR ${totalAmount.toFixed(0)})`;
    }

    async function submitChallanGeneration() {
        const btn = document.getElementById('btnGenerateChallan');
        btn.disabled = true;
        document.getElementById('btnGenerateText').innerText = 'Generating Challan & Sequenced Manifest...';

        const payload = {
            type: currentChallanType,
            class_level: document.getElementById('dlgClass').value,
            group: document.getElementById('dlgGroup').value,
            student_type: document.getElementById('dlgStudentType').value,
            fee_per_student: currentFeeRate,
            student_selections: studentSelections,
        };

        try {
            const res = await fetch(`{{ route('school.challan.generate') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (data.success) {
                document.getElementById('sucInvoiceNo').innerText = data.invoice_number;
                document.getElementById('sucCount').innerText = `${data.student_count} Candidates`;
                document.getElementById('sucAmount').innerText = `PKR ${data.total_amount.toFixed(2)}`;
                document.getElementById('sucBtnChallan').href = data.download_challan_url;
                document.getElementById('sucBtnList').href = data.download_list_url;

                goToStep(4);
            } else {
                btn.disabled = false;
                document.getElementById('btnGenerateText').innerText = 'Generate Challan';
                document.getElementById('step3ErrorAlert').style.display = 'block';
                document.getElementById('step3ErrorAlert').innerText = data.message || 'Error occurred while generating challan.';
            }
        } catch (e) {
            btn.disabled = false;
            document.getElementById('btnGenerateText').innerText = 'Generate Challan';
            document.getElementById('step3ErrorAlert').style.display = 'block';
            document.getElementById('step3ErrorAlert').innerText = 'Network or server error while generating challan.';
        }
    }

    // ═════════════════════════════════════════════════════════════════════════
    // INVOICE DETAIL MODAL
    // ═════════════════════════════════════════════════════════════════════════
    async function viewInvoiceDetail(invoiceId) {
        document.getElementById('invoiceDetailModal').classList.add('active');
        document.getElementById('dtlLoadingSpinner').style.display = 'block';
        document.getElementById('dtlContent').style.display = 'none';

        try {
            const res = await fetch(`/school/challans/${invoiceId}/detail`);
            const data = await res.json();

            document.getElementById('dtlLoadingSpinner').style.display = 'none';
            document.getElementById('dtlContent').style.display = 'block';

            document.getElementById('dtlNumberChip').innerText = data.invoice_number;
            document.getElementById('dtlProgram').innerText = `${data.class_level.toUpperCase()} — ${data.subject_group} (${data.student_type})`;
            document.getElementById('dtlAmount').innerText = `PKR ${data.total_amount.toFixed(2)}`;
            document.getElementById('dtlCount').innerText = `${data.student_count} Candidates`;
            document.getElementById('dtlStatusBadge').innerText = data.status.toUpperCase();

            // Allotted banner
            if (data.allotted_count > 0) {
                document.getElementById('dtlVerifiedBanner').style.display = 'block';
                document.getElementById('dtlAllottedCountText').innerText = `${data.allotted_count} official enrollment numbers`;
            } else {
                document.getElementById('dtlVerifiedBanner').style.display = 'none';
            }

            // Student table
            const allStudents = [...(data.included_students || []), ...(data.excluded_students || [])];
            document.getElementById('dtlStudentTableBody').innerHTML = allStudents.map((st, i) => `
                <tr style="${st.is_included ? '' : 'background: #fef2f2; color: #94a3b8;'}">
                    <td>${i + 1}</td>
                    <td><strong>${st.full_name}</strong></td>
                    <td>${st.father_name}</td>
                    <td style="font-family: monospace;">${st.cnic}</td>
                    <td style="font-family: monospace;">${st.enrollment_number}</td>
                    <td>${st.is_included ? '<span class="chip chip-green">Included</span>' : '<span class="chip chip-red">Excluded</span>'}</td>
                    <td style="text-align: right; font-family: monospace;">Rs ${st.amount.toFixed(2)}</td>
                </tr>
            `).join('');

            document.getElementById('dtlBtnChallan').href = data.download_challan_url;
            document.getElementById('dtlBtnList').href = data.download_list_url;

        } catch (e) {
            document.getElementById('dtlLoadingSpinner').innerHTML = '<div style="color: #ef4444;">Error loading invoice detail.</div>';
        }
    }

    function closeDetailModal() {
        document.getElementById('invoiceDetailModal').classList.remove('active');
    }
</script>
@endpush
@endsection
