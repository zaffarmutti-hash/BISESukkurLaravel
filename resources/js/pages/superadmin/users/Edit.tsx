import { Link, useForm } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import { AppButton } from '@/components/shared/AppButton';
import type { District, School, SelectOption, User } from '@/types';

interface EditProps {
  user: User;
  districts: District[];
  schools: School[];
  roles: SelectOption[];
}

export default function UsersEdit({ user, districts, schools, roles }: EditProps) {
  const form = useForm({
    username: user.username,
    name: user.name,
    email: user.email ?? '',
    role: user.role,
    district_id: user.district?.id ? String(user.district.id) : '',
    school_id: user.school?.id ? String(user.school.id) : '',
    is_active: user.is_active ?? true,
    change_password: false,
    password: '',
    password_confirmation: '',
  });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    form.put(route('superadmin.users.update', user.id));
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Schools & Users / Edit Account</span>}>
      <PageHeader title={`Edit: ${user.name}`} subtitle={user.username} />

      <form onSubmit={submit} className="card max-w-2xl">
        <div className="card-body space-y-4">
          <AppInput label="Username" required value={form.data.username} onChange={(e) => form.setData('username', e.target.value)} error={form.errors.username} />
          <AppInput label="Full Name" required value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} error={form.errors.name} />
          <AppInput label="Email" type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} error={form.errors.email} />
          <AppSelect label="Role" required value={form.data.role} onChange={(e) => form.setData('role', e.target.value as 'super_admin' | 'district_admin' | 'school_admin')} options={roles} placeholder="" />

          {form.data.role === 'district_admin' && (
            <AppSelect label="District" value={form.data.district_id} onChange={(e) => form.setData('district_id', e.target.value)} placeholder="">
              {districts.map((d) => (
                <option key={d.id} value={d.id}>{d.name}</option>
              ))}
            </AppSelect>
          )}

          {form.data.role === 'school_admin' && (
            <AppSelect label="School" value={form.data.school_id} onChange={(e) => form.setData('school_id', e.target.value)} placeholder="">
              {schools.map((s) => (
                <option key={s.id} value={s.id}>{s.name} ({s.code})</option>
              ))}
            </AppSelect>
          )}

          <label className="flex items-center gap-2 text-sm">
            <input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} />
            Account is active
          </label>

          <label className="flex items-center gap-2 text-sm">
            <input type="checkbox" checked={form.data.change_password} onChange={(e) => form.setData('change_password', e.target.checked)} />
            Change password
          </label>

          {form.data.change_password && (
            <>
              <AppInput label="New Password" type="password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} error={form.errors.password} />
              <AppInput label="Confirm Password" type="password" value={form.data.password_confirmation} onChange={(e) => form.setData('password_confirmation', e.target.value)} />
            </>
          )}

          <div className="flex gap-2 pt-2">
            <AppButton type="submit" loading={form.processing}>Save Changes</AppButton>
            <Link href={route('superadmin.users.index')} className="btn btn-secondary">Cancel</Link>
          </div>
        </div>
      </form>
    </SuperAdminLayout>
  );
}
