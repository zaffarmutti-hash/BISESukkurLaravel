@extends('layouts.superadmin')

@php
    $title             = 'Pending Approvals';
    $breadcrumbSection = 'System';
    $breadcrumbCurrent = 'Pending Approvals';
@endphp

@section('content')
<div class="sa-animate-fade-up">

    {{-- Page Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
        <div>
            <h1 style="font-size:24px;font-weight:800;color:#0f172a;margin:0 0 4px 0;display:flex;align-items:center;gap:10px;">
                <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                </div>
                Pending Approvals
                @if($totalPending > 0)
                    <span style="background:#ef4444;color:#fff;font-size:13px;font-weight:800;padding:3px 10px;border-radius:9999px;">{{ $totalPending }}</span>
                @endif
            </h1>
            <p style="font-size:13px;color:#64748b;margin:0;">Review and approve submitted invoices, data entries, and other items awaiting controller action.</p>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div style="display:flex;gap:4px;background:#f1f5f9;border-radius:12px;padding:4px;margin-bottom:24px;width:fit-content;">
        @php
        $tabs = [
            ['id' => 'verifications', 'label' => 'Invoice Verifications', 'count' => $totalPending],
        ];
        @endphp
        @foreach($tabs as $tab)
        <a href="{{ route('superadmin.approvals') }}?tab={{ $tab['id'] }}"
            style="padding:8px 18px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;transition:all .2s ease;display:flex;align-items:center;gap:8px;{{ $activeTab === $tab['id'] ? 'background:#fff;color:#0f172a;box-shadow:0 2px 8px rgba(0,0,0,.08);' : 'color:#64748b;' }}">
            {{ $tab['label'] }}
            @if(isset($tab['count']) && $tab['count'] > 0)
                <span style="background:{{ $activeTab === $tab['id'] ? '#ef4444' : '#94a3b8' }};color:#fff;font-size:10px;font-weight:800;padding:1px 6px;border-radius:9999px;">{{ $tab['count'] }}</span>
            @endif
        </a>
        @endforeach
    </div>

    {{-- Tab: Invoice Verifications --}}
    @if($activeTab === 'verifications')
    <div class="sa-panel">
        <div class="sa-panel-header">
            <h3 class="sa-panel-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Submitted Invoices Awaiting Verification
            </h3>
            @if($totalPending > 0)
                <span style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:9999px;font-size:11px;font-weight:700;padding:2px 10px;">{{ $totalPending }} pending</span>
            @endif
        </div>

        @if($pendingVerifications->isEmpty())
            <div style="text-align:center;padding:60px 20px;">
                <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#ecfdf5,#d1fae5);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <h3 style="font-size:18px;font-weight:700;color:#0f172a;margin:0 0 8px 0;">All Caught Up!</h3>
                <p style="font-size:14px;color:#64748b;margin:0;">No invoices are currently awaiting verification.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="sa-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>School</th>
                            <th>District</th>
                            <th>Type</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Students</th>
                            <th>Submitted</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingVerifications as $inv)
                        @php
                            $daysPending = $inv->submitted_at ? now()->diffInDays($inv->submitted_at) : 0;
                        @endphp
                        <tr>
                            <td>
                                <code style="font-size:12px;background:#f1f5f9;padding:2px 6px;border-radius:4px;font-family:monospace;font-weight:700;">
                                    {{ $inv->invoice_number }}
                                </code>
                            </td>
                            <td>
                                <div style="font-weight:700;color:#0f172a;font-size:13px;">{{ $inv->school?->name ?? '—' }}</div>
                                <div style="font-size:11px;font-family:monospace;color:#64748b;">{{ $inv->school?->username ?? '' }}</div>
                            </td>
                            <td style="font-size:13px;color:#475569;">{{ $inv->school?->district?->name ?? '—' }}</td>
                            <td>
                                <span style="background:{{ $inv->invoice_type === 'enrollment' ? '#eff6ff' : '#faf5ff' }};color:{{ $inv->invoice_type === 'enrollment' ? '#1e40af' : '#6b21a8' }};border-radius:6px;font-size:11px;font-weight:700;padding:2px 8px;text-transform:capitalize;">
                                    {{ $inv->invoice_type ?? 'enrollment' }}
                                </span>
                            </td>
                            <td class="text-right" style="font-weight:700;font-size:13px;">Rs {{ number_format(($inv->total_amount_paisas ?? 0) / 100) }}</td>
                            <td class="text-right" style="font-size:13px;">{{ number_format($inv->student_count ?? 0) }}</td>
                            <td>
                                <div style="font-size:12px;color:{{ $daysPending > 7 ? '#ef4444' : '#64748b' }};font-weight:{{ $daysPending > 7 ? '700' : '400' }};">
                                    {{ $daysPending }}d ago
                                </div>
                                <div style="font-size:11px;color:#94a3b8;">
                                    {{ $inv->submitted_at?->format('d-M-Y') ?? '—' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <div style="display:flex;align-items:center;justify-content:center;gap:6px;">
                                    <a href="{{ route('superadmin.invoices.verify') }}?invoice={{ $inv->invoice_number }}"
                                        style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:7px;padding:5px 12px;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap;">
                                        Verify
                                    </a>
                                    <a href="{{ route('superadmin.invoices.verify') }}?invoice={{ $inv->invoice_number }}"
                                        style="background:#f8fafc;color:#475569;border:1px solid #e2e8f0;border-radius:7px;padding:5px 12px;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap;">
                                        Review
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:20px;">
                {{ $pendingVerifications->links() }}
            </div>
        @endif
    </div>
    @endif

    {{-- Coming Soon Tabs (Data Entry, Marks, Reprints, Flagged) --}}
    <div style="margin-top:24px;display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;">
        @php
        $comingSoon = [
            ['title' => 'Data Entry Review', 'desc' => 'Approve data entry records submitted by operators.', 'icon' => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>', 'color' => '#3b82f6'],
            ['title' => 'Mark Entry Approval', 'desc' => 'Finalize and confirm result entries for exam papers.', 'icon' => '<polyline points="22 4 12 14.01 9 11.01"/><circle cx="12" cy="12" r="10"/>', 'color' => '#8b5cf6'],
            ['title' => 'Reprint Requests', 'desc' => 'Approve or deny requests to reprint documents.', 'icon' => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>', 'color' => '#f59e0b'],
            ['title' => 'Flagged Records', 'desc' => 'Review records flagged by Check and Balance Officer.', 'icon' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>', 'color' => '#ef4444'],
        ];
        @endphp
        @foreach($comingSoon as $cs)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;opacity:.7;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                <div style="width:36px;height:36px;border-radius:10px;background:{{ $cs['color'] }}22;display:flex;align-items:center;justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $cs['color'] }}" stroke-width="2">{!! $cs['icon'] !!}</svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:700;color:#0f172a;">{{ $cs['title'] }}</div>
                    <span style="font-size:10px;font-weight:700;background:#f1f5f9;color:#94a3b8;padding:1px 8px;border-radius:9999px;">COMING SOON</span>
                </div>
            </div>
            <p style="font-size:12px;color:#94a3b8;margin:0;">{{ $cs['desc'] }}</p>
        </div>
        @endforeach
    </div>

</div>

@push('styles')
<style>
    .text-right { text-align: right; }
    .text-center { text-align: center; }
</style>
@endpush
@endsection
