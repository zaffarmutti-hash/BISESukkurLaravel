@extends('layouts.superadmin')

@section('title', 'Payment Transactions Ledger')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Payment Transactions Ledger</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Complete board transaction history, challan issuances, and payment verification records.</p>
        </div>
        <div>
            <span class="sa-badge sa-badge-gold">SESSION: {{ $activeYear->label ?? 'Current' }}</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="sa-paper p-4">
        <form method="GET" action="{{ route('superadmin.invoices.transactions') }}" class="d-flex gap-3 flex-wrap align-end">
            <div style="flex: 1; min-width: 180px;">
                <label class="sa-form-label">District</label>
                <select name="district_id" class="sa-input">
                    <option value="">All Districts</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}" {{ request('district_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 140px;">
                <label class="sa-form-label">Type</label>
                <select name="type" class="sa-input">
                    <option value="">All Types</option>
                    <option value="enrollment" {{ request('type') == 'enrollment' ? 'selected' : '' }}>Enrollment</option>
                    <option value="examination" {{ request('type') == 'examination' ? 'selected' : '' }}>Examination</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 140px;">
                <label class="sa-form-label">Status</label>
                <select name="status" class="sa-input">
                    <option value="">All Statuses</option>
                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted (Pending)</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 180px;">
                <label class="sa-form-label">Search</label>
                <input type="text" name="search" class="sa-input" placeholder="Challan # or School..." value="{{ request('search') }}">
            </div>
            <div>
                <button type="submit" class="sa-btn sa-btn-gold">Filter</button>
            </div>
            @if(request()->anyFilled(['district_id', 'type', 'status', 'search']))
            <div>
                <a href="{{ route('superadmin.invoices.transactions') }}" class="sa-btn sa-btn-outline">Reset</a>
            </div>
            @endif
        </form>
    </div>

    <!-- Ledger Table -->
    <div class="sa-paper p-5">
        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>Challan / Ref #</th>
                        <th>Institution</th>
                        <th>District</th>
                        <th>Type</th>
                        <th class="text-right">Students</th>
                        <th class="text-right">Amount (PKR)</th>
                        <th>Payment / Submit Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td class="font-mono font-bold text-slate-900">{{ $inv->invoice_number ?? $inv->challan_no }}</td>
                        <td>
                            <div class="font-bold text-slate-800">{{ $inv->school?->name ?? 'N/A' }}</div>
                            <span class="text-xs text-slate-400 font-mono">{{ $inv->school?->username ?? '' }}</span>
                        </td>
                        <td>{{ $inv->school?->district?->name ?? 'N/A' }}</td>
                        <td>
                            <span class="sa-badge sa-badge-info">{{ ucfirst($inv->invoice_type ?? $inv->challan_type) }}</span>
                        </td>
                        <td class="text-right font-medium">{{ number_format($inv->total_students ?? $inv->student_count ?? 0) }}</td>
                        <td class="text-right font-bold text-emerald-700">Rs. {{ number_format(($inv->total_amount_paisas ?? 0) / 100) }}</td>
                        <td class="text-xs text-slate-600 font-mono">
                            {{ $inv->created_at ? $inv->created_at->format('M d, Y') : '-' }}
                        </td>
                        <td class="text-center">
                            @if(in_array($inv->status, ['verified', 'confirmed']))
                                <span class="sa-badge sa-badge-active">Verified</span>
                            @elseif(in_array($inv->status, ['submitted', 'pending', 'paid']))
                                <span class="sa-badge sa-badge-gold">Submitted</span>
                            @elseif($inv->status === 'rejected')
                                <span class="sa-badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">Rejected</span>
                            @else
                                <span class="sa-badge sa-badge-inactive">{{ ucfirst($inv->status) }}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('superadmin.invoices.verify', ['selected_id' => $inv->id]) }}" class="sa-btn sa-btn-outline" style="font-size: 11px; padding: 4px 8px;">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-6 text-slate-400">No payment transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection
