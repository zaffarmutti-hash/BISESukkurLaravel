@extends('layouts.superadmin')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">User Accounts & Role Governance</h1>
            <p style="font-size: 13.5px; color: #64748b; margin: 4px 0 0 0;">Manage administrative permissions, institutional credentials, and security access across the BISE network.</p>
        </div>

        <button type="button" onclick="openCreateUserModal()" class="sa-header-btn" style="background: linear-gradient(135deg, #1B3A6B, #2d5a9e); color: #fff; border: none; font-weight: 700; padding: 10px 20px; box-shadow: 0 4px 14px rgba(27, 58, 107, 0.25);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            <span>Create New User</span>
        </button>
    </div>

    <!-- Filter Bar Card -->
    <div class="sa-panel" style="padding: 18px 24px;">
        <form method="GET" action="{{ route('superadmin.users.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 100px; gap: 14px; align-items: flex-end;">
            <!-- Role -->
            <div>
                <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 6px; display: block;">System Role</label>
                <select name="role" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff;">
                    <option value="">All Roles</option>
                    <option value="super_admin" {{ ($filters['role'] ?? '') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="district_admin" {{ ($filters['role'] ?? '') === 'district_admin' ? 'selected' : '' }}>District Admin</option>
                    <option value="school_admin" {{ ($filters['role'] ?? '') === 'school_admin' ? 'selected' : '' }}>School Admin</option>
                </select>
            </div>

            <!-- District -->
            <div>
                <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 6px; display: block;">District</label>
                <select name="district_id" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff;">
                    <option value="">All Districts</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}" {{ ($filters['district_id'] ?? '') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 6px; display: block;">Account Status</label>
                <select name="is_active" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff;">
                    <option value="">All Statuses</option>
                    <option value="1" {{ ($filters['is_active'] ?? '') === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ ($filters['is_active'] ?? '') === '0' ? 'selected' : '' }}>Deactivated Only</option>
                </select>
            </div>

            <!-- Search -->
            <div>
                <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 6px; display: block;">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, username..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
            </div>

            <!-- Filter Button -->
            <div>
                <button type="submit" class="sa-header-btn" style="width: 100%; background: #1B3A6B; color: #fff; justify-content: center; border: none; font-weight: 600; padding: 9px 0;">Filter</button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="sa-panel" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Role Badge</th>
                        <th>School / District Scope</th>
                        <th>Account Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td>
                                <span style="font-family: 'JetBrains Mono', monospace; font-size: 12.5px; font-weight: 700; background: #0f172a; color: #ffffff; padding: 4px 10px; border-radius: 6px;">
                                    {{ $u->username }}
                                </span>
                            </td>

                            <td>
                                <strong style="color: #0f172a;">{{ $u->name }}</strong>
                                <div style="font-size: 11.5px; color: #64748b;">{{ $u->email ?? 'No email associated' }}</div>
                            </td>

                            <td>
                                @if($u->role === 'super_admin')
                                    <span class="sa-chip sa-chip-gold">Super Admin</span>
                                @elseif($u->role === 'district_admin')
                                    <span class="sa-chip sa-chip-purple">District Admin</span>
                                @elseif($u->role === 'school_admin')
                                    <span class="sa-chip sa-chip-blue">School Admin</span>
                                @else
                                    <span class="sa-chip sa-chip-blue">{{ ucfirst($u->role) }}</span>
                                @endif
                            </td>

                            <td>
                                @if($u->school)
                                    <div style="font-weight: 600; color: #1e293b;">{{ $u->school->name }}</div>
                                    <div style="font-size: 11px; color: #94a3b8;">Code: {{ $u->school->username }}</div>
                                @elseif($u->district)
                                    <div style="font-weight: 600; color: #1e293b;">{{ $u->district->name }} District</div>
                                @else
                                    <span style="color: #94a3b8; font-size: 12px;">Board Central</span>
                                @endif
                            </td>

                            <td>
                                <span class="sa-chip {{ $u->is_active ? 'sa-chip-green' : 'sa-chip-red' }}">
                                    <span class="sa-dot {{ $u->is_active ? 'sa-dot-green' : 'sa-dot-red' }}"></span>
                                    {{ $u->is_active ? 'Active' : 'Suspended' }}
                                </span>
                            </td>

                            <td style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <!-- Reset Password -->
                                    <button type="button" onclick="openResetPasswordModal({{ $u->id }}, '{{ addslashes($u->username) }}')" class="sa-header-btn" style="padding: 4px 8px; font-size: 11.5px;" title="Reset Password">
                                        Reset Pwd
                                    </button>

                                    <!-- Toggle Active -->
                                    @if($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('superadmin.users.toggle-active', $u->id) }}" style="margin: 0; display: inline;">
                                            @csrf
                                            <button type="submit" class="sa-header-btn" style="padding: 4px 8px; font-size: 11.5px; color: {{ $u->is_active ? '#ef4444' : '#10b981' }};">
                                                {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 36px;">No users match the selected query.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                <div style="font-size: 13px; color: #64748b;">
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                </div>
                <div>{{ $users->links() }}</div>
            </div>
        @endif
    </div>

</div>

<!-- ═════════════════════════════════════════════════════════════════════════
     CREATE USER MODAL
     ═════════════════════════════════════════════════════════════════════════ -->
<div id="createUserModal" class="sa-search-modal" onclick="if(event.target === this) closeCreateUserModal()">
    <div class="sa-search-card" style="max-width: 540px; padding: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">Create New User Account</h3>
            <button type="button" onclick="closeCreateUserModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" action="{{ route('superadmin.users.store') }}">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Username *</label>
                    <input type="text" name="username" required placeholder="e.g. sukkur_dist_admin" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                </div>

                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Muhammad Aslam" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                </div>

                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Email Address</label>
                    <input type="email" name="email" placeholder="admin@bisesukkur.edu.pk" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Password *</label>
                        <input type="password" name="password" required placeholder="Min 8 characters" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required placeholder="Repeat password" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    </div>
                </div>

                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Role *</label>
                    <select id="userRoleSelect" name="role" required onchange="handleRoleChange(this.value)" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff;">
                        <option value="district_admin">District Admin</option>
                        <option value="school_admin">School Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>

                <div id="districtScopeField">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Assign District *</label>
                    <select name="district_id" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff;">
                        @foreach($districts as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="schoolScopeField" style="display: none;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Assign School *</label>
                    <select name="school_id" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff;">
                        @foreach($schools as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->username }})</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 14px;">
                    <button type="button" onclick="closeCreateUserModal()" class="sa-header-btn">Cancel</button>
                    <button type="submit" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; font-weight: 700; padding: 8px 20px;">Save User Account</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════
     RESET PASSWORD MODAL
     ═════════════════════════════════════════════════════════════════════════ -->
