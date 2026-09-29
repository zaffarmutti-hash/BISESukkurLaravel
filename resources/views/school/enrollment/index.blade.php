@extends('layouts.school')

@section('title', 'Enrollment Forms & Candidate List')

@push('styles')
<style>
    .enl-container {
        max-width: 100%;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* ── Top Action Header ── */
    .enl-header-card {
        background: linear-gradient(135deg, #eff6ff 0%, #fafaff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1.25rem;
        box-shadow: 0 4px 15px -3px rgba(15, 23, 42, 0.04);
    }

    .enl-header-info h1 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 0.25rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        letter-spacing: -0.02em;
    }

    .enl-session-badge {
        font-size: 0.75rem;
        font-weight: 700;
        background: #dbeafe;
        color: #1e40af;
        padding: 0.2rem 0.65rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .enl-header-subtitle {
        font-size: 0.875rem;
        color: #64748b;
        margin: 0;
    }

    /* ── TWO MAIN TOP ACTION BUTTONS ── */
    .enl-action-buttons {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        flex-wrap: wrap;
    }

    .btn-add-enrollment {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.7rem 1.35rem;
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        color: #ffffff;
        font-size: 0.9rem;
        font-weight: 700;
        border-radius: 10px;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(30, 64, 175, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    .btn-add-enrollment:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(30, 64, 175, 0.45);
        color: #ffffff;
    }

    .btn-generate-challan {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.7rem 1.35rem;
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff;
        font-size: 0.9rem;
        font-weight: 700;
        border-radius: 10px;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(217, 119, 6, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    .btn-generate-challan:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(217, 119, 6, 0.45);
        color: #ffffff;
    }

    .btn-secondary-pdf {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.7rem 1.1rem;
        background: #ffffff;
        color: #475569;
        font-size: 0.88rem;
        font-weight: 600;
        border-radius: 10px;
        text-decoration: none;
        border: 1px solid #cbd5e1;
        transition: all 0.15s ease;
    }

    .btn-secondary-pdf:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #94a3b8;
    }

    /* ── KPI Stat Cards ── */
    .enl-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 1rem;
    }

    .enl-kpi-card {
        background: linear-gradient(135deg, #eff6ff 0%, #f9faff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.15rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .enl-kpi-card:nth-child(2) { background: linear-gradient(135deg, #effcf7 0%, #f9fdfb 100%); }
    .enl-kpi-card:nth-child(3) { background: linear-gradient(135deg, #fff6e9 0%, #fffdf7 100%); }
    .enl-kpi-card:nth-child(4) { background: linear-gradient(135deg, #fff1f4 0%, #fffafb 100%); }
    .enl-kpi-card:nth-child(5) { background: linear-gradient(135deg, #f2efff 0%, #fbfaff 100%); }

    .enl-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
    }

    .enl-kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .enl-kpi-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .enl-kpi-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.2rem;
    }

    .enl-kpi-value {
        font-size: 1.45rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    /* ── Filter Card ── */
    .enl-filter-card {
        background: linear-gradient(135deg, #f3f8ff 0%, #fcfdff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .enl-filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr auto;
        gap: 0.85rem;
        align-items: flex-end;
    }

    .enl-filter-field {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .enl-filter-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .enl-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .enl-input-wrap svg {
        position: absolute;
        left: 0.85rem;
        width: 16px;
        height: 16px;
        color: #94a3b8;
        pointer-events: none;
    }

    .enl-input {
        width: 100%;
        height: 40px;
        padding: 0 0.85rem 0 2.4rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.85rem;
        color: #0f172a;
        background: #ffffff;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .enl-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .enl-select {
        width: 100%;
        height: 40px;
        padding: 0 0.85rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.85rem;
        color: #0f172a;
        background: #ffffff;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .enl-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .enl-filter-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-filter-submit {
        height: 40px;
        padding: 0 1.25rem;
        background: #1e293b;
        color: #ffffff;
        font-size: 0.85rem;
        font-weight: 700;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: background 0.15s ease;
    }

    .btn-filter-submit:hover {
        background: #0f172a;
    }

    .btn-filter-reset {
        height: 40px;
        padding: 0 0.9rem;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
    }

    .btn-filter-reset:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* ── Table Container ── */
    .enl-table-card {
        background: linear-gradient(135deg, #f3fbf8 0%, #ffffff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
        overflow: hidden;
    }

    .enl-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
        text-align: left;
    }

    .enl-table thead {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .enl-table th {
        padding: 0.875rem 1.25rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }

    .enl-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.12s ease;
    }

    .enl-table tbody tr:hover {
        background: #f8fafc;
    }

    .enl-table td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        color: #334155;
    }

    /* Candidate Identity Cell */
    .enl-candidate-cell {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .enl-candidate-avatar {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: linear-gradient(135deg, #1e40af, #3b82f6);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
        flex-shrink: 0;
        overflow: hidden;
        border: 1px solid #dbeafe;
    }

    .enl-candidate-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .enl-candidate-name {
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
        font-size: 0.92rem;
    }

    .enl-candidate-meta {
        font-size: 0.75rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.15rem;
    }

    /* Enrollment No Badge */
    .enl-num-badge {
        font-family: 'JetBrains Mono', 'Consolas', monospace;
        font-weight: 700;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.8rem;
        display: inline-block;
        letter-spacing: 0.03em;
    }

    .enl-num-active {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .enl-num-pending {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    /* Status Badges */
    .enl-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    .status-enrolled {
        background: #dcfce7;
        color: #15803d;
    }

    .status-needs-challan {
        background: #fef3c7;
        color: #b45309;
    }

    .status-pending-challan {
        background: #ffedd5;
        color: #c2410c;
    }

    .status-submitted {
        background: #e0f2fe;
        color: #0369a1;
    }

    .status-draft {
        background: #f1f5f9;
        color: #475569;
    }

    /* Action Buttons in Rows */
    .enl-row-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.4rem;
    }

    .row-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        transition: all 0.15s ease;
        text-decoration: none;
        cursor: pointer;
    }

    .row-btn:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    .row-btn-pdf:hover {
        background: #eff6ff;
        color: #1e40af;
        border-color: #bfdbfe;
    }

    .row-btn-edit:hover {
        background: #f0fdf4;
        color: #15803d;
        border-color: #bbf7d0;
    }

    .row-btn-delete:hover {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    /* Empty State */
    .enl-empty-state {
        padding: 4rem 1.5rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .enl-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .enl-empty-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.35rem;
    }

    .enl-empty-desc {
        font-size: 0.88rem;
        color: #64748b;
        max-width: 420px;
        margin-bottom: 1.5rem;
    }

    /* Pagination */
    .enl-pagination-wrap {
        padding: 1rem 1.5rem;
        border-top: 1px solid #f1f5f9;
        background: #ffffff;
    }

    @media (max-width: 900px) {
        .enl-filter-grid {
            grid-template-columns: 1fr;
        }
        .enl-header-card {
            flex-direction: column;
            align-items: flex-start;
        }
        .enl-action-buttons {
            width: 100%;
        }
        .btn-add-enrollment, .btn-generate-challan {
            flex: 1;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="enl-container">

    <!-- ── Top Action Header with Two Buttons Above List ── -->
    <div class="enl-header-card">
        <div class="enl-header-info">
            <h1>
                Candidate Enrollment List
                <span class="enl-session-badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Session: {{ $activeYear->label ?? '2026' }}
                </span>
            </h1>
            <p class="enl-header-subtitle">
                Manage candidate enrollments, verify biodata, allotment numbers, and generate bank fee challans.
            </p>
        </div>

        <!-- TWO MAIN REQUIRED BUTTONS: "+ Add Enrollment" & "Generate Challan" -->
        <div class="enl-action-buttons">
            <a href="{{ route('school.students.create') }}" class="btn-add-enrollment" id="btnTopAddEnrollment">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                + Add Enrollment
            </a>

            <a href="{{ route('school.invoices') }}" class="btn-generate-challan" id="btnTopGenerateChallan">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
                Generate Challan
            </a>

            <a href="{{ route('school.documents.enrollment-forms.bulk-pdf', request()->query()) }}" target="_blank" class="btn-secondary-pdf" title="Export current list as PDF">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export PDF
            </a>
        </div>
    </div>

    <!-- ── KPI Stat Summary Cards ── -->
    <div class="enl-kpi-grid">
        <div class="enl-kpi-card">
            <div class="enl-kpi-icon" style="background: #eff6ff; color: #1e40af;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="enl-kpi-info">
                <span class="enl-kpi-label">Total Registered</span>
                <span class="enl-kpi-value">{{ number_format($stats['total'] ?? 0) }}</span>
            </div>
        </div>

        <div class="enl-kpi-card">
            <div class="enl-kpi-icon" style="background: #ecfdf5; color: #047857;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="enl-kpi-info">
                <span class="enl-kpi-label">Enrolled (Allotted)</span>
                <span class="enl-kpi-value">{{ number_format($stats['enrolled'] ?? 0) }}</span>
            </div>
        </div>

        <div class="enl-kpi-card">
            <div class="enl-kpi-icon" style="background: #fffbeb; color: #b45309;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            <div class="enl-kpi-info">
                <span class="enl-kpi-label">Needs Challan</span>
                <span class="enl-kpi-value">{{ number_format($stats['needs_challan'] ?? 0) }}</span>
            </div>
        </div>

        <div class="enl-kpi-card">
            <div class="enl-kpi-icon" style="background: #f5f3ff; color: #6d28d9;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </div>
            <div class="enl-kpi-info">
                <span class="enl-kpi-label">SSC / Matric</span>
                <span class="enl-kpi-value">{{ number_format($stats['ssc'] ?? 0) }}</span>
            </div>
        </div>

        <div class="enl-kpi-card">
            <div class="enl-kpi-icon" style="background: #fdf2f8; color: #be185d;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
            </div>
            <div class="enl-kpi-info">
                <span class="enl-kpi-label">HSC / Inter</span>
                <span class="enl-kpi-value">{{ number_format($stats['hsc'] ?? 0) }}</span>
            </div>
        </div>
    </div>

    <!-- ── Filter & Search Bar ── -->
    <div class="enl-filter-card">
        <form method="GET" action="{{ route('school.students.index') }}" class="enl-filter-grid">
            <div class="enl-filter-field">
                <label class="enl-filter-label">Search Candidate</label>
                <div class="enl-input-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="search" class="enl-input" placeholder="Search by Full Name, Father Name, CNIC, Enrollment #..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>

            <div class="enl-filter-field">
                <label class="enl-filter-label">Class Level</label>
                <select name="class_level" class="enl-select">
                    <option value="">All Classes</option>
                    <option value="ssc_part1" {{ ($filters['class_level'] ?? '') === 'ssc_part1' ? 'selected' : '' }}>SSC Part-I (9th)</option>
                    <option value="ssc_part2" {{ ($filters['class_level'] ?? '') === 'ssc_part2' ? 'selected' : '' }}>SSC Part-II (10th)</option>
                    <option value="hsc_part1" {{ ($filters['class_level'] ?? '') === 'hsc_part1' ? 'selected' : '' }}>HSC Part-I (11th)</option>
                    <option value="hsc_part2" {{ ($filters['class_level'] ?? '') === 'hsc_part2' ? 'selected' : '' }}>HSC Part-II (12th)</option>
                </select>
            </div>

            <div class="enl-filter-field">
                <label class="enl-filter-label">Group</label>
                <select name="group" class="enl-select">
                    <option value="">All Groups</option>
                    <option value="science" {{ ($filters['group'] ?? '') === 'science' ? 'selected' : '' }}>Science</option>
                    <option value="general" {{ ($filters['group'] ?? '') === 'general' ? 'selected' : '' }}>General</option>
                    <option value="arts" {{ ($filters['group'] ?? '') === 'arts' ? 'selected' : '' }}>Arts</option>
                    <option value="commerce" {{ ($filters['group'] ?? '') === 'commerce' ? 'selected' : '' }}>Commerce</option>
                    <option value="pre_medical" {{ ($filters['group'] ?? '') === 'pre_medical' ? 'selected' : '' }}>Pre-Medical</option>
                    <option value="pre_engineering" {{ ($filters['group'] ?? '') === 'pre_engineering' ? 'selected' : '' }}>Pre-Engineering</option>
                </select>
            </div>

            <div class="enl-filter-field">
                <label class="enl-filter-label">Status</label>
                <select name="status" class="enl-select">
                    <option value="">All Statuses</option>
                    <option value="final" {{ ($filters['status'] ?? '') === 'final' ? 'selected' : '' }}>Needs Challan</option>
                    <option value="pending_challan" {{ ($filters['status'] ?? '') === 'pending_challan' ? 'selected' : '' }}>Pending Payment</option>
                    <option value="challan_submitted" {{ ($filters['status'] ?? '') === 'challan_submitted' ? 'selected' : '' }}>Challan Submitted</option>
                    <option value="enrolled" {{ ($filters['status'] ?? '') === 'enrolled' ? 'selected' : '' }}>Enrolled (Allotted)</option>
                </select>
            </div>

            <div class="enl-filter-actions">
                <button type="submit" class="btn-filter-submit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                </button>
                @if(!empty(array_filter($filters ?? [])))
                    <a href="{{ route('school.students.index') }}" class="btn-filter-reset">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- ── Candidates / Enrollment Forms Table ── -->
    <div class="enl-table-card">
        <div style="overflow-x: auto;">
            <table class="enl-table">
                <thead>
                    <tr>
                        <th>Candidate Details</th>
                        <th>Father's Name</th>
                        <th>Class &amp; Group</th>
                        <th>Enrollment No</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $st)
                        @php
                            $rec = $st->currentAcademicRecord;
                            $status = $rec?->status ?? 'final';
                        @endphp
                        <tr>
                            <td>
                                <div class="enl-candidate-cell">
                                    <div class="enl-candidate-avatar">
                                        @if($st->photo_path)
                                            <img src="{{ asset('storage/' . $st->photo_path) }}" alt="{{ $st->full_name }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                                            <span style="display:none;">{{ strtoupper(substr($st->full_name, 0, 1)) }}</span>
                                        @else
                                            <span>{{ strtoupper(substr($st->full_name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="enl-candidate-name">{{ $st->full_name }}</div>
                                        <div class="enl-candidate-meta">
                                            <span>CNIC: {{ $st->cnic ?? $st->b_form ?? '—' }}</span>
                                            @if($st->gr_number)
                                                <span>&bull; GR: {{ $st->gr_number }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div style="font-weight: 600; color: #1e293b;">{{ $st->father_name }}</div>
                                @if($st->father_cnic)
                                    <div style="font-size: 0.75rem; color: #64748b;">{{ $st->father_cnic }}</div>
                                @endif
                            </td>

                            <td>
                                <div style="font-weight: 700; color: #1e40af; text-transform: capitalize;">
                                    {{ ucwords(str_replace('_', ' ', $rec?->class_level ?? '—')) }}
                                </div>
                                <div style="font-size: 0.78rem; color: #64748b; text-transform: capitalize;">
                                    {{ ucwords(str_replace('_', ' ', $rec?->subject_group ?? 'General')) }} ({{ ucfirst($rec?->student_type ?? 'Fresh') }})
                                </div>
                            </td>

                            <td>
                                @if(!empty($st->enrollment_number))
                                    <span class="enl-num-badge enl-num-active">
                                        {{ $st->enrollment_number }}
                                    </span>
                                @else
                                    <span class="enl-num-badge enl-num-pending">
                                        Pending Allotment
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if($status === 'enrolled')
                                    <span class="enl-status-pill status-enrolled">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                        Enrolled
                                    </span>
                                @elseif($status === 'final')
                                    <span class="enl-status-pill status-needs-challan">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                        Needs Challan
                                    </span>
                                @elseif($status === 'pending_challan')
                                    <span class="enl-status-pill status-pending-challan">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        Challan Pending
                                    </span>
                                @elseif($status === 'challan_submitted')
                                    <span class="enl-status-pill status-submitted">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        Challan Submitted
                                    </span>
                                @else
                                    <span class="enl-status-pill status-draft">
                                        {{ ucfirst($status) }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="enl-row-actions">
                                    <!-- Print PDF -->
                                    <a href="{{ route('school.documents.enrollment-form.pdf', $st->id) }}" target="_blank" class="row-btn row-btn-pdf" title="Print Enrollment Form PDF">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                    </a>

                                    <!-- Edit -->
                                    <a href="{{ route('school.students.edit', $st->id) }}" class="row-btn row-btn-edit" title="Edit Candidate">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>

                                    <!-- Delete (only if not locked) -->
                                    @if(!$rec || !$rec->is_locked)
                                        <form method="POST" action="{{ route('school.students.destroy', $st->id) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove candidate {{ addslashes($st->full_name) }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="confirm_final_delete" value="1">
                                            <button type="submit" class="row-btn row-btn-delete" title="Delete Enrollment">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="enl-empty-state">
                                    <div class="enl-empty-icon">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                                    </div>
                                    <h3 class="enl-empty-title">No Candidate Records Found</h3>
                                    <p class="enl-empty-desc">
                                        There are no student enrollment records matching the selected criteria. You can start enrolling candidates or generate bank challans right away.
                                    </p>
                                    <div style="display: flex; gap: 0.85rem; flex-wrap: wrap; justify-content: center;">
                                        <a href="{{ route('school.students.create') }}" class="btn-add-enrollment">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            + Add First Enrollment
                                        </a>
                                        <a href="{{ route('school.invoices') }}" class="btn-generate-challan">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                            Generate Challan
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="enl-pagination-wrap">
                {{ $students->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
