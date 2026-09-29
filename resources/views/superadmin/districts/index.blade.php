@extends('layouts.superadmin')

@section('title', 'Districts Management & Overview')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Districts Overview</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Comprehensive jurisdiction analytics across Sukkur, Khairpur, Naushahro Feroze, Ghotki, and Shikarpur.</p>
        </div>
        <div>
            <span class="sa-badge sa-badge-gold">SESSION: {{ $activeYear->label ?? 'Current' }}</span>
        </div>
    </div>

    @if(isset($district) && $district)
    <!-- Single District Deep Dive -->
    <div class="sa-paper p-5">
        <div class="d-flex justify-between align-center mb-4">
            <div>
                <a href="{{ route('superadmin.districts') }}" class="text-xs text-blue-600 font-semibold mb-2 inline-block">&larr; Back to all districts</a>
                <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0;">{{ $district->name }} District Details</h2>
                <p class="text-xs text-slate-500">Code: <span class="font-mono font-bold">{{ $district->code }}</span></p>
            </div>
            <form method="GET" action="{{ route('superadmin.districts') }}" class="d-flex gap-2">
                <input type="hidden" name="district_id" value="{{ $district->id }}">
                <input type="text" name="search" class="sa-input" placeholder="Search school name or code..." value="{{ request('search') }}" style="width: 250px;">
                <button type="submit" class="sa-btn sa-btn-gold">Search</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>School Name & Code</th>
                        <th class="text-right">Enrolled Students</th>
                        <th class="text-right">Verified Fees</th>
                        <th class="text-center">Pending Invoices</th>
                        <th class="text-center">Exam Gap</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schools as $sch)
                    <tr>
                        <td>
                            <div class="font-bold text-slate-900">{{ $sch['name'] }}</div>
                            <span class="text-xs font-mono text-slate-500">{{ $sch['code'] }}</span>
                        </td>
                        <td class="text-right font-bold text-slate-800">{{ number_format($sch['student_count']) }}</td>
                        <td class="text-right text-emerald-700 font-semibold">Rs. {{ number_format($sch['verified_amount']) }}</td>
                        <td class="text-center">
                            @if($sch['pending_invoices'] > 0)
                                <span class="sa-badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">{{ $sch['pending_invoices'] }} pending</span>
                            @else
                                <span class="sa-badge sa-badge-active">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($sch['missing_exam_forms'] > 0)
                                <span class="sa-badge" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a;">{{ $sch['missing_exam_forms'] }} gap</span>
                            @else
                                <span class="sa-badge sa-badge-active">0</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('superadmin.schools.show', $sch['id']) }}" class="sa-btn sa-btn-outline" style="font-size: 11px; padding: 4px 8px;">View School</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-slate-400">No schools matching search criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $schools->links() }}
        </div>
    </div>
    @else
    <!-- All 5 Districts Comparison Cards & Table -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        @foreach($districtBreakdown as $db)
        <div class="sa-paper p-5" style="border-top: 4px solid #1B3A6B; position: relative;">
            <div class="d-flex justify-between align-center mb-2">
                <span class="font-bold text-slate-900" style="font-size: 16px;">{{ $db['name'] ?? $db['district_name'] ?? 'District' }}</span>
                <span class="text-xs font-mono font-bold" style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $db['code'] ?? $db['district_code'] ?? '#'.$db['id'] }}</span>
            </div>
            <div class="text-xs text-slate-500 mb-3">Dist. Admin ID: #{{ $db['id'] ?? $db['district_id'] }}</div>

            <div class="space-y-2" style="font-size: 13px;">
                <div class="d-flex justify-between text-slate-600">
                    <span>Registered Schools:</span>
                    <strong class="text-slate-900">{{ number_format($db['school_count']) }}</strong>
                </div>
                <div class="d-flex justify-between text-slate-600">
                    <span>Enrolled Candidates:</span>
                    <strong class="text-slate-900">{{ number_format($db['student_count']) }}</strong>
                </div>
                <div class="d-flex justify-between text-slate-600">
                    <span>Verified Collection:</span>
                    <strong class="text-emerald-700">Rs. {{ number_format($db['verified_amount']) }}</strong>
                </div>
                <div class="d-flex justify-between text-slate-600">
                    <span>Pending Invoices:</span>
                    <strong class="{{ ($db['pending_invoices'] ?? 0) > 0 ? 'text-rose-600' : 'text-slate-500' }}">{{ $db['pending_invoices'] ?? 0 }}</strong>
                </div>
                <div class="d-flex justify-between text-slate-600">
                    <span>Registration Gap:</span>
                    <strong class="{{ ($db['exam_gap'] ?? $db['missing_exam_forms'] ?? 0) > 0 ? 'text-amber-600' : 'text-slate-500' }}">{{ $db['exam_gap'] ?? $db['missing_exam_forms'] ?? 0 }}</strong>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('superadmin.districts', ['district_id' => $db['id'] ?? $db['district_id']]) }}" class="sa-btn sa-btn-gold w-100 text-center" style="font-size: 12px; padding: 6px 12px; display: block; text-decoration: none;">
                    Explore District Schools &rarr;
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Comparative Table -->
    <div class="sa-paper p-5">
        <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">District Comparative Performance Matrix</h3>
        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>District Name</th>
                        <th>Code</th>
                        <th class="text-right">Schools</th>
                        <th class="text-right">Students</th>
                        <th class="text-right">Verified Collection</th>
                        <th class="text-center">Pending Verifications</th>
                        <th class="text-center">Exam Gap</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totSchools = 0; $totStudents = 0; $totAmount = 0; $totPending = 0; $totGap = 0;
                    @endphp
                    @foreach($districtBreakdown as $row)
                    @php
                        $totSchools += $row['school_count'];
                        $totStudents += $row['student_count'];
                        $totAmount += $row['verified_amount'];
                        $totPending += $row['pending_invoices'];
                        $totGap += ($row['exam_gap'] ?? $row['missing_exam_forms'] ?? 0);
                    @endphp
                    <tr>
                        <td style="width: 36px;">
                            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: {{ ($row['pending_invoices'] ?? 0) == 0 ? '#10b981' : (($row['pending_invoices'] ?? 0) < 10 ? '#f59e0b' : '#ef4444') }};"></span>
                        </td>
                        <td class="font-bold text-slate-900">{{ $row['name'] ?? $row['district_name'] }}</td>
                        <td><span class="font-mono text-xs font-semibold">{{ $row['code'] ?? $row['district_code'] ?? '#'.$row['id'] }}</span></td>
                        <td class="text-right font-medium text-slate-700">{{ number_format($row['school_count']) }}</td>
                        <td class="text-right font-medium text-slate-700">{{ number_format($row['student_count']) }}</td>
                        <td class="text-right font-bold text-emerald-700">Rs. {{ number_format($row['verified_amount']) }}</td>
                        <td class="text-center {{ ($row['pending_invoices'] ?? 0) > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                            {{ number_format($row['pending_invoices'] ?? 0) }}
                        </td>
                        <td class="text-center {{ ($row['exam_gap'] ?? $row['missing_exam_forms'] ?? 0) > 0 ? 'text-amber-600 font-bold' : 'text-slate-400' }}">
                            {{ number_format($row['exam_gap'] ?? $row['missing_exam_forms'] ?? 0) }}
                        </td>
                        <td class="text-right">
                            <a href="{{ route('superadmin.districts', ['district_id' => $row['id'] ?? $row['district_id']]) }}" class="sa-btn sa-btn-outline" style="font-size: 11px; padding: 4px 10px;">
                                View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1;">
                        <td colspan="3" class="text-slate-900">Total Across All 5 Districts</td>
                        <td class="text-right text-slate-900">{{ number_format($totSchools) }}</td>
                        <td class="text-right text-slate-900">{{ number_format($totStudents) }}</td>
                        <td class="text-right text-emerald-700">Rs. {{ number_format($totAmount) }}</td>
                        <td class="text-center text-rose-600">{{ number_format($totPending) }}</td>
                        <td class="text-center text-amber-600">{{ number_format($totGap) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
