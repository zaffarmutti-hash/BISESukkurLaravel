@extends('layouts.superadmin')

@php
    $title             = $user->name . ' — User Profile';
    $breadcrumbSection = 'Users';
    $breadcrumbCurrent = $user->username;
@endphp

@section('content')
<div class="sa-animate-fade-up">

    {{-- Page Header --}}
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:28px;flex-wrap:wrap;">
        <a href="{{ route('superadmin.users.index') }}" style="width:38px;height:38px;border-radius:10px;background:#f1f5f9;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#475569;" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='#f1f5f9'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div>
            <h1 style="font-size:22px;font-weight:800;color:#0f172a;margin:0 0 4px 0;">{{ $user->name }}</h1>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <code style="background:#f1f5f9;padding:2px 8px;border-radius:6px;font-size:12px;color:#0f172a;">{{ $user->username }}</code>
                @php
                $roleColors = ['super_admin'=>'#ef4444','controller'=>'#1B3A6B','district_admin'=>'#059669','school_admin'=>'#2563eb','fee_manager'=>'#f59e0b'];
                $rc = $roleColors[$user->role] ?? '#6b7280';
                @endphp
                <span style="background:{{ $rc }}22;color:{{ $rc }};border-radius:6px;font-size:11px;font-weight:700;padding:2px 10px;border:1px solid {{ $rc }}44;">
                    {{ ucwords(str_replace('_',' ',$user->role)) }}
                </span>
                <span style="background:{{ $user->is_active ? '#ecfdf5' : '#f1f5f9' }};color:{{ $user->is_active ? '#065f46' : '#94a3b8' }};border:1px solid {{ $user->is_active ? '#a7f3d0' : '#e2e8f0' }};border-radius:9999px;font-size:11px;font-weight:700;padding:2px 10px;">
                    {{ $user->is_active ? '● Active' : '● Inactive' }}
                </span>
            </div>
        </div>
        <div style="margin-left:auto;display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('superadmin.users.edit', $user) }}" style="background:linear-gradient(135deg,#1B3A6B,#2d5a9e);color:#fff;border:none;border-radius:10px;padding:10px 20px;font-size:13px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </a>
            <form method="POST" action="{{ route('superadmin.users.toggle-active', $user) }}" style="margin:0;">
                @csrf
                <button type="submit" style="background:#f8fafc;color:{{ $user->is_active ? '#ef4444' : '#10b981' }};border:1px solid {{ $user->is_active ? '#fecaca' : '#a7f3d0' }};border-radius:10px;padding:10px 16px;font-size:13px;font-weight:700;cursor:pointer;"
                    onclick="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} this user?')">
                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">

        {{-- Main Info Card --}}
        <div>
            <div class="sa-panel" style="margin-bottom:20px;">
                <div class="sa-panel-header">
                    <h3 class="sa-panel-title">Account Information</h3>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                    @php
                    $fields = [
                        ['label' => 'Username', 'value' => $user->username, 'mono' => true],
                        ['label' => 'Full Name', 'value' => $user->name],
                        ['label' => 'Email', 'value' => $user->email ?? '—'],
                        ['label' => 'Role', 'value' => ucwords(str_replace('_',' ',$user->role))],
                        ['label' => 'District', 'value' => $user->district?->name ?? '—'],
                        ['label' => 'School', 'value' => $user->school?->name ?? '—'],
                        ['label' => 'Last Login', 'value' => $user->last_login_at?->format('d-M-Y H:i') ?? 'Never'],
                        ['label' => 'Account Created', 'value' => $user->created_at?->format('d-M-Y')],
                        ['label' => 'Must Change Password', 'value' => $user->must_change_password ? 'Yes' : 'No'],
                        ['label' => 'Failed Login Attempts', 'value' => $user->failed_login_attempts ?? 0],
                        ['label' => 'Account Locked Until', 'value' => $user->locked_until ? $user->locked_until->format('d-M-Y H:i') : 'Not Locked'],
                    ];
                    @endphp
                    @foreach($fields as $field)
                    <div style="padding:12px;background:#f8fafc;border-radius:10px;border:1px solid #f1f5f9;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;margin-bottom:4px;">{{ $field['label'] }}</div>
                        <div style="font-size:14px;font-weight:600;color:#0f172a;{{ isset($field['mono']) && $field['mono'] ? 'font-family:monospace;' : '' }}">{{ $field['value'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Reset Password Section --}}
            <div class="sa-panel">
                <div class="sa-panel-header">
                    <h3 class="sa-panel-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Reset Password
                    </h3>
                </div>
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px;margin-bottom:16px;">
                    <p style="font-size:13px;color:#92400e;margin:0;">This will immediately change the user's password and force them to set a new one on next login.</p>
                </div>
                <form method="POST" action="{{ route('superadmin.users.reset-password', $user) }}" style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end;">
                    @csrf
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:#374151;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px;">New Password</label>
                        <input type="password" name="password" required style="width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;" placeholder="New password">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:#374151;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px;">Confirm</label>
                        <input type="password" name="password_confirmation" required style="width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;" placeholder="Confirm">
                    </div>
                    <button type="submit" onclick="return confirm('Reset password for {{ $user->name }}?')" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;">Reset</button>
                    @error('password') <p style="color:#ef4444;font-size:12px;grid-column:span 3;margin:0;">{{ $message }}</p> @enderror
                </form>
            </div>
        </div>

        {{-- Sidebar: Account Status --}}
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="sa-panel">
                <div class="sa-panel-header">
                    <h3 class="sa-panel-title" style="font-size:14px;">Status</h3>
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:#f8fafc;border-radius:8px;">
                        <span style="font-size:12px;color:#64748b;font-weight:600;">Account</span>
                        <span style="background:{{ $user->is_active ? '#ecfdf5' : '#f1f5f9' }};color:{{ $user->is_active ? '#065f46' : '#6b7280' }};border-radius:9999px;font-size:11px;font-weight:700;padding:2px 10px;">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:#f8fafc;border-radius:8px;">
                        <span style="font-size:12px;color:#64748b;font-weight:600;">Locked</span>
                        <span style="background:{{ $user->isLocked() ? '#fef2f2' : '#ecfdf5' }};color:{{ $user->isLocked() ? '#991b1b' : '#065f46' }};border-radius:9999px;font-size:11px;font-weight:700;padding:2px 10px;">{{ $user->isLocked() ? 'Yes' : 'No' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:#f8fafc;border-radius:8px;">
                        <span style="font-size:12px;color:#64748b;font-weight:600;">Must Change Pwd</span>
                        <span style="background:{{ $user->must_change_password ? '#fffbeb' : '#ecfdf5' }};color:{{ $user->must_change_password ? '#92400e' : '#065f46' }};border-radius:9999px;font-size:11px;font-weight:700;padding:2px 10px;">{{ $user->must_change_password ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
            </div>

            <div class="sa-panel">
                <div class="sa-panel-header">
                    <h3 class="sa-panel-title" style="font-size:14px;">Quick Actions</h3>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <a href="{{ route('superadmin.users.edit', $user) }}" style="display:flex;align-items:center;gap:10px;padding:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:#0f172a;font-size:13px;font-weight:600;transition:all .2s;" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='#f8fafc'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit Profile
                    </a>
                    <a href="{{ route('superadmin.activity-log') }}?user_id={{ $user->id }}" style="display:flex;align-items:center;gap:10px;padding:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:#0f172a;font-size:13px;font-weight:600;transition:all .2s;" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='#f8fafc'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                        View Activity
                    </a>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('superadmin.users.destroy', $user) }}" onsubmit="return confirm('Permanently delete user {{ $user->name }}? This cannot be undone.');">
                        @csrf @method('DELETE')
                        <button type="submit" style="width:100%;display:flex;align-items:center;gap:10px;padding:10px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#991b1b;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                            Delete User
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media (max-width: 1024px) {
        div[style*="grid-template-columns:2fr 1fr"] { grid-template-columns: 1fr !important; }
    }
</style>
@endpush
@endsection
