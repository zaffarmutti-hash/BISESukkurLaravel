@extends('layouts.superadmin')

@section('content')
<div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

    <!-- Breadcrumb & Title -->
    <div>
        <a href="{{ route('superadmin.schools') }}" style="font-size: 13px; color: #64748b; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            <span>Back to Schools Directory</span>
        </a>
        <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Register New Institution</h1>
        <p style="font-size: 13.5px; color: #64748b; margin: 4px 0 0 0;">Create a new public/private school or college with automatically generated administrator credentials.</p>
    </div>

    @if(session('registered_success'))
        <!-- ═════════════════════════════════════════════════════════════════
             SUCCESS CREDENTIALS CARD (Replaces form on valid submit)
             ═════════════════════════════════════════════════════════════════ -->
        <div class="sa-panel" style="border: 2px solid #10b981; background: #ffffff; padding: 36px; text-align: center;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0;">School Registered Successfully!</h2>
            <p style="font-size: 14px; color: #64748b; margin: 6px 0 24px 0;"><strong>{{ session('new_school_name') }}</strong> has been configured with official credentials.</p>

            <!-- Credentials Box -->
            <div style="background: #0f172a; color: #ffffff; border-radius: 14px; padding: 24px; max-width: 480px; margin: 0 auto 20px; text-align: left;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #C8960C; margin-bottom: 12px;">OFFICIAL ACCESS CREDENTIALS</div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <div>
                        <div style="font-size: 11px; color: #94a3b8;">Assigned School Username:</div>
                        <div id="credUsername" style="font-family: 'JetBrains Mono', monospace; font-size: 20px; font-weight: 800; color: #ffffff;">{{ session('new_username') }}</div>
                    </div>
                    <button type="button" onclick="copyCred('credUsername', this)" class="sa-header-btn" style="background: rgba(255,255,255,0.1); color: #fff; border: none; padding: 6px 12px; font-size: 12px;">Copy</button>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 11px; color: #94a3b8;">Initial Password (same as username):</div>
                        <div id="credPassword" style="font-family: 'JetBrains Mono', monospace; font-size: 20px; font-weight: 800; color: #C8960C;">{{ session('new_password') }}</div>
                    </div>
                    <button type="button" onclick="copyCred('credPassword', this)" class="sa-header-btn" style="background: rgba(255,255,255,0.1); color: #fff; border: none; padding: 6px 12px; font-size: 12px;">Copy</button>
                </div>
            </div>

            <!-- Mandatory Warning Alert -->
            <div style="max-width: 480px; margin: 0 auto 28px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 12px 16px; text-align: left; font-size: 12.5px; color: #92400e;">
                <strong>IMPORTANT:</strong> These credentials are displayed only once. The school principal must change their password on first login.
            </div>

            <!-- Action buttons -->
            <div style="display: flex; justify-content: center; gap: 14px;">
                <a href="{{ route('superadmin.schools.create') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; padding: 10px 20px; font-weight: 700;">
                    Register Another School
                </a>
                <a href="{{ route('superadmin.schools.show', session('new_school_id')) }}" class="sa-header-btn" style="border: 1px solid #cbd5e1; padding: 10px 20px; font-weight: 700;">
                    View School Profile &rarr;
                </a>
            </div>
        </div>
    @else
        <!-- ═════════════════════════════════════════════════════════════════
             REGISTRATION FORM
             ═════════════════════════════════════════════════════════════════ -->
        <form method="POST" action="{{ route('superadmin.schools.store') }}">
            @csrf

            <!-- Section 1: School Information -->
            <div class="sa-panel" style="margin-bottom: 24px; border-left: 4px solid #1B3A6B;">
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                    <span style="width: 24px; height: 24px; border-radius: 6px; background: #1B3A6B; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">1</span>
                    <span>School Information</span>
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
                    <!-- School Name -->
                    <div style="grid-column: span 2;">
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">School / College Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Govt. High School Shahdadpur / Army Public School" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;">
                        @error('name')<span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>@enderror
                    </div>

                    <!-- SEMIS Code (8 digits) -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">SEMIS Code (8 Digits Unique) *</label>
                        <input type="text" name="semis_code" value="{{ old('semis_code') }}" required maxlength="8" placeholder="e.g. 40102030" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; font-family: 'JetBrains Mono', monospace;">
                        @error('semis_code')<span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>@enderror
                    </div>

                    <!-- District -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">District *</label>
                        <select name="district_id" required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; background: #fff;">
                            <option value="">Select District</option>
                            @foreach($districts as $d)
                                <option value="{{ $d->id }}" {{ old('district_id') == $d->id ? 'selected' : '' }}>{{ $d->name }} (Code: {{ $d->code }})</option>
                            @endforeach
                        </select>
                        @error('district_id')<span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>@enderror
                    </div>

                    <!-- Tehsil -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Tehsil / Taluka</label>
                        <input type="text" name="tehsil" value="{{ old('tehsil') }}" placeholder="e.g. Rohri, Kot Diji" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;">
                    </div>

                    <!-- School Type (Public / Private) -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Institutional Type *</label>
                        <div style="display: flex; gap: 20px; align-items: center; padding-top: 6px;">
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; cursor: pointer;">
                                <input type="radio" name="school_type" value="public" {{ old('school_type', 'public') === 'public' ? 'checked' : '' }}>
                                <span>Public / Government</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; cursor: pointer;">
                                <input type="radio" name="school_type" value="private" {{ old('school_type') === 'private' ? 'checked' : '' }}>
                                <span>Private Sector</span>
                            </label>
                        </div>
                    </div>

                    <!-- Gender -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Gender *</label>
                        <select name="gender" required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; background: #fff;">
                            <option value="mixed" {{ old('gender') === 'mixed' ? 'selected' : '' }}>Co-Education / Mixed</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Boys Only</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Girls Only</option>
                        </select>
                    </div>

                    <!-- Address -->
                    <div style="grid-column: span 2;">
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Campus Address</label>
                        <input type="text" name="address" value="{{ old('address') }}" placeholder="Street, City, Postal Location" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;">
                    </div>
                </div>
            </div>

            <!-- Section 2: Head of School -->
            <div class="sa-panel" style="margin-bottom: 24px; border-left: 4px solid #C8960C;">
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                    <span style="width: 24px; height: 24px; border-radius: 6px; background: #C8960C; color: #0f172a; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">2</span>
                    <span>Head of School / Focal Person</span>
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px;">
                    <!-- Head Name -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Principal / Head Name *</label>
                        <input type="text" name="head_name" value="{{ old('head_name') }}" required placeholder="e.g. Dr. Ghulam Murtaza" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;">
                        @error('head_name')<span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>@enderror
                    </div>

                    <!-- Head Mobile (auto-formatted 03XX-XXXXXXX) -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Mobile Number * (03XX-XXXXXXX)</label>
                        <input type="text" id="headMobile" name="head_mobile" value="{{ old('head_mobile') }}" required placeholder="0300-1234567" maxlength="12" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; font-family: monospace;">
                        @error('head_mobile')<span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>@enderror
                    </div>

                    <!-- Head CNIC (auto-formatted 00000-0000000-0) -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">National ID / CNIC *</label>
                        <input type="text" id="headCnic" name="head_cnic" value="{{ old('head_cnic') }}" required placeholder="45504-1234567-1" maxlength="15" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; font-family: monospace;">
                        @error('head_cnic')<span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>@enderror
                    </div>

                    <!-- Head Email -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Official Email</label>
                        <input type="email" name="head_email" value="{{ old('head_email') }}" placeholder="principal@school.edu.pk" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;">
                    </div>
                </div>
            </div>

            <!-- Section 3: Class Levels Allowed -->
            <div class="sa-panel" style="margin-bottom: 24px; border-left: 4px solid #10b981;">
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
                    <span style="width: 24px; height: 24px; border-radius: 6px; background: #10b981; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">3</span>
                    <span>Class Levels Allowed</span>
                </h3>

                <!-- Warning Alert -->
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 12px 16px; margin-bottom: 18px; font-size: 12.5px; color: #1e40af;">
                    <strong>Crucial:</strong> This configuration permanently controls which student enrollment and exam modules this institution can access. Changes after creation require Super Admin action.
                </div>

                <!-- 4 Switch Toggles with dependencies -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 18px;">
                    <!-- SSC-I -->
                    <label style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                        <div>
                            <strong style="color: #0f172a; font-size: 13.5px; display: block;">SSC Part-I</strong>
                            <span style="font-size: 11px; color: #64748b;">Class IX</span>
                        </div>
                        <input type="checkbox" id="chkSSCI" name="allowed_levels[]" value="ssc_part1" checked onchange="handleLevelChange()" style="width: 18px; height: 18px; accent-color: #10b981; cursor: pointer;">
                    </label>

                    <!-- SSC-II -->
                    <label style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                        <div>
                            <strong style="color: #0f172a; font-size: 13.5px; display: block;">SSC Part-II</strong>
                            <span style="font-size: 11px; color: #64748b;">Class X (Requires SSC-I)</span>
                        </div>
                        <input type="checkbox" id="chkSSCII" name="allowed_levels[]" value="ssc_part2" checked onchange="handleLevelChange()" style="width: 18px; height: 18px; accent-color: #10b981; cursor: pointer;">
                    </label>

                    <!-- HSC-I -->
                    <label style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                        <div>
                            <strong style="color: #0f172a; font-size: 13.5px; display: block;">HSC Part-I</strong>
                            <span style="font-size: 11px; color: #64748b;">Class XI (College)</span>
                        </div>
                        <input type="checkbox" id="chkHSCI" name="allowed_levels[]" value="hsc_part1" onchange="handleLevelChange()" style="width: 18px; height: 18px; accent-color: #10b981; cursor: pointer;">
                    </label>

                    <!-- HSC-II -->
                    <label style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                        <div>
                            <strong style="color: #0f172a; font-size: 13.5px; display: block;">HSC Part-II</strong>
                            <span style="font-size: 11px; color: #64748b;">Class XII (Requires HSC-I)</span>
                        </div>
                        <input type="checkbox" id="chkHSCII" name="allowed_levels[]" value="hsc_part2" onchange="handleLevelChange()" style="width: 18px; height: 18px; accent-color: #10b981; cursor: pointer;">
                    </label>
                </div>

                <!-- Dependency auto-enabling note -->
                <div id="depAlert" style="display: none; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 8px 12px; margin-bottom: 16px; font-size: 12px; color: #b45309;">
                    Note: Higher class level selected. Foundational Part-I level was automatically enabled.
                </div>

                <!-- Three Presets Buttons -->
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <span style="font-size: 12px; color: #64748b; align-self: center;">Quick Presets:</span>
                    <button type="button" onclick="applyPreset('ssc')" class="sa-header-btn" style="padding: 5px 12px; font-size: 12px;">SSC Only</button>
                    <button type="button" onclick="applyPreset('both')" class="sa-header-btn" style="padding: 5px 12px; font-size: 12px;">SSC & HSC (Combined)</button>
                    <button type="button" onclick="applyPreset('hsc')" class="sa-header-btn" style="padding: 5px 12px; font-size: 12px;">HSC Only</button>
                </div>
            </div>

            <!-- Submit Button Card -->
            <div style="display: flex; justify-content: flex-end; gap: 14px;">
                <a href="{{ route('superadmin.schools') }}" class="sa-header-btn" style="padding: 10px 20px;">Cancel</a>
                <button type="submit" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; font-weight: 700; padding: 10px 24px;">
                    Generate Credentials & Register School
                </button>
            </div>
        </form>
    @endif

