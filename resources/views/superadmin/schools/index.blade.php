@extends('layouts.superadmin')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Page Header & Action -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Schools & Institutions Directory</h1>
            <p style="font-size: 13.5px; color: #64748b; margin: 4px 0 0 0;">Manage registered secondary schools, higher secondary colleges, and access permissions across all 5 districts.</p>
        </div>
        <a href="{{ route('superadmin.schools.create') }}" class="sa-header-btn" style="background: linear-gradient(135deg, #C8960C, #b08209); color: #ffffff; border: none; font-weight: 700; padding: 10px 20px; box-shadow: 0 4px 14px rgba(200, 150, 12, 0.3);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Register New School</span>
        </a>
    </div>

    <!-- Filter Bar Card -->
    <div class="sa-panel" style="padding: 18px 24px;">
        <form method="GET" action="{{ route('superadmin.schools') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 100px; gap: 14px; align-items: flex-end;">
            <!-- District -->
            <div>
                <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 6px; display: block;">District</label>
                <select name="district_id" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff; color: #0f172a;">
                    <option value="">All Districts</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}" {{ ($filters['district_id'] ?? '') == $d->id ? 'selected' : '' }}>{{ $d->name }} ({{ $d->code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- School Type -->
            <div>
                <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 6px; display: block;">School Type</label>
                <select name="school_type" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff; color: #0f172a;">
                    <option value="">All Types</option>
                    <option value="school" {{ ($filters['school_type'] ?? '') == 'school' ? 'selected' : '' }}>Public / Govt</option>
                    <option value="college" {{ ($filters['school_type'] ?? '') == 'college' ? 'selected' : '' }}>Private</option>
                </select>
            </div>

            <!-- Class Level Allowed -->
            <div>
                <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 6px; display: block;">Level Tier</label>
                <select name="level" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff; color: #0f172a;">
                    <option value="">All Levels</option>
                    <option value="ssc_only" {{ ($filters['level'] ?? '') == 'ssc_only' ? 'selected' : '' }}>SSC Only (IX-X)</option>
                    <option value="combined" {{ ($filters['level'] ?? '') == 'combined' ? 'selected' : '' }}>Combined (SSC & HSC)</option>
                    <option value="hsc_only" {{ ($filters['level'] ?? '') == 'hsc_only' ? 'selected' : '' }}>HSC Only (XI-XII)</option>
                </select>
            </div>

            <!-- Active Status Toggle -->
            <div>
                <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 6px; display: block;">Status</label>
                <select name="is_active" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff; color: #0f172a;">
                    <option value="">All Statuses</option>
                    <option value="1" {{ ($filters['is_active'] ?? '') === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ ($filters['is_active'] ?? '') === '0' ? 'selected' : '' }}>Suspended Only</option>
                </select>
            </div>

            <!-- Search Field -->
            <div style="grid-column: span 1;">
                <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 6px; display: block;">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, code, SEMIS..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" class="sa-header-btn" style="width: 100%; background: #1B3A6B; color: #fff; justify-content: center; border: none; font-weight: 600; padding: 9px 0;">Filter</button>
            </div>
        </form>
    </div>

    <!-- Schools DataGrid -->
    <div class="sa-panel" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Username / Code</th>
                        <th>School Name</th>
                        <th>SEMIS Code</th>
                        <th>District</th>
                        <th>Level Tier</th>
                        <th>Type</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schools as $sch)
                        @php
                            $levels = $sch->getAllowedLevelsList();
                            $hasSSC = in_array('ssc_part1', $levels) || in_array('ssc_part2', $levels);
                            $hasHSC = in_array('hsc_part1', $levels) || in_array('hsc_part2', $levels);
                        @endphp
                        <tr>
                            <!-- Username monospace navy chip -->
                            <td>
                                <span style="font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 12.5px; background: #0f172a; color: #ffffff; padding: 4px 10px; border-radius: 6px; letter-spacing: 0.5px;">
                                    {{ $sch->username }}
                                </span>
                            </td>

                            <!-- School Name bold -->
                            <td>
                                <strong style="color: #0f172a; font-size: 14px;">{{ $sch->name }}</strong>
                                <div style="font-size: 12px; color: #64748b;">Principal: {{ $sch->principal_name ?? 'Not assigned' }}</div>
                            </td>

                            <!-- SEMIS Code monospace small -->
                            <td style="font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #475569;">
                                {{ $sch->semis_code ?? '—' }}
                            </td>

                            <!-- District -->
                            <td>
                                <span style="font-weight: 600; color: #1e293b;">{{ $sch->district->name ?? '—' }}</span>
                                <div style="font-size: 11px; color: #94a3b8;">{{ $sch->tehsil->name ?? '' }}</div>
                            </td>

                            <!-- Level Badge: SSC Only (blue), Combined (purple), HSC Only (green) -->
                            <td>
                                @if($hasSSC && $hasHSC)
                                    <span class="sa-chip sa-chip-purple">Combined (SSC & HSC)</span>
                                @elseif($hasSSC)
                                    <span class="sa-chip sa-chip-blue">SSC Only</span>
                                @elseif($hasHSC)
                                    <span class="sa-chip sa-chip-green">HSC Only</span>
                                @else
                                    <span class="sa-chip sa-chip-blue">Standard</span>
                                @endif
                            </td>

                            <!-- Type: Public or Private chip -->
                            <td>
                                @if($sch->type === 'school' || $sch->type === 'public')
                                    <span class="sa-chip sa-chip-green">Public</span>
                                @else
                                    <span class="sa-chip sa-chip-gold">Private</span>
                                @endif
                            </td>

                            <!-- Status switch toggle -->
                            <td style="text-align: center;">
                                <label style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                                    <input type="checkbox" {{ $sch->is_active ? 'checked' : '' }} onchange="toggleSchoolActive({{ $sch->id }}, this)" style="opacity: 0; width: 0; height: 0;">
                                    <span class="sa-toggle-slider" style="position: absolute; cursor: pointer; inset: 0; background-color: {{ $sch->is_active ? '#10b981' : '#cbd5e1' }}; border-radius: 24px; transition: .25s;">
                                        <span style="position: absolute; height: 18px; width: 18px; left: {{ $sch->is_active ? '22px' : '3px' }}; bottom: 3px; background-color: white; border-radius: 50%; transition: .25s;"></span>
                                    </span>
                                </label>
                            </td>

                            <!-- Actions -->
                            <td style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <a href="{{ route('superadmin.schools.show', $sch->id) }}" class="sa-header-btn" style="padding: 5px 10px; font-size: 12px;" title="View Details">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 48px 24px;">
                                <div style="width: 56px; height: 56px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; color: #94a3b8;">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v14M21 7v14M6 11h2M6 15h2M11 11h2M11 15h2M16 11h2M16 15h2M3 7l9-4 9 4"/></svg>
                                </div>
                                <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">No matching schools found</h4>
                                <p style="font-size: 13px; color: #64748b; margin: 4px 0 16px 0;">Try adjusting your filters or register a new institution.</p>
                                <a href="{{ route('superadmin.schools.create') }}" class="sa-header-btn" style="display: inline-flex; background: #C8960C; color: #fff; border: none; font-weight: 600;">
                                    Register School
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($schools->hasPages())
            <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                <div style="font-size: 13px; color: #64748b;">
                    Showing <strong>{{ $schools->firstItem() }}</strong> to <strong>{{ $schools->lastItem() }}</strong> of <strong>{{ $schools->total() }}</strong> schools
                </div>
                <div>
                    {{ $schools->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
    async function toggleSchoolActive(schoolId, checkbox) {
        try {
            const res = await fetch(`/superadmin/schools/${schoolId}/toggle-active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                const slider = checkbox.nextElementSibling;
                const thumb = slider.querySelector('span');
                slider.style.backgroundColor = data.is_active ? '#10b981' : '#cbd5e1';
                thumb.style.left = data.is_active ? '22px' : '3px';
            } else {
                checkbox.checked = !checkbox.checked;
                alert('Could not update status: ' + (data.message || 'Server error'));
            }
        } catch(e) {
            checkbox.checked = !checkbox.checked;
            alert('Network error while toggling school status.');
        }
    }
</script>
@endpush
@endsection
