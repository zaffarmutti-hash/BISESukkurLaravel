@extends('layouts.superadmin')

@php
    $title             = 'Create Board User';
    $breadcrumbSection = 'Users';
    $breadcrumbCurrent = 'Create User';
@endphp

@section('content')
<div class="sa-animate-fade-up" style="max-width:800px;">

    {{-- Page Header --}}
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:28px;">
        <a href="{{ route('superadmin.users.index') }}" style="width:38px;height:38px;border-radius:10px;background:#f1f5f9;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#475569;transition:all .2s;" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='#f1f5f9'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div>
            <h1 style="font-size:22px;font-weight:800;color:#0f172a;margin:0 0 2px 0;">Create Board User</h1>
            <p style="font-size:13px;color:#64748b;margin:0;">Add a new board-level staff account. School accounts are created from the Schools page.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('superadmin.users.store') }}" id="createUserForm">
        @csrf
        <div class="sa-panel" style="margin-bottom:20px;">
            <div class="sa-panel-header">
                <h3 class="sa-panel-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Account Details
                </h3>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

                {{-- Username --}}
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">
                        Username <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" name="username" value="{{ old('username') }}" required
                        style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('username') ? '#ef4444' : '#e2e8f0' }};border-radius:10px;font-size:14px;font-family:monospace;background:#fff;color:#0f172a;outline:none;transition:border-color .2s;"
                        placeholder="e.g. bise-admin-01"
                        onfocus="this.style.borderColor='#1B3A6B'" onblur="this.style.borderColor='#e2e8f0'">
                    @error('username') <p style="color:#ef4444;font-size:12px;margin:4px 0 0;">{{ $message }}</p> @enderror
                </div>

                {{-- Full Name --}}
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">
                        Full Name <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('name') ? '#ef4444' : '#e2e8f0' }};border-radius:10px;font-size:14px;background:#fff;color:#0f172a;outline:none;transition:border-color .2s;"
                        placeholder="e.g. Muhammad Rafiq"
                        onfocus="this.style.borderColor='#1B3A6B'" onblur="this.style.borderColor='#e2e8f0'">
                    @error('name') <p style="color:#ef4444;font-size:12px;margin:4px 0 0;">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">
                        Password <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="passwordField" required
                            style="width:100%;padding:10px 42px 10px 14px;border:1.5px solid {{ $errors->has('password') ? '#ef4444' : '#e2e8f0' }};border-radius:10px;font-size:14px;background:#fff;color:#0f172a;outline:none;transition:border-color .2s;"
                            placeholder="Min. 8 chars, uppercase + number"
                            onfocus="this.style.borderColor='#1B3A6B'" onblur="this.style.borderColor='#e2e8f0'"
                            oninput="updateStrength(this.value)">
                        <button type="button" onclick="togglePwd('passwordField',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    {{-- Strength Bar --}}
                    <div style="margin-top:6px;">
                        <div id="strengthBar" style="height:3px;border-radius:9999px;background:#e2e8f0;transition:all .3s;">
                            <div id="strengthFill" style="height:100%;border-radius:9999px;width:0%;background:#ef4444;transition:all .3s;"></div>
                        </div>
                        <span id="strengthText" style="font-size:10px;color:#94a3b8;margin-top:2px;display:block;"></span>
                    </div>
                    @error('password') <p style="color:#ef4444;font-size:12px;margin:4px 0 0;">{{ $message }}</p> @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">
                        Confirm Password <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="position:relative;">
                        <input type="password" name="password_confirmation" id="confirmField" required
                            style="width:100%;padding:10px 42px 10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;background:#fff;color:#0f172a;outline:none;transition:border-color .2s;"
                            placeholder="Repeat password"
                            onfocus="this.style.borderColor='#1B3A6B'" onblur="this.style.borderColor='#e2e8f0'">
                        <button type="button" onclick="togglePwd('confirmField',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">Email (Optional)</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('email') ? '#ef4444' : '#e2e8f0' }};border-radius:10px;font-size:14px;background:#fff;color:#0f172a;outline:none;transition:border-color .2s;"
                        placeholder="user@bisesukkur.edu.pk"
                        onfocus="this.style.borderColor='#1B3A6B'" onblur="this.style.borderColor='#e2e8f0'">
                    @error('email') <p style="color:#ef4444;font-size:12px;margin:4px 0 0;">{{ $message }}</p> @enderror
                </div>

                {{-- Role --}}
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">
                        Role <span style="color:#ef4444;">*</span>
                    </label>
                    <select name="role" required onchange="handleRoleChange(this.value)"
                        style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('role') ? '#ef4444' : '#e2e8f0' }};border-radius:10px;font-size:14px;background:#fff;color:#0f172a;outline:none;appearance:none;transition:border-color .2s;"
                        onfocus="this.style.borderColor='#1B3A6B'" onblur="this.style.borderColor='#e2e8f0'">
                        <option value="">Select Role…</option>
                        @foreach($roles as $role)
                            <option value="{{ $role['value'] }}" {{ old('role') === $role['value'] ? 'selected' : '' }}>{{ $role['label'] }}</option>
                        @endforeach
                    </select>
                    @error('role') <p style="color:#ef4444;font-size:12px;margin:4px 0 0;">{{ $message }}</p> @enderror
                </div>

            </div>

            {{-- District Dropdown (shown for district_admin) --}}
            <div id="districtField" style="display:none;margin-top:20px;">
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">
                    District <span style="color:#ef4444;">*</span>
                </label>
                <select name="district_id"
                    style="width:100%;max-width:380px;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;background:#fff;color:#0f172a;outline:none;appearance:none;">
                    <option value="">Select District…</option>
                    @foreach($districts as $district)
                        <option value="{{ $district->id }}" {{ old('district_id') == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Active Status --}}
            <div style="display:flex;align-items:center;gap:12px;margin-top:20px;padding-top:20px;border-top:1px solid #f1f5f9;">
                <label style="font-size:13px;font-weight:600;color:#374151;">Active Status:</label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked
                        style="width:16px;height:16px;accent-color:#10b981;cursor:pointer;">
                    <span style="font-size:13px;color:#374151;">Account is active</span>
                </label>
            </div>
        </div>

        {{-- Submit --}}
        <div style="display:flex;gap:12px;">
            <button type="submit" style="background:linear-gradient(135deg,#1B3A6B,#2d5a9e);color:#fff;border:none;border-radius:10px;padding:12px 28px;font-size:14px;font-weight:700;cursor:pointer;transition:all .2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                Create User
            </button>
            <a href="{{ route('superadmin.users.index') }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:10px;padding:12px 24px;font-size:14px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function handleRoleChange(role) {
    document.getElementById('districtField').style.display = role === 'district_admin' ? 'block' : 'none';
}

function togglePwd(id, btn) {
    const field = document.getElementById(id);
    field.type = field.type === 'password' ? 'text' : 'password';
}

function updateStrength(val) {
    const fill = document.getElementById('strengthFill');
    const text = document.getElementById('strengthText');
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const colors = ['#ef4444','#f59e0b','#3b82f6','#10b981'];
    const labels = ['Weak','Fair','Good','Strong'];
    fill.style.width = (score * 25) + '%';
    fill.style.background = colors[score-1] || '#e2e8f0';
    text.textContent = val.length > 0 ? labels[score-1] || '' : '';
    text.style.color = colors[score-1] || '#94a3b8';
}

// Init role field on page load (for old() values)
const initRole = document.querySelector('select[name="role"]')?.value;
if (initRole) handleRoleChange(initRole);
</script>
@endpush
@endsection
