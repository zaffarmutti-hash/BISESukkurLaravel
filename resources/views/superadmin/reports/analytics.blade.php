@extends('layouts.superadmin')

@section('title', 'Cross-Domain Reports & Analytics')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Cross-Domain Reports & Analytics</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Compare enrollment, fee collections, and examination metrics across districts and academic years.</p>
        </div>
        <div class="d-flex gap-3 align-center">
            <form method="GET" action="{{ route('superadmin.reports-analytics') }}" class="d-flex align-center gap-2">
                <label class="sa-form-label mb-0" style="font-size: 13px; font-weight: 700; white-space: nowrap;">Academic Session:</label>
                <select name="academic_year_id" class="sa-input" onchange="this.form.submit()" style="min-width: 170px;">
                    @foreach($years as $y)
                        <option value="{{ $y->id }}" {{ $selectedYearId == $y->id ? 'selected' : '' }}>
                            {{ $y->label }}{{ $y->is_active ? ' (Active)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
            <button type="button" class="sa-btn sa-btn-primary" onclick="window.print()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Export / Print
            </button>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <div class="sa-panel" style="padding: 18px 20px; border-left: 4px solid #1B3A6B;">
            <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Total Students</div>
            <div style="font-size: 26px; font-weight: 800; color: #0f172a; margin-top: 4px;">
                {{ number_format($summary['total_students'] ?? 0) }}
            </div>
            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Enrolled for selected year</div>
        </div>

        <div class="sa-panel" style="padding: 18px 20px; border-left: 4px solid #10b981;">
            <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Verified Fees Collected</div>
            <div style="font-size: 26px; font-weight: 800; color: #10b981; margin-top: 4px;">
                Rs {{ number_format($summary['verified_payments'] ?? 0) }}
            </div>
            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Confirmed bank receipts</div>
        </div>

        <div class="sa-panel" style="padding: 18px 20px; border-left: 4px solid #f59e0b;">
            <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Pending Verification</div>
            <div style="font-size: 26px; font-weight: 800; color: #f59e0b; margin-top: 4px;">
                {{ number_format($summary['pending_verification'] ?? 0) }}
            </div>
            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Invoices awaiting bank check</div>
        </div>

        <div class="sa-panel" style="padding: 18px 20px; border-left: 4px solid #ef4444;">
            <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Missing Exam Forms</div>
            <div style="font-size: 26px; font-weight: 800; color: #ef4444; margin-top: 4px;">
                {{ number_format($summary['missing_exam_forms'] ?? 0) }}
            </div>
            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Enrollment-to-Exam gap</div>
        </div>
    </div>

    <!-- District Breakdown Table -->
    <div class="sa-paper p-5">
        <div class="d-flex justify-between align-center mb-4">
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">District Comparative Breakdown</h2>
            <span class="sa-badge sa-badge-blue">{{ count($districtBreakdown) }} Districts Reported</span>
        </div>

        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>District Name</th>
                        <th class="text-center">Affiliated Schools</th>
                        <th class="text-center">Total Students</th>
                        <th class="text-right">Verified Fees (PKR)</th>
                        <th class="text-center">Pending Invoices</th>
                        <th class="text-center">Missing Exam Forms</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($districtBreakdown as $d)
                        @php
                            $dArr = is_array($d) ? $d : (method_exists($d, 'toArray') ? $d->toArray() : (array)$d);
                        @endphp
                        <tr>
                            <td class="font-bold text-slate-900">{{ $dArr['name'] ?? 'N/A' }}</td>
                            <td class="text-center font-semibold">{{ number_format($dArr['school_count'] ?? 0) }}</td>
                            <td class="text-center font-semibold">{{ number_format($dArr['student_count'] ?? 0) }}</td>
                            <td class="text-right font-mono font-bold text-emerald-700">Rs {{ number_format($dArr['verified_amount'] ?? 0) }}</td>
                            <td class="text-center">
                                @if(($dArr['pending_invoices'] ?? 0) > 0)
                                    <span class="sa-badge sa-badge-gold">{{ number_format($dArr['pending_invoices']) }}</span>
                                @else
                                    <span class="sa-badge sa-badge-gray">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($dArr['missing_exam_forms'] ?? 0) > 0)
                                    <span class="sa-badge sa-badge-red">{{ number_format($dArr['missing_exam_forms']) }}</span>
                                @else
                                    <span class="sa-badge sa-badge-green">0</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-slate-400 py-5">
                                No district breakdown data available for this session.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
