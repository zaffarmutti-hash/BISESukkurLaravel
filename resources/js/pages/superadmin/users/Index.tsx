import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { ConfirmDialog } from '@/components/shared/ConfirmDialog';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import type { District, School, SelectOption, User } from '@/types';

interface PaginatedUsers {
  data: User[];
  links: { url: string | null; label: string; active: boolean }[];
  meta?: { total: number };
}

interface UsersIndexProps {
  users: PaginatedUsers;
  filters: Record<string, string | boolean | undefined>;
  districts: District[];
  schools: School[];
  roles: SelectOption[];
}

export default function UsersIndex({ users, filters, districts, schools: _schools, roles }: UsersIndexProps) {
  const [resetUser, setResetUser] = useState<User | null>(null);
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');

  function applyFilters(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    router.get(route('superadmin.users.index'), Object.fromEntries(form), { preserveState: true });
  }

  function toggleActive(user: User) {
    if (!confirm(`${user.is_active ? 'Deactivate' : 'Activate'} account for ${user.name}?`)) return;
    router.post(route('superadmin.users.toggle-active', user.id));
  }

  function submitReset() {
    if (!resetUser) return;
    router.post(route('superadmin.users.reset-password', resetUser.id), {
      password: newPassword,
      password_confirmation: confirmPassword,
    }, {
      onSuccess: () => {
        setResetUser(null);
        setNewPassword('');
        setConfirmPassword('');
      },
    });
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Schools & Users / User Accounts</span>}>
      <PageHeader
        title="User Accounts"
        subtitle="Manage every account across all roles — create, reset, or deactivate"
        actions={
          <Link href={route('superadmin.users.create')} className="btn btn-primary">
            Create Account
          </Link>
        }
      />

      <form onSubmit={applyFilters} className="card mb-6">
        <div className="card-body grid grid-cols-1 md:grid-cols-5 gap-3">
          <AppInput name="search" defaultValue={String(filters.search ?? '')} placeholder="Search name or username" />
          <AppSelect name="role" defaultValue={String(filters.role ?? '')}>
            <option value="">All roles</option>
            {roles.map((r) => (
              <option key={r.value} value={r.value}>{r.label}</option>
            ))}
          </AppSelect>
          <AppSelect name="district_id" defaultValue={String(filters.district_id ?? '')}>
            <option value="">All districts</option>
            {districts.map((d) => (
              <option key={d.id} value={d.id}>{d.name}</option>
            ))}
          </AppSelect>
          <AppSelect name="is_active" defaultValue={String(filters.is_active ?? '')}>
            <option value="">Any status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </AppSelect>
          <button type="submit" className="btn btn-secondary">Filter</button>
        </div>
      </form>

      <div className="card">
        <div className="card-body overflow-x-auto">
          <table className="data-table w-full">
            <thead>
              <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {users.data.map((user) => (
                <tr key={user.id}>
                  <td className="font-medium">{user.name}</td>
                  <td>{user.username}</td>
                  <td className="capitalize">{user.role.replace('_', ' ')}</td>
                  <td>
                    <StatusBadge label={user.is_active ? 'Active' : 'Inactive'} variant={user.is_active ? 'success' : 'neutral'} />
                  </td>
                  <td className="space-x-2 whitespace-nowrap">
                    <Link href={route('superadmin.users.edit', user.id)} className="text-amber-600 text-xs font-semibold">Edit</Link>
                    <button type="button" onClick={() => setResetUser(user)} className="text-blue-600 text-xs font-semibold">Reset Password</button>
                    <button type="button" onClick={() => toggleActive(user)} className="text-red-600 text-xs font-semibold">
                      {user.is_active ? 'Deactivate' : 'Activate'}
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      <ConfirmDialog
        open={!!resetUser}
        title="Reset Password"
        message={
          <div className="space-y-3">
            <p>Reset password for <strong>{resetUser?.name}</strong>. They will be required to change it on next login.</p>
            <AppInput type="password" value={newPassword} onChange={(e) => setNewPassword(e.target.value)} placeholder="New password" />
            <AppInput type="password" value={confirmPassword} onChange={(e) => setConfirmPassword(e.target.value)} placeholder="Confirm password" />
          </div>
        }
        confirmLabel="Reset Password"
        onConfirm={submitReset}
        onCancel={() => setResetUser(null)}
      />
    </SuperAdminLayout>
  );
}
