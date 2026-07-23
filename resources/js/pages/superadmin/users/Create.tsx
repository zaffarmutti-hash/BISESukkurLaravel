import { Link, useForm } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import { AppButton } from '@/components/shared/AppButton';
import type { District, School, SelectOption } from '@/types';

interface CreateProps {
  districts: District[];
  schools: School[];
  roles: SelectOption[];
}

export default function UsersCreate({ districts, schools, roles }: CreateProps) {
  const form = useForm({
    username: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'school_admin',
    district_id: '',
    school_id: '',
    is_active: true,
  });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    form.post(route('superadmin.users.store'));
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Schools & Users / Create Account</span>}>
      <PageHeader title="Create User Account" subtitle="Add a new board, district, or school administrator" />

      <form onSubmit={submit} className="card max-w-2xl">
        <div className="card-body space-y-4">
          <AppInput label="Username" required value={form.data.username} onChange={(e) => form.setData('username', e.target.value)} error={form.errors.username} />
          <AppInput label="Full Name" required value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} error={form.errors.name} />
          <AppInput label="Email" type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} error={form.errors.email} />
          <AppInput label="Password" type="password" required value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} error={form.errors.password} />
          <AppInput label="Confirm Password" type="password" required value={form.data.password_confirmation} onChange={(e) => form.setData('password_confirmation', e.target.value)} />

          <AppSelect label="Role" required value={form.data.role} onChange={(e) => form.setData('role', e.target.value)} options={roles} placeholder="" />

          {form.data.role === 'district_admin' && (
            <AppSelect label="District" required value={String(form.data.district_id)} onChange={(e) => form.setData('district_id', e.target.value)} placeholder="">
              {districts.map((d) => (
                <option key={d.id} value={d.id}>{d.name}</option>
              ))}
            </AppSelect>
          )}

          {form.data.role === 'school_admin' && (
            <AppSelect label="School" required value={String(form.data.school_id)} onChange={(e) => form.setData('school_id', e.target.value)} placeholder="">
              {schools.map((s) => (
                <option key={s.id} value={s.id}>{s.name} ({s.code})</option>
              ))}
            </AppSelect>
          )}

          <div className="flex gap-2 pt-2">
            <AppButton type="submit" loading={form.processing}>Create Account</AppButton>
            <Link href={route('superadmin.users.index')} className="btn btn-secondary">Cancel</Link>
          </div>
        </div>
      </form>
    </SuperAdminLayout>
  );
}
