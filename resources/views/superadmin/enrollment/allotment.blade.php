@extends('layouts.superadmin')

@section('content')
<style>
    .alt-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* KPI Summary */
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
    }
    .kpi-item {
        background: #ffffff;
        border-radius: var(--radius-lg, 14px);
        padding: 1.25rem 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Filters Card */
    .filter-card {
        background: #ffffff;
        border-radius: var(--radius-lg, 14px);
        border: 1px solid #e2e8f0;
        padding: 1.25rem;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.75rem;
        align-items: center;
    }
    .sa-input, .sa-select {
        width: 100%;
        padding: 0.55rem 0.85rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.84rem;
        background: #f8fafc;
        outline: none;
    }
    .sa-input:focus, .sa-select:focus {
        border-color: #1e40af;
        background: #ffffff;
    }

    /* Chips */
    .num-chip {
        font-family: monospace;
        font-size: 0.85rem;
        font-weight: 800;
        color: #1e40af;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .num-chip:hover {
        background: #dbeafe;
    }
    .sa-badge {
        padding: 0.2rem 0.55rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 700;
        display: inline-block;
    }
    .badge-auto { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .badge-manual { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }

    /* Table */
    .table-container {
        background: #ffffff;
        border-radius: var(--radius-lg, 14px);
        border: 1px solid #e2e8f0;
        overflow-x: auto;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }
    table.alt-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.84rem;
        text-align: left;
    }
    table.alt-table th {
        background: #f8fafc;
        padding: 0.85rem 1rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
    }
    table.alt-table td {
        padding: 0.9rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    table.alt-table tr:hover td {
        background: #fafafa;
    }

    /* Manual Section */
    .manual-box {
        background: #ffffff;
        border-radius: var(--radius-lg, 14px);
        border: 1.5px solid #fed7aa;
        padding: 1.5rem;
        box-shadow: 0 4px 14px rgba(249, 115, 22, 0.05);
    }
</style>

<div class="alt-container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">
                Official Enrollment Registry &amp; Allotment History
            </h1>
            <p style="font-size: 0.85rem; color: #64748b; margin-top: 2px;">
                Central database of official student enrollment numbers issued by BISE Sukkur
            </p>
        </div>
        <div>
            <span style="font-size: 0.8rem; font-weight: 700; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.4rem 0.85rem; border-radius: 9999px;">
                Active Session: {{ $activeYear->label ?? '2026' }}
            </span>
        </div>
    </div>

    <!-- 3 KPI Cards -->
    <div class="kpi-row">
        <div class="kpi-item">
            <div class="kpi-icon" style="background: #eff6ff; color: #1e40af;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <div style="font-size: 0.74rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Registered</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #0f172a;">{{ number_format($stats['total_students']) }}</div>
            </div>
        </div>

        <div class="kpi-item">
            <div class="kpi-icon" style="background: #ecfdf5; color: #059669;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div style="font-size: 0.74rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Numbers Issued</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #059669;">{{ number_format($stats['allotted']) }}</div>
            </div>
        </div>

        <div class="kpi-item">
            <div class="kpi-icon" style="background: #fffbeb; color: #d97706;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div style="font-size: 0.74rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Pending Allotment</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #d97706;">{{ number_format($stats['pending']) }}</div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="filter-card">
        <form method="GET" action="{{ route('superadmin.allotment-history') }}">
            <div class="filter-grid">
                <!-- District -->
                <select name="district_id" class="sa-select" onchange="this.form.submit()">
                    <option value="">All Districts</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}" {{ request('district_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>

                <!-- School -->
                <select name="school_id" class="sa-select" onchange="this.form.submit()">
                    <option value="">All Schools</option>
                    @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }} ({{ $sch->username }})</option>
                    @endforeach
                </select>

                <!-- Class -->
                <select name="class_level" class="sa-select" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    <option value="ssc_part1" {{ request('class_level') === 'ssc_part1' ? 'selected' : '' }}>SSC-I (Class 9)</option>
                    <option value="ssc_part2" {{ request('class_level') === 'ssc_part2' ? 'selected' : '' }}>SSC-II (Class 10)</option>
                    <option value="hsc_part1" {{ request('class_level') === 'hsc_part1' ? 'selected' : '' }}>HSC-I (Class 11)</option>
                    <option value="hsc_part2" {{ request('class_level') === 'hsc_part2' ? 'selected' : '' }}>HSC-II (Class 12)</option>
                </select>

                <!-- Group -->
                <select name="group" class="sa-select" onchange="this.form.submit()">
                    <option value="">All Groups</option>
                    <option value="science" {{ request('group') === 'science' ? 'selected' : '' }}>Science</option>
                    <option value="arts" {{ request('group') === 'arts' ? 'selected' : '' }}>Arts</option>
                    <option value="commerce" {{ request('group') === 'commerce' ? 'selected' : '' }}>Commerce</option>
                    <option value="general" {{ request('group') === 'general' ? 'selected' : '' }}>General</option>
                    <option value="pre_engineering" {{ request('group') === 'pre_engineering' ? 'selected' : '' }}>Pre-Engineering</option>
                    <option value="pre_medical" {{ request('group') === 'pre_medical' ? 'selected' : '' }}>Pre-Medical</option>
                </select>

                <!-- Search -->
                <div style="grid-column: span 2;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Enrollment No, Candidate Name, or CNIC..." class="sa-input" />
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" style="padding: 0.55rem 1.1rem; background: #1e40af; color: #ffffff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
                        Filter
                    </button>
                    @if(request()->anyFilled(['district_id', 'school_id', 'class_level', 'group', 'search']))
                        <a href="{{ route('superadmin.allotment-history') }}" class="sa-select" style="width: auto; text-decoration: none; color: #dc2626; display: flex; align-items: center;">Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Table (25 per page server-side) -->
    <div class="table-container">
        <table class="alt-table">
            <thead>
                <tr>
                    <th>Enrollment Number</th>
                    <th>Candidate &amp; Father Name</th>
                    <th>Institution</th>
                    <th>District</th>
                    <th>Program</th>
                    <th>Trigger Invoice</th>
                    <th>Allotment Date</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $st)
                    @php
                        $rec = $st->currentAcademicRecord;
                        $firstInvoice = $st->invoiceStudents->first()?->invoice;
                    @endphp
                    <tr>
                        <td>
                            <span class="num-chip" onclick="copyToClipboard('{{ $st->enrollment_number }}', this)" title="Click to copy enrollment number">
                                <span>{{ $st->enrollment_number }}</span>
                                <svg class="copy-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </span>
                        </td>
                        <td>
                            <strong style="color: #0f172a;">{{ $st->full_name }}</strong>
                            <div style="font-size: 0.74rem; color: #64748b;">S/D/O {{ $st->father_name }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #334155;">{{ $st->school->name ?? '—' }}</div>
                            <div style="font-size: 0.72rem; color: #64748b; font-family: monospace;">{{ $st->school->username ?? '' }}</div>
                        </td>
                        <td>
                            {{ $st->school->district->name ?? '—' }}
                        </td>
                        <td>
                            <strong>{{ strtoupper($rec->class_level ?? 'SSC') }}</strong> — {{ ucfirst($rec->subject_group ?? 'General') }}
                        </td>
                        <td>
                            @if($firstInvoice)
                                <a href="{{ route('superadmin.invoice-verification', ['selected_id' => $firstInvoice->id]) }}" style="font-family: monospace; font-size: 0.78rem; font-weight: 700; color: #1e40af; text-decoration: underline;">
                                    {{ $firstInvoice->invoice_number }}
                                </a>
                            @else
                                <span style="font-size: 0.75rem; color: #94a3b8;">Manual / Legacy</span>
                            @endif
                        </td>
                        <td style="font-size: 0.78rem; color: #475569;">
                            {{ $st->enrollment_number_issued_at ? $st->enrollment_number_issued_at->format('d-M-Y H:i') : ($st->created_at ? $st->created_at->format('d-M-Y H:i') : '—') }}
                        </td>
                        <td>
                            @if(($st->allotment_type ?? 'auto') === 'manual')
                                <span class="sa-badge badge-manual">Manual</span>
                            @else
                                <span class="sa-badge badge-auto">Auto Allotted</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                            No issued enrollment numbers found matching query filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div style="font-size: 0.8rem; color: #64748b;">
            Showing {{ $records->firstItem() ?? 0 }} to {{ $records->lastItem() ?? 0 }} of {{ $records->total() }} candidates (25 per page)
        </div>
        <div>
            {{ $records->links() }}
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <!-- MANUAL ALLOTMENT SECTION (PART EIGHT SPECIFICATION)                   -->
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <div class="manual-box" id="manualAllotmentSection">
        <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.75rem;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #9a3412;">Edge Case Manual Enrollment Number Allotment</h3>
        </div>

        <div style="background: #fff7ed; border: 1px solid #ffedd5; border-radius: 8px; padding: 0.85rem 1rem; color: #9a3412; font-size: 0.82rem; margin-bottom: 1.25rem;">
            <strong>Audit Warning:</strong> Manual allotment bypasses the normal challan verification flow and should only be used for system-verified edge cases (court orders, special board dispensation). Every manual allotment is permanently logged with the officer ID and mandatory justification.
        </div>

        <!-- Student Search Form -->
        <div style="display: flex; gap: 0.75rem; max-width: 600px; margin-bottom: 1.25rem;">
            <input type="text" id="manualSearchInput" placeholder="Enter Candidate CNIC, B-Form, or Full Name..." class="sa-input" />
            <button type="button" onclick="searchCandidateForManual()" style="padding: 0.55rem 1.25rem; background: #ea580c; color: #ffffff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; white-space: nowrap;">
                Search Candidate
            </button>
        </div>

        <div id="manualCandidateResult" style="display: none; background: #ffffff; border: 1px solid #fed7aa; border-radius: 10px; padding: 1.25rem; margin-bottom: 1rem;">
            <!-- Populated via JS -->
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyToClipboard(text, el) {
        navigator.clipboard.writeText(text).then(() => {
            const originalHtml = el.innerHTML;
            el.innerHTML = `<span>${text}</span> <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>`;
            setTimeout(() => {
                el.innerHTML = originalHtml;
            }, 1800);
        });
    }

    async function searchCandidateForManual() {
        const query = document.getElementById('manualSearchInput').value.trim();
        const box = document.getElementById('manualCandidateResult');

        if (!query) return;

        box.style.display = 'block';
        box.innerHTML = '<div style="color: #64748b; font-size: 0.84rem;">Searching registry...</div>';

        try {
            const res = await fetch(`{{ route('superadmin.enrollment.search-student-manual') }}?query=${encodeURIComponent(query)}`);
            const data = await res.json();

            if (!data.found) {
                box.innerHTML = `<div style="color: #dc2626; font-size: 0.84rem;">${data.message}</div>`;
                return;
            }

            if (data.has_enrollment_number) {
                box.innerHTML = `
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 1rem; color: #1e40af; font-size: 0.85rem;">
                        <strong>Candidate Already Allotted:</strong> ${data.full_name} S/D/O ${data.father_name} already holds enrollment number: 
                        <strong style="font-family: monospace; font-size: 0.95rem;">${data.enrollment_number}</strong>. No manual allotment needed.
                    </div>
                `;
                return;
            }

            // Student found and has no enrollment number
            box.innerHTML = `
                <div style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h4 style="font-size: 1rem; font-weight: 800; color: #0f172a;">${data.full_name}</h4>
                            <div style="font-size: 0.78rem; color: #64748b;">Father Name: <strong>${data.father_name}</strong> | CNIC: <strong style="font-family: monospace;">${data.cnic}</strong></div>
                            <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">School: <strong>${data.school_name} (${data.school_code})</strong> | District: <strong>${data.district_name}</strong></div>
                            <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">Program: <strong>${data.class_level} — ${data.subject_group}</strong></div>
                        </div>
                        <span class="sa-badge badge-manual">Ready for Manual Allotment</span>
                    </div>
                </div>

                <div style="border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Mandatory Audit Justification (Minimum 30 characters) *
                    </label>
                    <textarea id="manualReasonInput" rows="3" class="sa-input" placeholder="Enter clear administrative rationale (e.g. Special permission granted by Chairman under Notification #1234)..." oninput="validateManualReason()"></textarea>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.4rem;">
                        <span style="font-size: 0.72rem; color: #64748b;">Count: <span id="manualCharCount">0</span> / 30 required</span>
                        <button type="button" id="btnConfirmManualAllot" onclick="submitManualAllotment(${data.id})" disabled style="padding: 0.55rem 1.25rem; background: #ea580c; color: #ffffff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; opacity: 0.5;">
                            Generate Enrollment Number
                        </button>
                    </div>
                </div>
            `;
        } catch (e) {
            box.innerHTML = '<div style="color: #dc2626;">Network error while searching candidate.</div>';
        }
    }

    function validateManualReason() {
        const val = document.getElementById('manualReasonInput')?.value.trim() || '';
        const len = val.length;
        document.getElementById('manualCharCount').innerText = len;
        const btn = document.getElementById('btnConfirmManualAllot');
        if (btn) {
            btn.disabled = (len < 30);
            btn.style.opacity = (len < 30) ? '0.5' : '1';
        }
    }

    async function submitManualAllotment(studentId) {
        const reason = document.getElementById('manualReasonInput').value.trim();
        if (reason.length < 30) return;

        if (!confirm('Are you sure you want to manually generate and issue an official enrollment number for this candidate? This action is permanently audited.')) {
            return;
        }

        const btn = document.getElementById('btnConfirmManualAllot');
        btn.disabled = true;
        btn.innerText = 'Generating Number...';

        try {
            const res = await fetch(`{{ route('superadmin.enrollment.manual-allot') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    student_id: studentId,
                    reason: reason,
                })
            });

            const data = await res.json();

            if (data.success) {
                alert(`SUCCESS: Manual Enrollment Number Allotted: ${data.enrollment_number}`);
                window.location.reload();
            } else {
                alert(data.message || 'Error occurred during manual allotment');
                btn.disabled = false;
                btn.innerText = 'Generate Enrollment Number';
            }
        } catch (e) {
            alert('Server error occurred during manual allotment.');
            btn.disabled = false;
            btn.innerText = 'Generate Enrollment Number';
        }
    }
</script>
@endpush
@endsection
