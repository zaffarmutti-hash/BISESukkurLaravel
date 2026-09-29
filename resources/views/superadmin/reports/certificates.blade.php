@extends('layouts.superadmin')
@section('title', 'Certificate Coverage Report')
@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 style="font-size:24px;font-weight:800;color:#0f172a;margin:0;">Certificate Coverage Report</h1>
            <p style="margin:4px 0 0 0;font-size:13px;color:#64748b;">Students who passed their exam but have not received an issued certificate.</p>
        </div>
        <span class="sa-badge sa-badge-gold">{{ $activeYear->label ?? 'Current Session' }}</span>
    </div>

    {{-- Summary KPI Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;">
        <div class="sa-paper p-4 text-center">
            <div style="font-size:32px;font-weight:800;color:#0f172a;">{{ number_format($totalPassed) }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">Total Passed</div>
        </div>
        <div class="sa-paper p-4 text-center" style="border-left:4px solid #10b981;">
            <div style="font-size:32px;font-weight:800;color:#059669;">{{ number_format($totalIssued) }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">Certificates Issued</div>
        </div>
        <div class="sa-paper p-4 text-center" style="border-left:4px solid {{ $totalPending > 0 ? '#ef4444' : '#10b981' }};">
            <div style="font-size:32px;font-weight:800;color:{{ $totalPending > 0 ? '#ef4444' : '#059669' }};">{{ number_format($totalPending) }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">Pending Issuance {{ $totalPending > 0 ? '⚠️' : '✓' }}</div>
        </div>
        @if($totalPassed > 0)
        <div class="sa-paper p-4 text-center" style="border-left:4px solid #6366f1;">
            <div style="font-size:32px;font-weight:800;color:#4f46e5;">{{ number_format(($totalIssued / $totalPassed) * 100, 1) }}%</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">Issuance Rate</div>
        </div>
        @endif
    </div>

    {{-- District Breakdown --}}
    <div class="sa-paper p-5">
        <h2 style="font-size:16px;font-weight:700;color:#0f172a;margin:0 0 16px 0;">District-by-District Summary</h2>
        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>District</th>
                        <th class="text-right">Passed Students</th>
                        <th class="text-right">Certificates Issued</th>
                        <th class="text-right">Pending</th>
                        <th class="text-center">Coverage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($districtBreakdown as $row)
                    <tr>
                        <td style="font-weight:700;">{{ $row['district'] }}</td>
                        <td class="text-right">{{ number_format($row['passed']) }}</td>
                        <td class="text-right" style="color:#059669;font-weight:600;">{{ number_format($row['issued']) }}</td>
                        <td class="text-right" style="color:{{ $row['pending'] > 0 ? '#ef4444' : '#059669' }};font-weight:700;">{{ number_format($row['pending']) }}</td>
                        <td class="text-center">
                            @if($row['passed'] > 0)
                                @php $pct = ($row['issued'] / $row['passed']) * 100; @endphp
                                <span class="sa-badge" style="background:{{ $pct >= 100 ? '#d1fae5' : ($pct >= 80 ? '#fef3c7' : '#fee2e2') }};color:{{ $pct >= 100 ? '#065f46' : ($pct >= 80 ? '#92400e' : '#991b1b') }};">
                                    {{ number_format($pct, 1) }}%
                                </span>
                            @else
                                <span style="color:#94a3b8;">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Filters --}}
    <div class="sa-paper p-4">
        <form method="GET" action="{{ route('superadmin.reports.certificates') }}" class="d-flex gap-3 flex-wrap align-end">
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
            <div><a href="{{ route('superadmin.reports.certificates') }}" class="sa-btn sa-btn-outline">Reset</a></div>
            @endif
        </form>
    </div>

    {{-- Missing Certificates Table --}}
    <div class="sa-paper p-5">
        <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 12px 0;">Students Awaiting Certificate Issuance</h2>
        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>Enrollment #</th>
                        <th>Student Name</th>
                        <th>School</th>
                        <th>District</th>
                        <th class="text-center">Result</th>
                        <th class="text-center">Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($missing as $student)
                    <tr style="background:#fffbeb;">
                        <td style="font-family:monospace;font-weight:700;">{{ $student->enrollment_number ?? '—' }}</td>
                        <td>
                            <div style="font-weight:600;">{{ $student->full_name }}</div>
                            <div style="font-size:12px;color:#64748b;">S/D/O {{ $student->father_name }}</div>
                        </td>
                        <td>
                            <div style="font-weight:600;">{{ $student->school?->name ?? '—' }}</div>
                            <div style="font-size:12px;font-family:monospace;color:#94a3b8;">{{ $student->school?->username }}</div>
                        </td>
                        <td>{{ $student->school?->district?->name ?? '—' }}</td>
                        <td class="text-center"><span class="sa-badge sa-badge-active">Passed ✓</span></td>
                        <td class="text-center"><span class="sa-badge" style="background:#fef3c7;color:#92400e;">Pending Issuance</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-6" style="color:#10b981;font-weight:600;">✓ All passing students have certificates issued.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $missing->links() }}</div>
    </div>
</div>
@endsection
