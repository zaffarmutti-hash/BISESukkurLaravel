@extends('layouts.superadmin')

@section('title', 'Enrollment Report')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Enrollment Report</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Candidates registered across SSC & HSC streams in the active session.</p>
        </div>
        <div class="d-flex gap-2">
            <span class="sa-badge sa-badge-gold">SESSION: {{ $activeYear->label ?? 'Current' }}</span>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="sa-paper p-4">
        <form method="GET" action="{{ route('superadmin.reports.enrollment') }}" class="d-flex gap-3 flex-wrap align-end">
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
            <div style="flex: 1; min-width: 120px;">
                <label class="sa-form-label">Class</label>
                <select name="class_level" class="sa-input">
                    <option value="">All Classes</option>
                    <option value="9" {{ request('class_level') == '9' ? 'selected' : '' }}>SSC-I (Class 9)</option>
                    <option value="10" {{ request('class_level') == '10' ? 'selected' : '' }}>SSC-II (Class 10)</option>
                    <option value="11" {{ request('class_level') == '11' ? 'selected' : '' }}>HSC-I (Class 11)</option>
                    <option value="12" {{ request('class_level') == '12' ? 'selected' : '' }}>HSC-II (Class 12)</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 160px;">
                <label class="sa-form-label">Search</label>
                <input type="text" name="search" class="sa-input" placeholder="Name or Enrollment #" value="{{ request('search') }}">
            </div>
            <div>
                <button type="submit" class="sa-btn sa-btn-gold">Filter Records</button>
            </div>
        </form>
    </div>

    <!-- Records Table -->
    <div class="sa-paper p-5">
        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>Enrollment #</th>
                        <th>Student Name & Father</th>
                        <th>School</th>
                        <th>District</th>
                        <th>Class & Group</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $rec)
                    <tr>
                        <td class="font-mono font-bold text-slate-900">
                            {{ $rec->student?->enrollment_number ?? 'Pending Allotment' }}
                        </td>
                        <td>
                            <div class="font-bold text-slate-800">{{ $rec->student?->full_name ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-500">S/D/O {{ $rec->student?->father_name ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <div class="text-sm font-semibold text-slate-700">{{ $rec->student?->school?->name ?? 'N/A' }}</div>
                            <span class="text-xs font-mono text-slate-400">{{ $rec->student?->school?->username ?? '' }}</span>
                        </td>
                        <td>{{ $rec->student?->school?->district?->name ?? 'N/A' }}</td>
                        <td>
                            <span class="sa-badge sa-badge-info">{{ $rec->class_level ?? $rec->student?->class_level ?? 'SSC-I' }}</span>
                            <span class="text-xs text-slate-500">{{ $rec->group_name ?? $rec->student?->group ?? 'General' }}</span>
                        </td>
                        <td class="text-center">
                            @if($rec->student?->enrollment_number)
                                <span class="sa-badge sa-badge-active">Enrolled</span>
                            @else
                                <span class="sa-badge sa-badge-gold">Verified Paid</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-6 text-slate-400">No enrollment records matching query.</td>
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
