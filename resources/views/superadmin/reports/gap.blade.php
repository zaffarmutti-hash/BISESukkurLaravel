@extends('layouts.superadmin')

@section('title', 'Examination Registration Gap Report')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Enrollment to Examination Gap Report</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Candidates possessing an official enrollment number who have NOT yet submitted examination forms for the active session.</p>
        </div>
        <div class="d-flex gap-2">
            <span class="sa-badge sa-badge-gold">SESSION: {{ $activeYear->label ?? 'Current' }}</span>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="sa-paper p-4">
        <form method="GET" action="{{ route('superadmin.reports.gap') }}" class="d-flex gap-3 flex-wrap align-end">
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
            <div style="flex: 1; min-width: 180px;">
                <label class="sa-form-label">Search</label>
                <input type="text" name="search" class="sa-input" placeholder="Candidate name or enrollment #" value="{{ request('search') }}">
            </div>
            <div>
                <button type="submit" class="sa-btn sa-btn-gold">Filter Gap Records</button>
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
                        <th>Institution</th>
                        <th>District</th>
                        <th class="text-center">Exam Form Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $stu)
                    <tr>
                        <td class="font-mono font-bold text-amber-700">{{ $stu->enrollment_number }}</td>
                        <td>
                            <div class="font-bold text-slate-800">{{ $stu->full_name }}</div>
                            <div class="text-xs text-slate-500">S/D/O {{ $stu->father_name }}</div>
                        </td>
                        <td>
                            <div class="text-sm font-semibold text-slate-700">{{ $stu->school?->name ?? 'N/A' }}</div>
                            <span class="text-xs font-mono text-slate-400">{{ $stu->school?->username ?? '' }}</span>
                        </td>
                        <td>{{ $stu->school?->district?->name ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span class="sa-badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                                Missing Exam Registration
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-slate-400">Zero gap detected! All enrolled students have submitted examination forms.</td>
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