</div>

@push('scripts')
<script>
    // Auto format Mobile: 03XX-XXXXXXX
    const mobileInput = document.getElementById('headMobile');
    if (mobileInput) {
        mobileInput.addEventListener('input', function(e) {
            let val = e.target.value.replace(/\D/g, '');
            if (val.length > 4) {
                val = val.substring(0, 4) + '-' + val.substring(4, 11);
            }
            e.target.value = val;
        });
    }

    // Auto format CNIC: 00000-0000000-0
    const cnicInput = document.getElementById('headCnic');
    if (cnicInput) {
        cnicInput.addEventListener('input', function(e) {
            let val = e.target.value.replace(/\D/g, '');
            if (val.length > 5 && val.length <= 12) {
                val = val.substring(0, 5) + '-' + val.substring(5);
            } else if (val.length > 12) {
                val = val.substring(0, 5) + '-' + val.substring(5, 12) + '-' + val.substring(12, 13);
            }
            e.target.value = val;
        });
    }

    // Dependency enforcement: SSC-II requires SSC-I, HSC-II requires HSC-I
    function handleLevelChange() {
        const sscI = document.getElementById('chkSSCI');
        const sscII = document.getElementById('chkSSCII');
        const hscI = document.getElementById('chkHSCI');
        const hscII = document.getElementById('chkHSCII');
        const depAlert = document.getElementById('depAlert');

        let triggered = false;

        if (sscII.checked && !sscI.checked) {
            sscI.checked = true;
            triggered = true;
        }

        if (hscII.checked && !hscI.checked) {
            hscI.checked = true;
            triggered = true;
        }

        depAlert.style.display = triggered ? 'block' : 'none';
    }

    function applyPreset(preset) {
        const sscI = document.getElementById('chkSSCI');
        const sscII = document.getElementById('chkSSCII');
        const hscI = document.getElementById('chkHSCI');
        const hscII = document.getElementById('chkHSCII');

        if (preset === 'ssc') {
            sscI.checked = true;
            sscII.checked = true;
            hscI.checked = false;
            hscII.checked = false;
        } else if (preset === 'hsc') {
            sscI.checked = false;
            sscII.checked = false;
            hscI.checked = true;
            hscII.checked = true;
        } else if (preset === 'both') {
            sscI.checked = true;
            sscII.checked = true;
            hscI.checked = true;
            hscII.checked = true;
        }
        document.getElementById('depAlert').style.display = 'none';
    }

    function copyCred(elementId, btn) {
        const text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(() => {
            const original = btn.innerText;
            btn.innerText = 'Copied!';
            setTimeout(() => btn.innerText = original, 1800);
        });
    }
</script>
@endpush
@endsection
