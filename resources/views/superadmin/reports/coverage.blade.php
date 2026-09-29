@extends('layouts.superadmin')
@section('title', 'Enrollment Coverage Report')
@section('content')
<div class="sa-animate-fade-up space-y-6">

    {{-- Header --}}
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 style="font-size:24px;font-weight:800;color:#0f172a;margin:0;">Enrollment Coverage Report</h1>
            <p style="margin:4px 0 0 0;font-size:13px;color:#64748b;">Schools with zero challan submissions — every zero means ALL students at that school are missed.</p>
        </div>
        <span class="sa-badge sa-badge-gold">SESSION: {{ $activeYear->label ?? '—' }}</span>
    </div>

    {{-- Summary KPI Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;">
        <div class="sa-paper p-4 text-center">
            <div style="font-size:32px;font-weight:800;color:#0f172a;">{{ number_format($summary['total_schools']) }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">Total Active Schools</div>
        </div>
        <div class="sa-paper p-4 text-center" style="border-left:4px solid #ef4444;">
            <div style="font-size:32px;font-weight:800;color:#ef4444;">{{ number_format($summary['zero_submission']) }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">Zero Submissions ⚠️</div>
        </div>
        <div class="sa-paper p-4 text-center" style="border-left:4px solid #f59e0b;">
            <div style="font-size:32px;font-weight:800;color:#d97706;">{{ number_format($summary['has_gap']) }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">Partial Gap Schools</div>
        </div>
        <div class="sa-paper p-4 text-center" style="border-left:4px solid #10b981;">
            <div style="font-size:32px;font-weight:800;color:#059669;">{{ number_format($summary['fully_allotted']) }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">Fully Allotted</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="sa-paper p-4">
        <form method="GET" action="{{ route('superadmin.reports.coverage') }}" class="d-flex gap-3 flex-wrap align-end">
            <div style="flex:1;min-width:180px;">
                <label class="sa-form-label">District</label>
                <select name="district_id" class="sa-input">
                    <option value="">All Districts</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}" {{ request('district_id') == $d->id ? 'selected' : ' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;min-width:160px;">
                <label class="sa-form-label">Show</label>
                <select name="show" class="sa-input">
                    <option value="all" {{ request('show','all')==='all' ? 'selected' : ' }}>All Schools</option>
                    <option value="zero" {{ request('show')==='zero' ? 'selected' : ' }}>Zero Submissions Only ⚠️</option>
                    <option value="gap" {{ request('show')==='gap' ? 'selected' : ' }}>Has Allotment Gap</option>
                </select>
            </div>
            <div><button type="submit" class="sa-btn sa-btn-gold">Filter</button></div>
            @if(request()->anyFilled(['district_id','show']))
            <div><a href="{{ route('superadmin.reports.coverage') }}" class="sa-btn sa-btn-outline">Reset</a></div>
            @endif
        </form>
    </div>

    {{-- Schools Table --}}
    <div class="sa-paper p-5">
        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>School</th>
                        <th>District</th>
                        <th class="text-right">Challans Submitted</th>
                        <th class="text-right">Challans Confirmed</th>
                        <th class="text-right">Students Enrolled</th>
                        <th class="text-right">Numbers Allotted</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schools as $row)
                    <tr style="{{ $row['is_zero'] ? 'background:#fff1f2;' : ($row['has_gap'] ? 'background:#fffbeb;' : '') }}">
                        <td>
                            <div style="font-weight:600;color:#0f172a;">{{ $row['school']->name }}</div>
                            <div style="font-size:12px;font-family:monospace;color:#94a3b8;">{{ $row['school']->username }}</div>
                        </td>
                        <td>{{ $row['district'] }}</td>
                        <td class="text-right font-bold">{{ number_format($row['submitted']) }}</td>
                        <td class="text-right">{{ number_format($row['confirmed']) }}</td>
                        <td class="text-right">{{ number_format($row['enrolled']) }}</td>
                        <td class="text-right">{{ number_format($row['allotted']) }}</td>
                        <td class="text-center">
                            @if($row['is_zero'])
                                <span class="sa-badge" style="background:#fee2e2;color:#991b1b;">⚠ Zero Submissions</span>
                            @elseif($row['has_gap'])
                                <span class="sa-badge sa-badge-gold">Partial Gap</span>
                            @else
                                <span class="sa-badge sa-badge-active">Complete</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-6" style="color:#94a3b8;">No schools found for selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
