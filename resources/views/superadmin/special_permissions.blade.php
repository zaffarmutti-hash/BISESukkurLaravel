@extends('layouts.superadmin')

@section('title', 'Special Permissions & Exceptions')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Special Permissions & School Exceptions</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Time-bound, audited exceptions granted to individual institutions with clear justifications.</p>
        </div>
        <div>
            <button type="button" class="sa-btn sa-btn-gold" onclick="document.getElementById('modal-grant-exception').style.display='flex'">
                + Grant School Exception
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

    <!-- Filter Bar -->
    <div class="sa-paper p-4">
        <form method="GET" action="{{ route('superadmin.special-permissions.index') }}" class="d-flex gap-3 flex-wrap align-end">
            <div style="flex: 1; min-width: 200px;">
                <label class="sa-form-label">Exception Type</label>
                <select name="exception_type" class="sa-input">
                    <option value="">All Exception Types</option>
                    @foreach($types as $t)
                        <option value="{{ $t['value'] }}" {{ request('exception_type') == $t['value'] ? 'selected' : '' }}>
                            {{ $t['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 150px;">
                <label class="sa-form-label">Status</label>
                <select name="active_only" class="sa-input">
                    <option value="1" {{ request('active_only', '1') == '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ request('active_only') === '0' ? 'selected' : '' }}>All (Including Expired/Revoked)</option>
                </select>
            </div>

            <div>
                <button type="submit" class="sa-btn sa-btn-primary">Filter</button>
                <a href="{{ route('superadmin.special-permissions.index') }}" class="sa-btn sa-btn-outline" style="margin-left: 6px;">Reset</a>
            </div>
        </form>
    </div>

    <!-- Exceptions Table -->
    <div class="sa-paper p-5">
        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>School Institution</th>
                        <th>Exception Type</th>
                        <th>Academic Session</th>
                        <th>Granted By</th>
                        <th>Expiry Date</th>
                        <th>Reason / Justification</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exceptions as $ex)
                        <tr>
                            <td>
                                <div class="font-bold text-slate-900">{{ $ex->school->name ?? 'N/A' }}</div>
                                <div class="text-xs font-mono text-slate-400">SEMIS: {{ $ex->school->username ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <span class="sa-badge sa-badge-gold">
                                    {{ ucwords(str_replace('_', ' ', $ex->exception_type)) }}
                                </span>
                            </td>
                            <td class="font-semibold">{{ $ex->academicYear->label ?? 'All Sessions' }}</td>
                            <td>
                                <div class="text-sm font-medium text-slate-800">{{ $ex->grantedBy->name ?? 'Administrator' }}</div>
                                <div class="text-xs text-slate-400">{{ $ex->created_at?->format('d M Y, h:i A') }}</div>
                            </td>
                            <td>
                                @if($ex->expires_at)
                                    <div class="{{ $ex->expires_at->isPast() ? 'text-red-500 font-bold' : 'text-slate-700' }}">
                                        {{ $ex->expires_at->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-slate-400">
                                        {{ $ex->expires_at->isPast() ? 'Expired' : $ex->expires_at->diffForHumans() }}
                                    </div>
                                @else
                                    <span class="text-slate-400">Indefinite</span>
                                @endif
                            </td>
                            <td style="max-width: 280px;">
                                <div class="text-xs text-slate-600" style="white-space: normal; line-height: 1.4;">
                                    {{ $ex->reason }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($ex->is_active && !$ex->revoked_at && (!$ex->expires_at || $ex->expires_at->isFuture()))
                                    <form method="POST" action="{{ route('superadmin.special-permissions.revoke', $ex->id) }}" onsubmit="return confirm('Are you sure you want to revoke this special permission immediately?');">
                                        @csrf
                                        <button type="submit" class="sa-btn sa-btn-outline" style="color: #dc2626; border-color: #fca5a5; font-size: 11px; padding: 4px 8px;">
                                            Revoke
                                        </button>
                                    </form>
                                @else
                                    <span class="sa-badge sa-badge-gray">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 py-5">
                                No special permission records match the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $exceptions->links() }}
        </div>
    </div>
</div>

<!-- Modal: Grant Exception -->
<div id="modal-grant-exception" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="sa-paper p-6" style="width: 100%; max-width: 580px; max-height: 90vh; overflow-y: auto;">
        <div class="d-flex justify-between align-center border-b border-slate-100 pb-3 mb-4">
            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">Grant Special School Permission</h3>
            <button type="button" onclick="document.getElementById('modal-grant-exception').style.display='none'" style="background: none; border: none; font-size: 20px; color: #64748b; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" action="{{ route('superadmin.special-permissions.store') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="sa-form-label">Target School *</label>
                    <select name="school_id" class="sa-input" required>
                        <option value="">Select an affiliated school...</option>
                        @foreach($schools as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} (SEMIS: {{ $s->username }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="sa-form-label">Academic Session *</label>
                    <select name="academic_year_id" class="sa-input" required>
                        @foreach($years as $y)
                            <option value="{{ $y->id }}">{{ $y->label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="sa-form-label">Exception Type *</label>
                    <select name="exception_type" class="sa-input" required>
                        @foreach($types as $t)
                            <option value="{{ $t['value'] }}">{{ $t['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="sa-form-label">Expiration Date (Optional)</label>
                    <input type="date" name="expires_at" class="sa-input" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    <span class="text-xs text-slate-400 mt-1 block">Leave empty for session-long duration.</span>
                </div>

                <div>
                    <label class="sa-form-label">Official Justification / Board Note Reference *</label>
                    <textarea name="reason" class="sa-input" rows="3" required placeholder="Provide reference number or administrative approval context (min 10 characters)..."></textarea>
                </div>
            </div>

            <div class="d-flex justify-end gap-2 mt-6 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-grant-exception').style.display='none'" class="sa-btn sa-btn-outline">Cancel</button>
                <button type="submit" class="sa-btn sa-btn-gold">Authorize & Save Exception</button>
            </div>
        </form>
    </div>
</div>
@endsection
