@extends('layouts.superadmin')

@php
    $title             = 'Edit User — ' . $user->username;
    $breadcrumbSection = 'Users';
    $breadcrumbCurrent = 'Edit User';
@endphp

@section('content')
<div class="sa-animate-fade-up" style="max-width:800px;">

    {{-- Page Header --}}
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:28px;">
        <a href="{{ route('superadmin.users.index') }}" style="width:38px;height:38px;border-radius:10px;background:#f1f5f9;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#475569;" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='#f1f5f9'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div>
            <h1 style="font-size:22px;font-weight:800;color:#0f172a;margin:0 0 2px 0;">Edit User</h1>
            <p style="font-size:13px;color:#64748b;margin:0;">
                Editing: <code style="background:#f1f5f9;padding:1px 6px;border-radius:4px;font-size:12px;">{{ $user->username }}</code>
                · Created {{ $user->created_at?->format('d-M-Y') }}
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('superadmin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="sa-panel" style="margin-bottom:20px;">
            <div class="sa-panel-header">
                <h3 class="sa-panel-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Account Details
                </h3>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

                {{-- Username (read-only) --}}
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">Username</label>
                    <input type="text" name="username" value="{{ $user->username }}" readonly
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:monospace;background:#f8fafc;color:#64748b;cursor:not-allowed;">
                    <p style="font-size:11px;color:#94a3b8;margin:4px 0 0;">Usernames are permanent and cannot be changed.</p>
                </div>

                {{-- Full Name --}}
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">
                        Full Name <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('name') ? '#ef4444' : '#e2e8f0' }};border-radius:10px;font-size:14px;background:#fff;color:#0f172a;outline:none;transition:border-color .2s;"
                        onfocus="this.style.borderColor='#1B3A6B'" onblur="this.style.borderColor='#e2e8f0'">
                    @error('name') <p style="color:#ef4444;font-size:12px;margin:4px 0 0;">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">Email (Optional)</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('email') ? '#ef4444' : '#e2e8f0' }};border-radius:10px;font-size:14px;background:#fff;color:#0f172a;outline:none;transition:border-color .2s;"
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
                        @foreach($roles as $role)
                            <option value="{{ $role['value'] }}" {{ old('role', $user->role) === $role['value'] ? 'selected' : '' }}>{{ $role['label'] }}</option>
                        @endforeach
                    </select>
                    @error('role') <p style="color:#ef4444;font-size:12px;margin:4px 0 0;">{{ $message }}</p> @enderror
                </div>

            </div>

            {{-- District Dropdown --}}
            <div id="districtField" style="{{ $user->role === 'district_admin' ? '' : 'display:none;' }}margin-top:20px;">
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">District</label>
                <select name="district_id" style="width:100%;max-width:380px;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;background:#fff;color:#0f172a;outline:none;appearance:none;">
                    <option value="">Select District…</option>
                    @foreach($districts as $district)
                        <option value="{{ $district->id }}" {{ old('district_id', $user->district_id) == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Change Password Toggle --}}
            <div style="margin-top:24px;padding-top:20px;border-top:1px solid #f1f5f9;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;user-select:none;" onclick="togglePasswordSection()">
                    <div id="changePwdToggle" style="width:40px;height:22px;border-radius:11px;background:#e2e8f0;position:relative;transition:background .2s;cursor:pointer;">
                        <div id="changePwdThumb" style="width:18px;height:18px;border-radius:50%;background:#fff;position:absolute;top:2px;left:2px;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.2);"></div>
                    </div>
                    <input type="hidden" name="change_password" id="changePwdHidden" value="0">
                    <span style="font-size:13px;font-weight:600;color:#374151;">Change Password</span>
                </label>

                <div id="passwordSection" style="display:none;margin-top:16px;display:none;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">New Password</label>
                            <div style="position:relative;">
                                <input type="password" name="password" id="newPwdField"
                                    style="width:100%;padding:10px 42px 10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;background:#fff;color:#0f172a;outline:none;"
                                    placeholder="New password" onfocus="this.style.borderColor='#1B3A6B'" onblur="this.style.borderColor='#e2e8f0'">
                                <button type="button" onclick="togglePwd('newPwdField',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                            @error('password') <p style="color:#ef4444;font-size:12px;margin:4px 0 0;">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">Confirm New Password</label>
                            <input type="password" name="password_confirmation"
                                style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;background:#fff;color:#0f172a;outline:none;"
                                placeholder="Confirm new password" onfocus="this.style.borderColor='#1B3A6B'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Active Status --}}
            <div style="display:flex;align-items:center;gap:12px;margin-top:20px;padding-top:20px;border-top:1px solid #f1f5f9;">
                <label style="font-size:13px;font-weight:600;color:#374151;">Active Status:</label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}
                        style="width:16px;height:16px;accent-color:#10b981;cursor:pointer;">
                    <span style="font-size:13px;color:#374151;">Account is active</span>
                </label>
            </div>
        </div>

        {{-- Submit --}}
        <div style="display:flex;gap:12px;">
            <button type="submit" style="background:linear-gradient(135deg,#1B3A6B,#2d5a9e);color:#fff;border:none;border-radius:10px;padding:12px 28px;font-size:14px;font-weight:700;cursor:pointer;transition:all .2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                Save Changes
            </button>
            <a href="{{ route('superadmin.users.index') }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:10px;padding:12px 24px;font-size:14px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
let changePwdActive = false;

function handleRoleChange(role) {
    document.getElementById('districtField').style.display = role === 'district_admin' ? 'block' : 'none';
}

function togglePasswordSection() {
    changePwdActive = !changePwdActive;
    const toggle = document.getElementById('changePwdToggle');
    const thumb  = document.getElementById('changePwdThumb');
    const hidden = document.getElementById('changePwdHidden');
    const section = document.getElementById('passwordSection');
    toggle.style.background = changePwdActive ? '#1B3A6B' : '#e2e8f0';
    thumb.style.transform = changePwdActive ? 'translateX(18px)' : 'translateX(0)';
    hidden.value = changePwdActive ? '1' : '0';
    section.style.display = changePwdActive ? 'block' : 'none';
    if (changePwdActive) document.getElementById('newPwdField')?.focus();
}

function togglePwd(id, btn) {
    const field = document.getElementById(id);
    if (field) field.type = field.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
@endsection
