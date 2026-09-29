@extends('layouts.superadmin')

@section('title', 'Fee Collection Report')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Fee Collection Report</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Comprehensive revenue reconciliation across all 5 districts and affiliated institutions.</p>
        </div>
        <div class="d-flex gap-2">
            <span class="sa-badge sa-badge-gold">SESSION: {{ $activeYear->label ?? 'Current' }}</span>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="sa-paper p-4">
        <form method="GET" action="{{ route('superadmin.reports.fee-collection') }}" class="d-flex gap-3 flex-wrap align-end">
            <div style="flex: 1; min-width: 180px;">
                <label class="sa-form-label">District</label>
                <select name="district_id" class="sa-input">
                    <option value="">All Districts</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}" {{ request('district_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 180px;">
                <label class="sa-form-label">School</label>
                <select name="school_id" class="sa-input">
                    <option value="">All Schools</option>
                    @foreach($schools as $s)
                        <option value="{{ $s->id }}" {{ request('school_id') == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->username }})</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 140px;">
                <label class="sa-form-label">Date From</label>
                <input type="date" name="date_from" class="sa-input" value="{{ request('date_from') }}">
            </div>
            <div style="flex: 1; min-width: 140px;">
                <label class="sa-form-label">Date To</label>
                <input type="date" name="date_to" class="sa-input" value="{{ request('date_to') }}">
            </div>
            <div>
                <button type="submit" class="sa-btn sa-btn-gold">Filter Records</button>
            </div>
            @if(request()->anyFilled(['district_id', 'school_id', 'date_from', 'date_to']))
            <div>
                <a href="{{ route('superadmin.reports.fee-collection') }}" class="sa-btn sa-btn-outline">Reset</a>
            </div>
            @endif
        </form>
    </div>

    <!-- Records Table -->
    <div class="sa-paper p-5">
        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>Challan / Invoice #</th>
                        <th>School Name</th>
                        <th>District</th>
                        <th>Type</th>
                        <th class="text-right">Students</th>
                        <th class="text-right">Amount (PKR)</th>
                        <th>Verified Date</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $rec)
                    <tr>
                        <td class="font-mono font-bold text-slate-900">{{ $rec->challan_no ?? $rec->invoice_number }}</td>
                        <td>
                            <div class="font-bold text-slate-800">{{ $rec->school?->name ?? 'N/A' }}</div>
                            <span class="text-xs text-slate-400 font-mono">{{ $rec->school?->username ?? '' }}</span>
                        </td>
                        <td>{{ $rec->school?->district?->name ?? 'N/A' }}</td>
                        <td><span class="sa-badge sa-badge-info">{{ ucfirst($rec->challan_type ?? $rec->invoice_type) }}</span></td>
                        <td class="text-right font-medium">{{ number_format($rec->total_students ?? $rec->student_count ?? 0) }}</td>
                        <td class="text-right font-bold text-emerald-700">Rs. {{ number_format(($rec->total_amount_paisas ?? 0) / 100) }}</td>
                        <td class="text-xs text-slate-600">{{ $rec->payment_date ? \Carbon\Carbon::parse($rec->payment_date)->format('M d, Y') : ($rec->updated_at ? $rec->updated_at->format('M d, Y') : '-') }}</td>
                        <td class="text-center"><span class="sa-badge sa-badge-active">Verified</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-6 text-slate-400">No verified payment records found for the selected criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection
