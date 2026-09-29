@extends('layouts.superadmin')
@section('title', 'Result Coverage Report')
@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 style="font-size:24px;font-weight:800;color:#0f172a;margin:0;">Result Coverage Report</h1>
            <p style="margin:4px 0 0 0;font-size:13px;color:#64748b;">Students who sat the examination but have no result published — every row is a student in limbo.</p>
        </div>
        <div class="d-flex gap-2 align-center">
            @if($totalMissing > 0)
                <span class="sa-badge" style="background:#fee2e2;color:#991b1b;font-size:14px;padding:6px 14px;">⚠ {{ number_format($totalMissing) }} students missing results</span>
            @else
                <span class="sa-badge sa-badge-active">All results published ✓</span>
            @endif
            <span class="sa-badge sa-badge-gold">{{ $activeYear->label ?? 'Current Session' }}</span>
        </div>
    </div>

    {{-- District Breakdown --}}
    <div class="sa-paper p-5">
        <h2 style="font-size:16px;font-weight:700;color:#0f172a;margin:0 0 16px 0;">District-by-District Summary</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
            @foreach($districtBreakdown as $row)
            <div class="sa-paper p-4" style="border-left:4px solid {{ $row['missing'] > 0 ? '#ef4444' : '#10b981' }};">
                <div style="font-weight:700;color:#0f172a;font-size:15px;">{{ $row['district'] }}</div>
                <div style="margin-top:8px;display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:#64748b;">Examined</span><span style="font-weight:600;">{{ number_format($row['confirmed_exam']) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:#64748b;">Has Results</span><span style="font-weight:600;color:#059669;">{{ number_format($row['with_results']) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-top:4px;padding-top:4px;border-top:1px solid #f1f5f9;">
                    <span style="color:#64748b;">Missing</span>
                    <span style="font-weight:800;color:{{ $row['missing'] > 0 ? '#ef4444' : '#059669' }};">{{ number_format($row['missing']) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Filters --}}
    <div class="sa-paper p-4">
        <form method="GET" action="{{ route('superadmin.reports.results') }}" class="d-flex gap-3 flex-wrap align-end">
            <div style="flex:1;min-width:180px;">
                <label class="sa-form-label">District</label>
                <select name="district_id" class="sa-input">
                    <option value="">All Districts</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}" {{ request('district_id') == $d->id ? 'selected' : ' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;min-width:200px;">
                <label class="sa-form-label">Search Student</label>
                <input type="text" name="search" class="sa-input" placeholder="Name or Enrollment #" value="{{ request('search') }}">
            </div>
            <div><button type="submit" class="sa-btn sa-btn-gold">Filter</button></div>
            @if(request()->anyFilled(['district_id','search']))
            <div><a href="{{ route('superadmin.reports.results') }}" class="sa-btn sa-btn-outline">Reset</a></div>
            @endif
        </form>
    </div>

    {{-- Missing Students Table --}}
    <div class="sa-paper p-5">
        <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 12px 0;">Students Without Published Results</h2>
        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>Enrollment #</th>
                        <th>Student Name</th>
                        <th>School</th>
                        <th>District</th>
                        <th class="text-center">Exam Form Status</th>
                        <th class="text-center">Action Needed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($missing as $student)
                    <tr style="background:#fff1f2;">
                        <td style="font-family:monospace;font-weight:700;">{{ $student->enrollment_number ?? '—' }}</td>
                        <td>
                            <div style="font-weight:600;">{{ $student->full_name }}</div>
                            <div style="font-size:12px;color:#64748b;">S/D/O {{ $student->father_name }}</div>
                        </td>
                        <td>
                            <div style="font-weight:600;">{{ $student->school?->name ?? '—' }}</div>
                            <div style="font-size:12px;color:#94a3b8;font-family:monospace;">{{ $student->school?->username ?? ' }}</div>
                        </td>
                        <td>{{ $student->school?->district?->name ?? '—' }}</td>
                        <td class="text-center"><span class="sa-badge sa-badge-active">Confirmed</span></td>
                        <td class="text-center"><span class="sa-badge" style="background:#fef3c7;color:#92400e;">Enter Result</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-6" style="color:#10b981;font-weight:600;">✓ All examined students have results published for the selected criteria.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $missing->links() }}</div>
    </div>
</div>
@endsection