<div id="resetPwdModal" class="sa-search-modal" onclick="if(event.target === this) closeResetPasswordModal()">
    <div class="sa-search-card" style="max-width: 440px; padding: 24px;">
        <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 8px 0;">Reset Password</h3>
        <p style="font-size: 13px; color: #64748b; margin: 0 0 18px 0;">Set a new password for <strong id="resetTargetUsername" style="color: #0f172a;"></strong>.</p>

        <form id="resetPwdForm" method="POST" action="">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">New Password</label>
                    <input type="password" name="password" required minlength="8" placeholder="Min 8 characters" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required minlength="8" placeholder="Repeat new password" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 14px;">
                    <button type="button" onclick="closeResetPasswordModal()" class="sa-header-btn">Cancel</button>
                    <button type="submit" class="sa-header-btn" style="background: #C8960C; color: #fff; border: none; font-weight: 700;">Update Password</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCreateUserModal() {
        document.getElementById('createUserModal').classList.add('active');
    }
    function closeCreateUserModal() {
        document.getElementById('createUserModal').classList.remove('active');
    }

    function handleRoleChange(role) {
        document.getElementById('districtScopeField').style.display = (role === 'district_admin') ? 'block' : 'none';
        document.getElementById('schoolScopeField').style.display = (role === 'school_admin') ? 'block' : 'none';
    }

    function openResetPasswordModal(userId, username) {
        document.getElementById('resetTargetUsername').innerText = username;
        document.getElementById('resetPwdForm').action = `/superadmin/users/${userId}/reset-password`;
        document.getElementById('resetPwdModal').classList.add('active');
    }
    function closeResetPasswordModal() {
        document.getElementById('resetPwdModal').classList.remove('active');
    }
</script>
@endpush
@endsection
