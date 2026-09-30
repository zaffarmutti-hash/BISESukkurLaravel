@extends('layouts.superadmin')

@section('title', 'Board Fee Structures & Rates')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Board Fee Rates Matrix</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Official fee tariffs and late surcharges applied system-wide to all school registrations.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('superadmin.enrollment.fees') }}" class="sa-btn sa-btn-outline">
                ← Enrollment Hub Fees
            </a>
            <button type="button" class="sa-btn sa-btn-gold" onclick="document.getElementById('modal-add-fee').style.display='flex'">
                + Add Fee Rate
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="sa-paper p-4" style="background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46;">
            <strong>Success:</strong> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="sa-paper p-4" style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b;">
            <strong>Error:</strong> {{ session('error') }}
        </div>
    @endif

    <div class="sa-paper p-5">
        <div class="d-flex justify-between align-center mb-4">
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">Active Session Fee Rates ({{ count($fees) }})</h2>
            <span class="sa-badge sa-badge-gold">Session: {{ $activeYear->label ?? 'Current' }}</span>
        </div>

        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>Class Level</th>
                        <th>Student Category</th>
                        <th>Fee Type</th>
                        <th class="text-right">Standard Amount (PKR)</th>
                        <th class="text-right">Grace Late Surcharge</th>
                        <th class="text-right">Total Grace Amount</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fees as $f)
                        @php
                            $baseAmount = ($f->amount_paisas ?? 0) / 100;
                            $surcharge = ($f->late_fee_surcharge_paisas ?? 0) / 100;
                            $totalGrace = $baseAmount + $surcharge;
                        @endphp
                        <tr>
                            <td class="font-bold text-slate-900">
                                {{ strtoupper(str_replace('_', ' ', $f->class_level)) }}
                            </td>
                            <td>
                                <span class="sa-badge sa-badge-blue">{{ ucfirst($f->student_type) }}</span>
                            </td>
                            <td>
                                <span class="sa-badge {{ $f->fee_type === 'enrollment' ? 'sa-badge-green' : 'sa-badge-gold' }}">
                                    {{ strtoupper($f->fee_type) }}
                                </span>
                            </td>
                            <td class="text-right font-mono font-bold text-slate-900">
                                Rs {{ number_format($baseAmount, 2) }}
                            </td>
                            <td class="text-right font-mono text-amber-700">
                                + Rs {{ number_format($surcharge, 2) }}
                            </td>
                            <td class="text-right font-mono font-bold text-emerald-700">
                                Rs {{ number_format($totalGrace, 2) }}
                            </td>
                            <td class="text-center">
                                @if($f->is_active)
                                    <span class="sa-badge sa-badge-green">Active</span>
                                @else
                                    <span class="sa-badge sa-badge-gray">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 py-5">
                                No fee structures configured for the active academic session.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Fee -->
<div id="modal-add-fee" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="sa-paper p-6" style="width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto;">
        <div class="d-flex justify-between align-center border-b border-slate-100 pb-3 mb-4">
            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">Add Board Fee Rate</h3>
            <button type="button" onclick="document.getElementById('modal-add-fee').style.display='none'" style="background: none; border: none; font-size: 20px; color: #64748b; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" action="{{ route('superadmin.fee-rates.store') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="sa-form-label">Fee Type *</label>
                    <select name="fee_type" class="sa-input" required>
                        <option value="enrollment">Enrollment Registration Fee</option>
                        <option value="examination">Examination Form Fee</option>
                    </select>
                </div>

                <div>
                    <label class="sa-form-label">Class Level *</label>
                    <select name="class_level" class="sa-input" required>
                        <option value="ssc_part1">SSC Part-I (9th)</option>
                        <option value="ssc_part2">SSC Part-II (10th)</option>
                        <option value="hsc_part1">HSC Part-I (11th)</option>
                        <option value="hsc_part2">HSC Part-II (12th)</option>
                    </select>
                </div>

                <div>
                    <label class="sa-form-label">Student Type *</label>
                    <select name="student_type" class="sa-input" required>
                        <option value="regular">Regular Candidate</option>
                        <option value="private">Private Candidate</option>
                    </select>
                </div>

                <div>
                    <label class="sa-form-label">Base Fee Amount in Paisas (e.g. 150000 = Rs 1,500) *</label>
                    <input type="number" name="amount_paisas" class="sa-input" min="0" step="100" required placeholder="e.g. 150000">
                </div>

                <div>
                    <label class="sa-form-label">Grace Late Surcharge in Paisas (e.g. 50000 = Rs 500)</label>
                    <input type="number" name="late_fee_surcharge_paisas" class="sa-input" min="0" step="100" value="0" placeholder="e.g. 50000">
                </div>

                <div>
                    <label class="sa-form-label">Status</label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13.5px;">
                        <input type="checkbox" name="is_active" value="1" checked style="accent-color: #1B3A6B; width: 18px; height: 18px;">
                        <span>Active immediately for all institutions</span>
                    </label>
                </div>
            </div>

            <div class="d-flex justify-end gap-2 mt-6 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-add-fee').style.display='none'" class="sa-btn sa-btn-outline">Cancel</button>
                <button type="submit" class="sa-btn sa-btn-gold">Create Fee Rate</button>
            </div>
        </form>
    </div>
</div>
@endsection
