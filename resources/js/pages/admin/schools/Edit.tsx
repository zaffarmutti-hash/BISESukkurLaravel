import { Link, useForm } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';

import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import { AppButton } from '@/components/shared/AppButton';
import type { District, School } from '@/types';

interface EditProps {
  school: School & { district?: District };
  districts: District[];
}

export default function SchoolsEdit({ school, districts }: EditProps) {
  const form = useForm({
    district_id: String(school.district_id),
    name: school.name,
    code: school.code,
    type: (school as School & { type: string }).type ?? 'school',
    gender: (school as School & { gender?: string }).gender ?? 'mixed',
    principal_name: school.principal_name ?? '',
    address: school.address ?? '',
    phone: school.phone ?? '',
    email: school.email ?? '',
    is_active: school.is_active,
  });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    form.put(route('superadmin.schools.update', school.id));
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Schools & Users / Edit School</span>}>
      <PageHeader title={school.name} subtitle={`Code: ${school.code}`} />

      <form onSubmit={submit} className="card max-w-2xl">
        <div className="card-body space-y-4">
          <AppInput label="School Name" required value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
          <AppInput label="School Code" required value={form.data.code} onChange={(e) => form.setData('code', e.target.value)} />
          <AppSelect label="District" value={form.data.district_id} onChange={(e) => form.setData('district_id', e.target.value)} placeholder="">
            {districts.map((d) => (
              <option key={d.id} value={d.id}>{d.name}</option>
            ))}
          </AppSelect>
          <AppSelect label="Type" value={form.data.type} onChange={(e) => form.setData('type', e.target.value as 'government' | 'private' | 'semi-government')} placeholder="">
            <option value="school">School</option>
            <option value="college">College</option>
          </AppSelect>
          <AppSelect label="Gender" value={form.data.gender} onChange={(e) => form.setData('gender', e.target.value)} placeholder="">
            <option value="male">Boys</option>
            <option value="female">Girls</option>
            <option value="mixed">Mixed</option>
          </AppSelect>
          <label className="flex items-center gap-2 text-sm">
            <input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} />
            School is active (uncheck to suspend entirely)
          </label>
          <div className="flex gap-2">
            <AppButton type="submit" loading={form.processing}>Save Changes</AppButton>
            <Link href={route('superadmin.schools')} className="btn btn-secondary">Cancel</Link>
          </div>
        </div>
      </form>
    </SuperAdminLayout>
  );
}
