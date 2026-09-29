@extends('layouts.school')

@section('title', 'Examination Forms Portal')

@section('content')
<style>
    /* ── Examination Forms Portal Styles ── */
    .ef-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
    }
    .kpi-card {
        background: linear-gradient(135deg, #eff6ff 0%, #f8faff 100%);
        border-radius: var(--radius-lg, 12px);
        padding: 1.25rem 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .kpi-card:nth-child(2) { background: linear-gradient(135deg, #f2efff 0%, #fbfaff 100%); }
    .kpi-card:nth-child(3) { background: linear-gradient(135deg, #effcf7 0%, #f9fdfb 100%); }
    .kpi-card:nth-child(4) { background: linear-gradient(135deg, #fff7e8 0%, #fffdf7 100%); }
    .kpi-card:nth-child(5) { background: linear-gradient(135deg, #fff1f4 0%, #fffafb 100%); }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08);
    }
    .kpi-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .kpi-icon-navy { background: #eff6ff; color: #1e40af; }
    .kpi-icon-violet { background: #f5f3ff; color: #7c3aed; }
    .kpi-icon-green { background: #ecfdf5; color: #059669; }
    .kpi-icon-amber { background: #fffbeb; color: #d97706; }
    .kpi-icon-rose { background: #fff1f2; color: #e11d48; }

    .kpi-meta { display: flex; flex-direction: column; min-width: 0; }
    .kpi-title { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
    .kpi-value { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-top: 2px; }

    /* Action & Filter Bar */
    .bar-card {
        background: linear-gradient(135deg, #f4f1ff 0%, #fcfbff 100%);
        border-radius: var(--radius-lg, 12px);
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
    }

    .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        justify-content: space-between;
    }

    .filter-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        align-items: center;
        flex: 1;
    }

    .ef-input, .ef-select {
        padding: 0.55rem 0.85rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.85rem;
        color: #0f172a;
        background: #fff;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .ef-input:focus, .ef-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .ef-tabs {
        display: flex;
        gap: 4px;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 8px;
    }
    .ef-tab {
        padding: 0.45rem 1rem;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        text-decoration: none;
        color: #64748b;
        transition: all 0.15s ease;
    }
    .ef-tab.active {
        background: #fff;
        color: #1e40af;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* Table */
    .ef-table-wrap {
        background: linear-gradient(135deg, #f3fbf8 0%, #ffffff 100%);
        border-radius: var(--radius-lg, 12px);
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    .ef-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
        text-align: left;
    }
    .ef-table th {
        background: #f8fafc;
        padding: 0.85rem 1rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e2e8f0;
    }
    .ef-table td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
    }
    .ef-table tr:hover td {
        background: #f8fafc;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .badge-draft { background: #f1f5f9; color: #475569; }
    .badge-final { background: #dbeafe; color: #1e40af; }
    .badge-submitted { background: #e0e7ff; color: #4338ca; }
    .badge-confirmed { background: #dcfce7; color: #166534; }
    .badge-rejected { background: #fee2e2; color: #991b1b; }

    .btn-create-exam {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 1.15rem;
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        color: #ffffff;
        font-size: 0.85rem;
        font-weight: 700;
        border-radius: 8px;
        text-decoration: none;
        box-shadow: 0 4px 10px rgba(30, 64, 175, 0.25);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .btn-create-exam:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(30, 64, 175, 0.35);
        color: #fff;
    }

    .action-btn {
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        transition: all 0.15s ease;
    }
    .action-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
</style>

<div class="ef-container">
    <!-- Header with Breadcrumb and CTA -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0;">Examination Forms Management</h1>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Manage annual board examination candidacies, submit finalized entries, and review seat alloted slips.</p>
        </div>
        <div style="display: flex; gap: 0.6rem;">
            <a href="{{ route('school.examination.gap-report') }}" class="action-btn" style="border-color: #fde68a; background: #fffbeb; color: #b45309;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span>Gap Report ({{ $stats['gap_count'] ?? 0 }})</span>
            </a>
            <a href="{{ route('school.examination.form.create') }}" class="btn-create-exam">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>+ New Exam Form</span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon-wrap kpi-icon-navy">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path d="M12 14l-9 5 9 5 9-5"/></svg>
            </div>
            <div class="kpi-meta">
                <span class="kpi-title">SSC Candidates</span>
                <span class="kpi-value">{{ number_format($stats['ssc_count'] ?? 0) }}</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-wrap kpi-icon-violet">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/></svg>
            </div>
            <div class="kpi-meta">
                <span class="kpi-title">HSC Candidates</span>
                <span class="kpi-value">{{ number_format($stats['hsc_count'] ?? 0) }}</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-wrap kpi-icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="kpi-meta">
                <span class="kpi-title">Fees Verified</span>
                <span class="kpi-value">{{ number_format($stats['fees_paid'] ?? 0) }}</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-wrap kpi-icon-amber">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="kpi-meta">
                <span class="kpi-title">Unpaid / In Process</span>
                <span class="kpi-value">{{ number_format($stats['fees_unpaid'] ?? 0) }}</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-wrap kpi-icon-rose">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="18" y1="8" x2="23" y2="13"/><line x1="23" y1="8" x2="18" y2="13"/></svg>
            </div>
            <div class="kpi-meta">
                <span class="kpi-title">Missing Exam Form</span>
                <span class="kpi-value">{{ number_format($stats['gap_count'] ?? 0) }}</span>
            </div>
        </div>
    </div>

    <!-- Filter and Tabs Bar -->
    <div class="bar-card">
        <form method="GET" action="{{ route('school.examination.forms') }}" class="filter-row">
            <div class="filter-group">
                <!-- Search -->
                <input type="text" name="search" class="ef-input" placeholder="Search candidate, CNIC, Enrollment #..." value="{{ request('search') }}" style="min-width: 240px;">

                <!-- Class Level -->
                <select name="class_level" class="ef-select">
                    <option value="">All Class Levels</option>
                    <option value="ssc_part1" {{ request('class_level') === 'ssc_part1' ? 'selected' : '' }}>SSC Part-I (9th)</option>
                    <option value="ssc_part2" {{ request('class_level') === 'ssc_part2' ? 'selected' : '' }}>SSC Part-II (10th)</option>
                    <option value="hsc_part1" {{ request('class_level') === 'hsc_part1' ? 'selected' : '' }}>HSC Part-I (11th)</option>
                    <option value="hsc_part2" {{ request('class_level') === 'hsc_part2' ? 'selected' : '' }}>HSC Part-II (12th)</option>
                </select>

                <!-- Subject Group -->
                <select name="group" class="ef-select">
                    <option value="">All Groups</option>
                    <option value="Science" {{ request('group') === 'Science' ? 'selected' : '' }}>Science</option>
                    <option value="General" {{ request('group') === 'General' ? 'selected' : '' }}>General</option>
                    <option value="Pre-Medical" {{ request('group') === 'Pre-Medical' ? 'selected' : '' }}>Pre-Medical</option>
                    <option value="Pre-Engineering" {{ request('group') === 'Pre-Engineering' ? 'selected' : '' }}>Pre-Engineering</option>
                    <option value="Commerce" {{ request('group') === 'Commerce' ? 'selected' : '' }}>Commerce</option>
                    <option value="Humanities" {{ request('group') === 'Humanities' ? 'selected' : '' }}>Humanities</option>
                </select>

                <input type="hidden" name="tab" value="{{ $tab ?? 'draft' }}">

                <button type="submit" class="action-btn" style="background: #1e40af; color: #fff; border: none; font-weight: 700;">Filter</button>
                @if(request()->anyFilled(['search', 'class_level', 'group', 'status']))
                    <a href="{{ route('school.examination.forms', ['tab' => $tab ?? 'draft']) }}" class="action-btn" style="color: #64748b;">Reset</a>
                @endif
            </div>

            <!-- Tab Toggle -->
            <div class="ef-tabs">
                <a href="{{ route('school.examination.forms', array_merge(request()->except('tab', 'page'), ['tab' => 'draft'])) }}" class="ef-tab {{ ($tab ?? 'draft') === 'draft' ? 'active' : '' }}">
                    Draft Forms ({{ $draftCount ?? 0 }})
                </a>
                <a href="{{ route('school.examination.forms', array_merge(request()->except('tab', 'page'), ['tab' => 'final'])) }}" class="ef-tab {{ ($tab ?? '') === 'final' ? 'active' : '' }}">
                    Final / Submitted ({{ $finalCount ?? 0 }})
                </a>
            </div>
        </form>
    </div>

    <!-- Examination Forms Data Table -->
    <div class="ef-table-wrap">
        <table class="ef-table">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Enrollment #</th>
                    <th>Class / Group</th>
                    <th>Center</th>
                    <th>Seat / Roll #</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($examForms as $form)
                <tr>
                    <td>
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">
                            {{ $form->student->full_name ?? '—' }}
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                            S/O, D/O: {{ $form->student->father_name ?? '—' }} &bull; CNIC: {{ $form->student->cnic ?? $form->student->b_form ?? '—' }}
                        </div>
                    </td>
                    <td>
                        <span style="font-family: monospace; font-size: 0.82rem; font-weight: 700; color: #1e40af;">
                            {{ $form->student->enrollment_number ?? 'Pending' }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #334155;">
                            {{ ucwords(str_replace('_', ' ', $form->studentAcademicRecord->class_level ?? '')) }}
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b;">
                            {{ $form->studentAcademicRecord->subject_group ?? 'General' }}
                        </div>
                    </td>
                    <td>
                        <span style="font-size: 0.8rem; color: #475569;">
                            {{ $form->examCenter->name ?? 'Board Assigned' }}
                        </span>
                    </td>
                    <td>
                        @if($form->seat_number)
                            <span style="font-family: monospace; font-weight: 800; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem;">
                                {{ $form->seat_number }}
                            </span>
                        @else
                            <span style="color: #94a3b8; font-size: 0.78rem;">Not Alloted</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $st = $form->status ?? 'draft';
                        @endphp
                        <span class="badge badge-{{ $st }}">{{ ucfirst($st) }}</span>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 4px; align-items: center;">
                            @if($form->status === 'draft')
                                <a href="{{ route('school.examination.form.edit', $form->id) }}" class="action-btn">Edit</a>
                                <form method="POST" action="{{ route('school.examination.form.save-final', $form->id) }}" style="display:inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="action-btn" style="background: #1e40af; color: #fff; border: none;" onclick="return confirm('Finalize this examination form?')">Finalize</button>
                                </form>
                            @else
                                <a href="{{ route('school.examination.form.show', $form->id) }}" class="action-btn">View</a>
                                @if($form->seat_number)
                                    <a href="{{ route('school.documents.exam-slip', $form->id) }}" target="_blank" class="action-btn" style="color: #059669; border-color: #a7f3d0;">Slip</a>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 0.75rem auto; display: block; opacity: 0.6;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                        <div style="font-size: 0.95rem; font-weight: 600; color: #475569;">No examination forms found</div>
                        <p style="font-size: 0.8rem; color: #94a3b8; margin: 0.25rem 0 1rem 0;">Start registering candidates by creating an examination form.</p>
                        <a href="{{ route('school.examination.form.create') }}" class="btn-create-exam" style="font-size: 0.8rem; padding: 0.45rem 0.9rem;">
                            + Add Candidate Exam Form
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($examForms->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0;">
            {{ $examForms->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
