@extends('layouts.superadmin')

@php
    $title             = 'Controller Examination Dashboard';
    $breadcrumbSection = 'Executive';
    $breadcrumbCurrent = 'Controller Console';
@endphp

@section('content')
<div class="sa-animate-fade-up" style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Status Banner -->
    <div style="background: linear-gradient(135deg, #1B3A6B 0%, #0f172a 100%); border-radius: 16px; padding: 24px 28px; color: #ffffff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 8px 24px rgba(27,58,107,0.3);">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(200, 150, 12, 0.2); border: 1px solid #C8960C; color: #f1c40f; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; padding: 3px 12px; border-radius: 9999px; margin-bottom: 8px;">
                EXECUTIVE CONSOLE
            </div>
            <h1 style="font-size: 24px; font-weight: 800; margin: 0;">Welcome, Controller of Examinations</h1>
            <p style="font-size: 13.5px; color: rgba(255,255,255,0.7); margin: 4px 0 0 0;">
                Active Session: <strong>{{ $activeYear->name ?? '2026-2027' }}</strong> &bull; Total Candidates Enrolled: <strong>{{ number_format($totalStudents) }}</strong>
            </p>
        </div>

        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('superadmin.approvals') }}" class="sa-header-btn" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; border: none; padding: 10px 20px; font-weight: 800; text-decoration: none; border-radius: 8px; box-shadow: 0 4px 12px rgba(239,68,68,0.35);">
                Pending Approvals ({{ $pendingApprovalsCount }})
            </a>
            <a href="{{ route('superadmin.invoices.verify') }}" class="sa-header-btn" style="background: #ffffff; color: #0f172a; border: none; padding: 10px 18px; font-weight: 700; text-decoration: none; border-radius: 8px;">
                Verify Invoices
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px;">
        <div class="sa-panel" style="border-left: 4px solid #ef4444; padding: 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Awaiting Approvals</div>
            <div style="font-size: 32px; font-weight: 800; color: #ef4444; margin-top: 4px;">{{ number_format($pendingApprovalsCount) }}</div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Assistant verifications & entries</div>
        </div>

        <div class="sa-panel" style="border-left: 4px solid #10b981; padding: 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Verified Payments</div>
            <div style="font-size: 32px; font-weight: 800; color: #10b981; margin-top: 4px;">Rs {{ number_format($verifiedPaymentsRs) }}</div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Audited board revenue</div>
        </div>

        <div class="sa-panel" style="border-left: 4px solid #f59e0b; padding: 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Pending Allotments</div>
            <div style="font-size: 32px; font-weight: 800; color: #f59e0b; margin-top: 4px;">{{ number_format($pendingEnrollmentNumbers) }}</div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Paid candidates awaiting numbers</div>
        </div>

        <div class="sa-panel" style="border-left: 4px solid #1B3A6B; padding: 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Enrollment-Exam Gap</div>
            <div style="font-size: 32px; font-weight: 800; color: #1B3A6B; margin-top: 4px;">{{ number_format($enrollmentToExamGap) }}</div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Enrolled but no exam form filed</div>
        </div>
    </div>

    <!-- District Breakdown Table (Read-Only) -->
    <div class="sa-panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Jurisdiction District Census</h3>
                <p style="font-size: 13px; color: #64748b; margin: 2px 0 0 0;">Institutional performance and examination readiness across 5 Sindh districts.</p>
            </div>
            <a href="{{ route('superadmin.districts') }}" style="font-size: 13px; font-weight: 700; color: #1B3A6B; text-decoration: none;">View Detailed Report &rarr;</a>
        </div>

        <div style="overflow-x: auto;">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>District</th>
                        <th class="text-right">Active Schools</th>
                        <th class="text-right">Enrolled Students</th>
                        <th class="text-right">Verified Revenue</th>
                        <th class="text-right">Pending Verifications</th>
                        <th class="text-right">Registration Gap</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($districtStats as $ds)
                        <tr>
                            <td style="font-weight: 700; color: #0f172a;">{{ $ds['name'] }}</td>
                            <td class="text-right">{{ number_format($ds['schools_count']) }}</td>
                            <td class="text-right" style="font-weight: 700;">{{ number_format($ds['students_count']) }}</td>
                            <td class="text-right" style="color: #059669; font-weight: 700;">Rs {{ number_format($ds['verified_amount']) }}</td>
                            <td class="text-right" style="color: {{ $ds['pending_invoices'] > 0 ? '#ef4444' : '#64748b' }}; font-weight: 700;">
                                {{ number_format($ds['pending_invoices']) }}
                            </td>
                            <td class="text-right" style="color: {{ $ds['exam_gap'] > 0 ? '#f59e0b' : '#64748b' }}; font-weight: 700;">
                                {{ number_format($ds['exam_gap']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 24px;">No district records available for this session.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
