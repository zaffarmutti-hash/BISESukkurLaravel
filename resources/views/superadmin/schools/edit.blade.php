@extends('layouts.superadmin')

@php
    $title             = 'Edit Institution — ' . $school->name;
    $breadcrumbSection = 'Management';
    $breadcrumbCurrent = 'Edit School';
    $currentLevels     = is_array($school->allowed_levels) ? $school->allowed_levels : [];
@endphp

@section('content')
<div class="sa-animate-fade-up" style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

    <!-- Breadcrumb & Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <a href="{{ route('superadmin.schools.show', $school->id) }}" style="font-size: 13px; color: #64748b; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                <span>Back to School Profile</span>
            </a>
            <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Edit Institution: {{ $school->name }}</h1>
            <p style="font-size: 13.5px; color: #64748b; margin: 4px 0 0 0;">Update institutional records, administration details, and class level authorizations.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('superadmin.schools.show', $school->id) }}" class="sa-header-btn" style="border: 1px solid #cbd5e1; padding: 8px 16px; font-weight: 700; text-decoration: none;">
                Cancel
            </a>
        </div>
    </div>

    <!-- EDIT FORM -->
    <form method="POST" action="{{ route('superadmin.schools.update', $school->id) }}" id="editSchoolForm">
        @csrf
        @method('PUT')

        <!-- SECTION 1: School Identity & Location -->
        <div class="sa-panel" style="margin-bottom: 24px; border-left: 4px solid #1B3A6B;">
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                <span style="width: 24px; height: 24px; border-radius: 6px; background: #1B3A6B; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">1</span>
                <span>Institution Identity & Profile</span>
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
                <!-- Permanent Username (Readonly) -->
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 6px; display: block;">Board Username (Permanent)</label>
                    <input type="text" value="{{ $school->username }}" readonly style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13.5px; font-family: monospace; background: #f8fafc; color: #475569; font-weight: 700;">
                    <span style="font-size: 11px; color: #94a3b8; margin-top: 4px; display: block;">Usernames are permanently tied to financial sequences and cannot be altered.</span>
                </div>

                <!-- SEMIS Code (Readonly) -->
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 6px; display: block;">SEMIS Code (Permanent)</label>
                    <input type="text" value="{{ $school->semis_code }}" readonly style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13.5px; font-family: monospace; background: #f8fafc; color: #475569; font-weight: 700;">
                    <span style="font-size: 11px; color: #94a3b8; margin-top: 4px; display: block;">Registered with School Education & Literacy Department Sindh.</span>
                </div>

                <!-- School Name -->
                <div style="grid-column: span 2;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Institution Official Name *</label>
                    <input type="text" name="name" value="{{ old('name', $school->name) }}" required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;">
                    @error('name')<span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>@enderror
                </div>

                <!-- District & Tehsil -->
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 6px; display: block;">District Jurisdiction</label>
                    <input type="text" value="{{ $school->district?->name ?? '—' }} ({{ $school->district?->code }})" readonly style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13.5px; background: #f8fafc; color: #475569;">
                </div>

                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Gender Category *</label>
                    <select name="gender" required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; background: #fff;">
                        <option value="co_education" {{ old('gender', $school->gender) === 'co_education' ? 'selected' : '' }}>Co-Education</option>
                        <option value="boys" {{ old('gender', $school->gender) === 'boys' ? 'selected' : '' }}>Boys Only</option>
                        <option value="girls" {{ old('gender', $school->gender) === 'girls' ? 'selected' : '' }}>Girls Only</option>
                    </select>
                </div>

                <!-- Full Address -->
                <div style="grid-column: span 2;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Official Campus Address *</label>
                    <textarea name="address" rows="2" required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;">{{ old('address', $school->address) }}</textarea>
                    @error('address')<span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <!-- SECTION 2: Head of Institution Details -->
        <div class="sa-panel" style="margin-bottom: 24px; border-left: 4px solid #C8960C;">
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                <span style="width: 24px; height: 24px; border-radius: 6px; background: #C8960C; color: #0f172a; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">2</span>
                <span>Principal / Head of Institution</span>
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Principal / Headmaster Name *</label>
                    <input type="text" name="head_name" value="{{ old('head_name', $school->principal_name) }}" required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;">
                    @error('head_name')<span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Mobile Contact (SMS Alerts) *</label>
                    <input type="text" name="head_mobile" value="{{ old('head_mobile', $school->phone) }}" required placeholder="03XX-XXXXXXX" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;">
                    @error('head_mobile')<span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Official Email Address</label>
                    <input type="email" name="head_email" value="{{ old('head_email', $school->email) }}" placeholder="principal@school.edu.pk" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;">
                    @error('head_email')<span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <!-- SECTION 3: Class Levels Allowed -->
        <div class="sa-panel" style="margin-bottom: 24px; border-left: 4px solid #ef4444;">
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                <span style="width: 24px; height: 24px; border-radius: 6px; background: #ef4444; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">3</span>
                <span>Authorized Examination Levels</span>
            </h3>

            <!-- Statutory Warning Card -->
            <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; gap: 14px; align-items: flex-start;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2.2" style="flex-shrink: 0; margin-top: 2px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <div style="font-size: 13px; color: #9f1239; line-height: 1.5;">
                    <strong>STATUTORY WARNING:</strong> Changing allowed class levels impacts existing student registrations and examination eligibility. Disabling a level hides previously enrolled candidates from school rosters. If you modify these levels, you must verify against board affiliation orders and type <strong>CONFIRM</strong> below.
                </div>
            </div>

            <!-- Preset Fill Buttons -->
            <div style="display: flex; gap: 10px; margin-bottom: 18px; flex-wrap: wrap;">
                <span style="font-size: 12px; font-weight: 700; color: #64748b; align-self: center;">Quick Presets:</span>
                <button type="button" onclick="setPreset('ssc_only')" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 14px; font-size: 12px; font-weight: 600; cursor: pointer;">
                    SSC Only (Class 9 & 10)
                </button>
                <button type="button" onclick="setPreset('combined')" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 14px; font-size: 12px; font-weight: 600; cursor: pointer;">
                    SSC + HSC (High School / Higher Sec)
                </button>
                <button type="button" onclick="setPreset('hsc_only')" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 14px; font-size: 12px; font-weight: 600; cursor: pointer;">
                    HSC Only (Intermediate College)
                </button>
            </div>

            <!-- Toggles Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <!-- SSC-I -->
                <label style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 16px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s;" id="label_ssc1">
                    <div>
                        <div style="font-weight: 800; font-size: 14px; color: #0f172a;">SSC-I</div>
                        <div style="font-size: 12px; color: #64748b;">Class 9 (Ninth)</div>
                    </div>
                    <input type="checkbox" name="allowed_levels[]" value="ssc_part1" id="chk_ssc1" {{ in_array('ssc_part1', $currentLevels) ? 'checked' : '' }} onchange="handleLevelChange()" style="width: 20px; height: 20px; accent-color: #1B3A6B; cursor: pointer;">
                </label>

                <!-- SSC-II -->
                <label style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 16px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s;" id="label_ssc2">
                    <div>
                        <div style="font-weight: 800; font-size: 14px; color: #0f172a;">SSC-II</div>
                        <div style="font-size: 12px; color: #64748b;">Class 10 (Matric)</div>
                    </div>
                    <input type="checkbox" name="allowed_levels[]" value="ssc_part2" id="chk_ssc2" {{ in_array('ssc_part2', $currentLevels) ? 'checked' : '' }} onchange="handleSsc2Change()" style="width: 20px; height: 20px; accent-color: #1B3A6B; cursor: pointer;">
                </label>

                <!-- HSC-I -->
                <label style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 16px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s;" id="label_hsc1">
                    <div>
                        <div style="font-weight: 800; font-size: 14px; color: #0f172a;">HSC-I</div>
                        <div style="font-size: 12px; color: #64748b;">Class 11 (First Year)</div>
                    </div>
                    <input type="checkbox" name="allowed_levels[]" value="hsc_part1" id="chk_hsc1" {{ in_array('hsc_part1', $currentLevels) ? 'checked' : '' }} onchange="handleLevelChange()" style="width: 20px; height: 20px; accent-color: #1B3A6B; cursor: pointer;">
                </label>

                <!-- HSC-II -->
                <label style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 16px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s;" id="label_hsc2">
                    <div>
                        <div style="font-weight: 800; font-size: 14px; color: #0f172a;">HSC-II</div>
                        <div style="font-size: 12px; color: #64748b;">Class 12 (Second Year)</div>
                    </div>
                    <input type="checkbox" name="allowed_levels[]" value="hsc_part2" id="chk_hsc2" {{ in_array('hsc_part2', $currentLevels) ? 'checked' : '' }} onchange="handleHsc2Change()" style="width: 20px; height: 20px; accent-color: #1B3A6B; cursor: pointer;">
                </label>
            </div>

            <!-- Confirmation Input Box (revealed when levels change) -->
            <div id="confirmLevelsBox" style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 16px; display: none;">
                <label style="font-size: 12.5px; font-weight: 700; color: #991b1b; display: block; margin-bottom: 6px;">
                    Authorized Level Modifications Detected — Type "CONFIRM" to authorize this change:
                </label>
                <div style="display: flex; gap: 12px; max-width: 400px;">
                    <input type="text" name="confirm_levels" id="confirmLevelsInput" placeholder="Type CONFIRM" style="flex: 1; padding: 9px 12px; border: 2px solid #f87171; border-radius: 8px; font-size: 13px; font-weight: 800; text-transform: uppercase;">
                </div>
                @error('confirm_levels')
                    <span style="color: #ef4444; font-size: 12px; font-weight: 600; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- SUBMIT BAR -->
        <div style="display: flex; justify-content: space-between; align-items: center; background: #ffffff; padding: 16px 24px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
            <div style="font-size: 12.5px; color: #64748b;">
                All modifications will be recorded to the immutable BISE Sukkur audit trail.
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="{{ route('superadmin.schools.show', $school->id) }}" class="sa-header-btn" style="border: 1px solid #cbd5e1; padding: 10px 20px; font-weight: 700; text-decoration: none;">
                    Cancel
                </a>
                <button type="submit" class="sa-header-btn" style="background: linear-gradient(135deg, #1B3A6B, #2d5a9e); color: #fff; border: none; padding: 10px 28px; font-weight: 800; font-size: 13.5px; cursor: pointer; border-radius: 8px; box-shadow: 0 4px 12px rgba(27,58,107,0.3);">
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const originalLevels = @json($currentLevels).sort();

    function getCurrentCheckedLevels() {
        const checked = [];
        if (document.getElementById('chk_ssc1').checked) checked.push('ssc_part1');
        if (document.getElementById('chk_ssc2').checked) checked.push('ssc_part2');
        if (document.getElementById('chk_hsc1').checked) checked.push('hsc_part1');
        if (document.getElementById('chk_hsc2').checked) checked.push('hsc_part2');
        return checked.sort();
    }

    function checkLevelDiff() {
        const cur = getCurrentCheckedLevels();
        const diff = JSON.stringify(cur) !== JSON.stringify(originalLevels);
        const box = document.getElementById('confirmLevelsBox');
        if (diff) {
            box.style.display = 'block';
        } else {
            box.style.display = 'none';
        }
    }

    function handleSsc2Change() {
        if (document.getElementById('chk_ssc2').checked) {
            document.getElementById('chk_ssc1').checked = true;
        }
        checkLevelDiff();
    }

    function handleHsc2Change() {
        if (document.getElementById('chk_hsc2').checked) {
            document.getElementById('chk_hsc1').checked = true;
        }
        checkLevelDiff();
    }

    function handleLevelChange() {
        checkLevelDiff();
    }

    function setPreset(type) {
        if (type === 'ssc_only') {
            document.getElementById('chk_ssc1').checked = true;
            document.getElementById('chk_ssc2').checked = true;
            document.getElementById('chk_hsc1').checked = false;
            document.getElementById('chk_hsc2').checked = false;
        } else if (type === 'combined') {
            document.getElementById('chk_ssc1').checked = true;
            document.getElementById('chk_ssc2').checked = true;
            document.getElementById('chk_hsc1').checked = true;
            document.getElementById('chk_hsc2').checked = true;
        } else if (type === 'hsc_only') {
            document.getElementById('chk_ssc1').checked = false;
            document.getElementById('chk_ssc2').checked = false;
            document.getElementById('chk_hsc1').checked = true;
            document.getElementById('chk_hsc2').checked = true;
        }
        checkLevelDiff();
    }

    document.addEventListener('DOMContentLoaded', () => {
        checkLevelDiff();
    });
</script>
@endpush
@endsection
