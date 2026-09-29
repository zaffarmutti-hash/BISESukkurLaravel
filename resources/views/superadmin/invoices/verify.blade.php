@extends('layouts.superadmin')

@section('content')
<style>
    /* Two-Panel Layout */
    .sa-workspace {
        display: grid;
        grid-template-columns: 420px 1fr;
        gap: 1.5rem;
        height: calc(100vh - 140px);
        min-height: 600px;
    }
    @media (max-width: 1024px) {
        .sa-workspace {
            grid-template-columns: 1fr;
            height: auto;
        }
    }

    /* Left Panel */
    .left-panel {
        background: #ffffff;
        border-radius: var(--radius-lg, 14px);
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }
    .panel-filter-box {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }
    .filter-input-sm, .filter-select-sm {
        width: 100%;
        padding: 0.45rem 0.75rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.8rem;
        background: #ffffff;
        outline: none;
    }
    .filter-input-sm:focus, .filter-select-sm:focus {
        border-color: #1e40af;
    }
    .invoices-scroll-list {
        flex: 1;
        overflow-y: auto;
        padding: 0.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }
    .inv-card {
        padding: 0.85rem 1rem;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        text-decoration: none;
        color: inherit;
    }
    .inv-card:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .inv-card.active {
        background: #eff6ff;
        border-color: #93c5fd;
        border-left: 4px solid #1e40af;
    }

    /* Right Panel */
    .right-panel {
        background: #ffffff;
        border-radius: var(--radius-lg, 14px);
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        padding: 1.5rem;
    }

    /* Chips */
    .sa-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.55rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 700;
    }
    .sa-chip-navy { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .sa-chip-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .sa-chip-green { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .sa-chip-red { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

    /* Modals */
    .sa-modal-backdrop {
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
    .sa-modal-backdrop.active { display: flex; }
    .sa-modal {
        background: #ffffff;
        border-radius: 16px;
        max-width: 550px;
        width: 100%;
        padding: 1.75rem;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
    }
</style>

<div class="sa-workspace">
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <!-- LEFT PANEL: INVOICE LIST (PART SEVEN)                                 -->
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <div class="left-panel">
        <!-- Search & Filter Form -->
        <div class="panel-filter-box">
            <form method="GET" action="{{ route('superadmin.invoice-verification') }}" id="leftFilterForm">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Invoice or School..." class="filter-input-sm" style="margin-bottom: 0.5rem;" onchange="document.getElementById('leftFilterForm').submit()" />
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <!-- District -->
                    <select name="district_id" class="filter-select-sm" onchange="document.getElementById('leftFilterForm').submit()">
                        <option value="">All Districts</option>
                        @foreach($districts as $d)
                            <option value="{{ $d->id }}" {{ request('district_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>

                    <!-- Type -->
                    <select name="type" class="filter-select-sm" onchange="document.getElementById('leftFilterForm').submit()">
                        <option value="">All Types</option>
                        <option value="enrollment" {{ request('type') === 'enrollment' ? 'selected' : '' }}>Enrollment</option>
                        <option value="examination" {{ request('type') === 'examination' ? 'selected' : '' }}>Examination</option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <select name="status" class="filter-select-sm" onchange="document.getElementById('leftFilterForm').submit()">
                        <option value="pending" {{ request('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending / Submitted</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    @if(request()->anyFilled(['search', 'district_id', 'type']))
                        <a href="{{ route('superadmin.invoice-verification') }}" class="filter-select-sm" style="width: auto; text-decoration: none; color: #ef4444; display: flex; align-items: center;">Reset</a>
                    @endif
                </div>
            </form>

            <!-- Bulk Action Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.25rem;">
                <label style="font-size: 0.75rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                    <input type="checkbox" id="chkSelectAllInvoices" onchange="toggleSelectAllInvoices(this.checked)" style="accent-color: #1e40af;" />
                    <span>Select All</span>
                </label>
                <button type="button" id="btnVerifySelected" onclick="openBulkVerifyModal()" disabled style="padding: 3px 10px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; background: #10b981; color: #ffffff; border: none; cursor: pointer; opacity: 0.5;">
                    Verify Selected (<span id="bulkSelectedCount">0</span>)
                </button>
            </div>
        </div>

        <!-- Invoices List -->
        <div class="invoices-scroll-list">
            @forelse($invoices as $inv)
                @php
                    $isSelected = ($selectedInvoice && $selectedInvoice->id === $inv->id);
                    $daysOld = $inv->created_at ? (int) $inv->created_at->diffInDays(now()) : 0;
                    $isOver7 = ($daysOld > 7 && !in_array($inv->status, ['confirmed', 'verified']));
                @endphp
                <div class="inv-card {{ $isSelected ? 'active' : '' }}" onclick="selectInvoiceItem({{ $inv->id }})">
                    <div style="padding-top: 2px;" onclick="event.stopPropagation()">
                        <input type="checkbox" class="bulk-inv-chk" value="{{ $inv->id }}" onchange="onInvoiceCheckboxChanged()" style="accent-color: #1e40af; cursor: pointer;" />
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 2px;">
                            <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $inv->school->name }}
                            </div>
                            <span class="sa-chip {{ $inv->invoice_type === 'enrollment' ? 'sa-chip-navy' : 'sa-chip-amber' }}">
                                {{ ucfirst($inv->invoice_type) }}
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 4px;">
                            <span style="font-family: monospace; font-size: 0.78rem; font-weight: 700; color: #1e40af;">
                                {{ $inv->invoice_number }}
                            </span>
                            <strong style="font-size: 0.84rem; font-family: monospace; color: #059669;">
                                Rs {{ number_format($inv->total_amount_paisas / 100, 2) }}
                            </strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.72rem; color: #64748b; margin-top: 4px;">
                            <span>{{ $inv->student_count }} candidates</span>
                            <span style="{{ $isOver7 ? 'color: #dc2626; font-weight: 800;' : '' }}">
                                {{ $daysOld }}d ago
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 2.5rem 1rem; color: #64748b; font-size: 0.82rem;">
                    No challans found for this filter criteria.
                </div>
            @endforelse
        </div>

        <!-- Left Pagination -->
        <div style="padding: 0.5rem 1rem; border-top: 1px solid #e2e8f0; font-size: 0.75rem; color: #64748b;">
            {{ $invoices->links() }}
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <!-- RIGHT PANEL: INVOICE DETAIL & VERIFICATION (PART SEVEN)               -->
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <div class="right-panel" id="rightPanel">
        @if($selectedInvoice)
            <!-- Detail Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        <span style="font-family: monospace; font-size: 1.25rem; font-weight: 900; color: #1e3a8a;">
                            {{ $selectedInvoice->invoice_number }}
                        </span>
                        @if(($selectedInvoice->fee_phase ?? 'normal') === 'grace')
                            <span class="sa-chip sa-chip-amber">Late Fee (Grace Phase)</span>
                        @else
                            <span class="sa-chip sa-chip-navy">Standard Phase</span>
                        @endif
                        @if(in_array($selectedInvoice->status, ['confirmed', 'verified']))
                            <span class="sa-chip sa-chip-green">Verified</span>
                        @elseif($selectedInvoice->status === 'rejected')
                            <span class="sa-chip sa-chip-red">Rejected</span>
                        @else
                            <span class="sa-chip sa-chip-amber">Pending Verification</span>
                        @endif
                    </div>
                    <h2 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-top: 4px;">
                        {{ $selectedInvoice->school->name }} (Code: {{ $selectedInvoice->school->username }})
                    </h2>
                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                        District: <strong>{{ $selectedInvoice->school->district->name ?? 'Sukkur' }}</strong> | Session: <strong>{{ $selectedInvoice->academicYear->label ?? '2026' }}</strong>
                    </div>
                </div>

                <!-- Action Buttons Row -->
                <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;">
                    @if(!in_array($selectedInvoice->status, ['confirmed', 'verified']))
                        <button type="button" onclick="openVerifyModal({{ $selectedInvoice->id }}, '{{ $selectedInvoice->invoice_type }}', {{ $selectedInvoice->student_count }})" style="padding: 0.6rem 1.25rem; background: #059669; color: #ffffff; font-weight: 700; font-size: 0.84rem; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.4rem; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Verify &amp; Allot Numbers</span>
                        </button>
                        <button type="button" onclick="openRejectModal({{ $selectedInvoice->id }})" style="padding: 0.6rem 1rem; background: #ffffff; color: #dc2626; border: 1.5px solid #fecaca; font-weight: 700; font-size: 0.84rem; border-radius: 8px; cursor: pointer;">
                            Reject
                        </button>
                    @endif
                    <a href="{{ route('superadmin.invoices.download-challan', $selectedInvoice->id) }}" target="_blank" style="padding: 0.6rem 0.9rem; background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-weight: 700; font-size: 0.82rem; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 0.35rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>Challan PDF</span>
                    </a>
                    <a href="{{ route('superadmin.invoices.download-list', $selectedInvoice->id) }}" target="_blank" style="padding: 0.6rem 0.9rem; background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; font-weight: 700; font-size: 0.82rem; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 0.35rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span>Manifest PDF</span>
                    </a>
                </div>
            </div>

            <!-- If rejected alert -->
            @if($selectedInvoice->status === 'rejected' && $selectedInvoice->rejection_reason)
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 0.75rem 1rem; color: #991b1b; font-size: 0.82rem; margin-bottom: 1.25rem;">
                    <strong>Rejection Reason:</strong> {{ $selectedInvoice->rejection_reason }}
                </div>
            @endif

            <!-- Info Grid Row -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.85rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.8rem;">
                <div><span style="color: #64748b;">Class:</span> <strong style="color: #0f172a;">{{ strtoupper($selectedInvoice->class_level ?? 'SSC') }}</strong></div>
                <div><span style="color: #64748b;">Group:</span> <strong style="color: #0f172a;">{{ ucfirst($selectedInvoice->subject_group ?? 'General') }}</strong></div>
                <div><span style="color: #64748b;">Candidate Type:</span> <strong style="color: #0f172a;">{{ ucfirst($selectedInvoice->student_type ?? 'Regular') }}</strong></div>
                <div><span style="color: #64748b;">Candidates:</span> <strong style="color: #059669;">{{ $selectedInvoice->student_count }} Included</strong></div>
                <div><span style="color: #64748b;">Total Amount:</span> <strong style="color: #1e3a8a; font-family: monospace; font-size: 0.95rem;">PKR {{ number_format($selectedInvoice->total_amount_paisas / 100, 2) }}</strong></div>
                <div><span style="color: #64748b;">Generated Date:</span> <strong>{{ $selectedInvoice->created_at?->format('d-M-Y H:i') }}</strong></div>
                @if($selectedInvoice->approved_at)
                    <div><span style="color: #64748b;">Verified Date:</span> <strong style="color: #059669;">{{ $selectedInvoice->approved_at->format('d-M-Y H:i') }}</strong></div>
                    <div><span style="color: #64748b;">Verified By:</span> <strong>{{ $selectedInvoice->approvedBy->name ?? 'Admin' }}</strong></div>
                @endif
            </div>

            <!-- Table of Included Candidates -->
            <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 0.6rem;">
                Enrolled Candidates on this Challan ({{ $selectedInvoice->invoiceStudents->count() }})
            </h3>
            <div style="border: 1px solid #e2e8f0; border-radius: 10px; overflow-x: auto; flex: 1;">
                <table class="chl-table" style="font-size: 0.8rem; width: 100%;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 0.65rem 0.85rem;">#</th>
                            <th style="padding: 0.65rem 0.85rem;">Candidate Name</th>
                            <th style="padding: 0.65rem 0.85rem;">CNIC / B-Form</th>
                            <th style="padding: 0.65rem 0.85rem;">Enrollment No.</th>
                            <th style="padding: 0.65rem 0.85rem; text-align: center;">Challan Status</th>
                            <th style="padding: 0.65rem 0.85rem; text-align: right;">Fee (Rs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($selectedInvoice->invoiceStudents as $idx => $item)
                            <tr style="{{ $item->is_included ? '' : 'background: #fef2f2; color: #94a3b8;' }}">
                                <td style="padding: 0.65rem 0.85rem;">{{ $idx + 1 }}</td>
                                <td style="padding: 0.65rem 0.85rem;">
                                    <strong style="color: #0f172a;">{{ $item->student->full_name }}</strong>
                                    <div style="font-size: 0.72rem; color: #64748b;">S/D/O {{ $item->student->father_name }}</div>
                                </td>
                                <td style="padding: 0.65rem 0.85rem; font-family: monospace;">
                                    {{ $item->student->cnic ?? $item->student->b_form ?? '—' }}
                                </td>
                                <td style="padding: 0.65rem 0.85rem; font-family: monospace; font-weight: 700; color: #1e3a8a;">
                                    {{ $item->student->enrollment_number ?? 'PENDING ALLOTMENT' }}
                                </td>
                                <td style="padding: 0.65rem 0.85rem; text-align: center;">
                                    @if($item->is_included)
                                        <span class="sa-chip sa-chip-green">Included</span>
                                    @else
                                        <span class="sa-chip sa-chip-red">Excluded</span>
                                    @endif
                                </td>
                                <td style="padding: 0.65rem 0.85rem; text-align: right; font-family: monospace;">
                                    Rs {{ number_format($item->amount_paisas / 100, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @else
            <!-- Placeholder when nothing selected -->
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #64748b; text-align: center;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 0.75rem;"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #334155;">Select an invoice from the left panel</h3>
                <p style="font-size: 0.82rem; max-width: 320px; margin-top: 4px;">
                    Review bank deposit details, student manifests, and approve payments to trigger enrollment number allotments.
                </p>
            </div>
        @endif
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL: VERIFY CONFIRMATION                                                -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="sa-modal-backdrop" id="verifyConfirmModal">
    <div class="sa-modal">
        <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">
            Confirm Invoice Payment &amp; Verification
        </h3>
        <p style="font-size: 0.85rem; color: #475569; margin-bottom: 1.25rem;" id="verifyModalDesc">
            Verifying this invoice will permanently mark it as paid.
        </p>

        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 1rem; color: #065f46; font-size: 0.84rem; margin-bottom: 1.5rem;" id="verifyAllotmentNotice">
            <strong>Automatic Trigger:</strong> All included candidates without an enrollment number will immediately receive an official BISE Sukkur enrollment number formatted according to board protocol.
        </div>

        <form id="verifyForm" method="POST" action="">
            @csrf
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeVerifyModal()" style="padding: 0.6rem 1.1rem; border: 1px solid #cbd5e1; background: #ffffff; border-radius: 8px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="submit" id="btnConfirmVerify" style="padding: 0.6rem 1.25rem; background: #059669; color: #ffffff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
                    Confirm &amp; Issue Numbers
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL: REJECT INVOICE                                                     -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="sa-modal-backdrop" id="rejectModal">
    <div class="sa-modal">
        <h3 style="font-size: 1.2rem; font-weight: 800; color: #991b1b; margin-bottom: 0.5rem;">
            Reject Fee Challan
        </h3>
        <p style="font-size: 0.82rem; color: #475569; margin-bottom: 1rem;">
            Please provide a specific reason for rejection. This reason will be permanently displayed to the school administration.
        </p>

        <form id="rejectForm" method="POST" action="">
            @csrf
            <div style="margin-bottom: 1rem;">
                <textarea id="rejectionReasonText" name="rejection_reason" rows="4" placeholder="Enter rejection reason (minimum 20 characters required)..." class="filter-input-sm" style="width: 100%; font-size: 0.85rem;" oninput="validateRejectionInput()"></textarea>
                <div style="font-size: 0.72rem; color: #64748b; margin-top: 3px;">
                    Character count: <span id="charCount">0</span> / 20 required
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeRejectModal()" style="padding: 0.6rem 1.1rem; border: 1px solid #cbd5e1; background: #ffffff; border-radius: 8px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="submit" id="btnConfirmReject" disabled style="padding: 0.6rem 1.25rem; background: #dc2626; color: #ffffff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; opacity: 0.5;">
                    Reject Challan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL: BULK VERIFY CONFIRMATION                                           -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="sa-modal-backdrop" id="bulkVerifyModal">
    <div class="sa-modal">
        <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">
            Bulk Verify Selected Challans
        </h3>
        <p style="font-size: 0.85rem; color: #475569; margin-bottom: 1rem;">
            You have selected <strong id="bulkModalCount">0</strong> invoices to verify in batch.
        </p>
        <div id="bulkStatusText" style="font-size: 0.82rem; color: #1e40af; font-weight: 700; margin-bottom: 1rem; display: none;">
            Processing invoices sequentially...
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" onclick="closeBulkVerifyModal()" style="padding: 0.6rem 1.1rem; border: 1px solid #cbd5e1; background: #ffffff; border-radius: 8px; font-weight: 600; cursor: pointer;">Cancel</button>
            <button type="button" id="btnExecuteBulkVerify" onclick="executeBulkVerify()" style="padding: 0.6rem 1.25rem; background: #059669; color: #ffffff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
                Start Bulk Verification
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function selectInvoiceItem(id) {
        const url = new URL(window.location.href);
        url.searchParams.set('selected_id', id);
        window.location.href = url.toString();
    }

    function toggleSelectAllInvoices(isChecked) {
        document.querySelectorAll('.bulk-inv-chk').forEach(c => c.checked = isChecked);
        onInvoiceCheckboxChanged();
    }

    function onInvoiceCheckboxChanged() {
        const checked = Array.from(document.querySelectorAll('.bulk-inv-chk:checked')).map(c => c.value);
        const count = checked.length;
        document.getElementById('bulkSelectedCount').innerText = count;
        const btn = document.getElementById('btnVerifySelected');
        btn.disabled = (count === 0);
        btn.style.opacity = (count === 0) ? '0.5' : '1';
    }

    function openVerifyModal(invoiceId, type, count) {
        document.getElementById('verifyConfirmModal').classList.add('active');
        document.getElementById('verifyForm').action = `/superadmin/invoice-verification/${invoiceId}/confirm`;

        if (type === 'enrollment') {
            document.getElementById('verifyModalDesc').innerText = 
                `Verifying this enrollment challan will issue official enrollment numbers to ${count} candidate(s).`;
            document.getElementById('verifyAllotmentNotice').style.display = 'block';
        } else {
            document.getElementById('verifyModalDesc').innerText = 
                `Verifying this examination challan will confirm payment and allow roll number generation. No enrollment numbers will be issued.`;
            document.getElementById('verifyAllotmentNotice').style.display = 'none';
        }
    }

    function closeVerifyModal() {
        document.getElementById('verifyConfirmModal').classList.remove('active');
    }

    function openRejectModal(invoiceId) {
        document.getElementById('rejectModal').classList.add('active');
        document.getElementById('rejectForm').action = `/superadmin/invoice-verification/${invoiceId}/reject`;
        document.getElementById('rejectionReasonText').value = '';
        validateRejectionInput();
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.remove('active');
    }

    function validateRejectionInput() {
        const val = document.getElementById('rejectionReasonText').value.trim();
        const len = val.length;
        document.getElementById('charCount').innerText = len;
        const btn = document.getElementById('btnConfirmReject');
        btn.disabled = (len < 20);
        btn.style.opacity = (len < 20) ? '0.5' : '1';
    }

    function openBulkVerifyModal() {
        const checked = Array.from(document.querySelectorAll('.bulk-inv-chk:checked')).map(c => c.value);
        document.getElementById('bulkModalCount').innerText = checked.length;
        document.getElementById('bulkVerifyModal').classList.add('active');
    }

    function closeBulkVerifyModal() {
        document.getElementById('bulkVerifyModal').classList.remove('active');
    }

    async function executeBulkVerify() {
        const checked = Array.from(document.querySelectorAll('.bulk-inv-chk:checked')).map(c => parseInt(c.value));
        const btn = document.getElementById('btnExecuteBulkVerify');
        btn.disabled = true;
        document.getElementById('bulkStatusText').style.display = 'block';

        try {
            const res = await fetch(`{{ route('superadmin.invoices.bulk-verify') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ invoice_ids: checked })
            });

            const data = await res.json();
            if (data.success) {
                alert(`Bulk Verification Complete:\nVerified: ${data.verified_count}\nEnrollment Numbers Allotted: ${data.allotted_total}\nFailed: ${data.failed_count}`);
                window.location.reload();
            } else {
                alert('Error in bulk verification');
                btn.disabled = false;
            }
        } catch (e) {
            alert('Network error in bulk verification');
            btn.disabled = false;
        }
    }
</script>
@endpush
@endsection
