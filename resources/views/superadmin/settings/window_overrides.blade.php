@extends('layouts.superadmin')

@section('title', 'Window Overrides')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Registration Window Overrides</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">District-level and School-level customized timeline windows that override board-wide global dates.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('superadmin.enrollment-hub') }}" class="sa-btn sa-btn-outline">
                ← Enrollment Hub
            </a>
            <button type="button" class="sa-btn sa-btn-gold" onclick="document.getElementById('modal-add-override').style.display='flex'">
                + Add Window Override
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
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">Configured Active Overrides ({{ count($overrides) }})</h2>
            <span class="sa-badge sa-badge-gold">Session: {{ $activeYear->label ?? 'Current' }}</span>
        </div>

        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th>Scope Entity</th>
                        <th>Scope Level</th>
                        <th>Window Type</th>
                        <th>Normal Schedule</th>
                        <th>Grace Period End</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overrides as $o)
                        <tr>
                            <td class="font-bold text-slate-900">
                                {{ $o['scope_name'] }}
                            </td>
                            <td>
                                @if($o['scope_type'] === 'district')
                                    <span class="sa-badge sa-badge-blue">DISTRICT SCOPE</span>
                                @else
                                    <span class="sa-badge sa-badge-gray">SCHOOL TENANT</span>
                                @endif
                            </td>
                            <td>
                                <span class="sa-badge {{ $o['window_type'] === 'enrollment' ? 'sa-badge-green' : 'sa-badge-gold' }}">
                                    {{ strtoupper($o['window_type']) }}
                                </span>
                            </td>
                            <td>
                                <div class="font-semibold text-slate-800">
                                    {{ date('d M Y', strtotime($o['normal_start'])) }} — {{ date('d M Y', strtotime($o['normal_end'])) }}
                                </div>
                                <div class="text-xs text-slate-400">Regular fee period</div>
                            </td>
                            <td>
                                @if(!empty($o['grace_end']))
                                    <div class="font-semibold text-amber-700">
                                        {{ date('d M Y', strtotime($o['grace_end'])) }}
                                    </div>
                                    <div class="text-xs text-amber-600">Late fee multiplier active</div>
                                @else
                                    <span class="text-slate-400">No Grace Extension</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('superadmin.window-overrides.destroy', $o['id']) }}" onsubmit="return confirm('Remove this override? The entity will immediately inherit board-wide dates.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="sa-btn sa-btn-outline" style="color: #dc2626; border-color: #fca5a5; font-size: 11px; padding: 4px 8px;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-slate-400 py-5">
                                No custom window overrides defined. All schools and districts follow default board schedules.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Override -->
<div id="modal-add-override" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="sa-paper p-6" style="width: 100%; max-width: 540px; max-height: 90vh; overflow-y: auto;">
        <div class="d-flex justify-between align-center border-b border-slate-100 pb-3 mb-4">
            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">Create Registration Window Override</h3>
            <button type="button" onclick="document.getElementById('modal-add-override').style.display='none'" style="background: none; border: none; font-size: 20px; color: #64748b; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" action="{{ route('superadmin.window-overrides.store') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="sa-form-label">Scope Level *</label>
                    <select name="scope_type" id="scope_type_select" class="sa-input" onchange="toggleScopeInputs(this.value)" required>
                        <option value="district">District-Wide Override</option>
                        <option value="school">Single School Override</option>
                    </select>
                </div>

                <div id="district_select_wrap">
                    <label class="sa-form-label">Select District *</label>
                    <select name="scope_id_district" id="district_select" class="sa-input">
                        @foreach($districts as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="school_select_wrap" style="display: none;">
                    <label class="sa-form-label">Select School *</label>
                    <select name="scope_id_school" id="school_select" class="sa-input">
                        @foreach($schools as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->username }})</option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="scope_id" id="final_scope_id" value="{{ $districts->first()->id ?? '' }}">

                <div>
                    <label class="sa-form-label">Window Operation *</label>
                    <select name="window_type" class="sa-input" required>
                        <option value="enrollment">Student Enrollment Window</option>
                        <option value="examination">Examination Form Window</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label class="sa-form-label">Normal Start *</label>
                        <input type="date" name="normal_start" class="sa-input" required>
                    </div>
                    <div>
                        <label class="sa-form-label">Normal End *</label>
                        <input type="date" name="normal_end" class="sa-input" required>
                    </div>
                </div>

                <div>
                    <label class="sa-form-label">Grace Period End (Late Fee Applies)</label>
                    <input type="date" name="grace_end" class="sa-input">
                    <span class="text-xs text-slate-400 mt-1 block">Optional buffer period with late fee multiplier.</span>
                </div>
            </div>

            <div class="d-flex justify-end gap-2 mt-6 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-add-override').style.display='none'" class="sa-btn sa-btn-outline">Cancel</button>
                <button type="submit" class="sa-btn sa-btn-gold">Create Schedule Override</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleScopeInputs(scope) {
    var distWrap = document.getElementById('district_select_wrap');
    var schWrap = document.getElementById('school_select_wrap');
    var distSelect = document.getElementById('district_select');
    var schSelect = document.getElementById('school_select');
    var finalScope = document.getElementById('final_scope_id');

    if (scope === 'district') {
        distWrap.style.display = 'block';
        schWrap.style.display = 'none';
        finalScope.value = distSelect.value;
    } else {
        distWrap.style.display = 'none';
        schWrap.style.display = 'block';
        finalScope.value = schSelect.value;
    }
}

document.getElementById('district_select')?.addEventListener('change', function() {
    if (document.getElementById('scope_type_select').value === 'district') {
        document.getElementById('final_scope_id').value = this.value;
    }
});

document.getElementById('school_select')?.addEventListener('change', function() {
    if (document.getElementById('scope_type_select').value === 'school') {
        document.getElementById('final_scope_id').value = this.value;
    }
});
</script>
@endsection
