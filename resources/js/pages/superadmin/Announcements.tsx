import { useForm } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import { AppButton } from '@/components/shared/AppButton';
import type { Announcement, District, Paginated, School } from '@/types';

interface AnnouncementsProps {
  announcements: Paginated<Announcement & { sender?: { name: string }; district?: District; school?: School }>;
  districts: District[];
  schools: School[];
}

export default function Announcements({ announcements, districts, schools }: AnnouncementsProps) {
  const form = useForm({
    title: '',
    body: '',
    type: 'info' as 'info' | 'warning' | 'success',
    target_scope: 'all' as 'all' | 'district' | 'school',
    district_id: '',
    school_id: '',
  });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    form.post(route('superadmin.announcements.store'), { onSuccess: () => form.reset() });
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">System & Audit / Announcements</span>}>
      <PageHeader title="Announcements" subtitle="Message schools system-wide, by district, or individually" />

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <form onSubmit={submit} className="card">
          <div className="card-header"><h2 className="card-title">Compose Message</h2></div>
          <div className="card-body space-y-4">
            <AppInput label="Title" required value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} error={form.errors.title} />
            <label className="block text-sm font-medium text-gray-700">Message</label>
            <textarea className="form-input w-full min-h-32" required value={form.data.body} onChange={(e) => form.setData('body', e.target.value)} />
            <AppSelect label="Type" value={form.data.type} onChange={(e) => form.setData('type', e.target.value as typeof form.data.type)} placeholder="">
              <option value="info">Information</option>
              <option value="warning">Warning</option>
              <option value="success">Success</option>
            </AppSelect>
            <AppSelect label="Send To" value={form.data.target_scope} onChange={(e) => form.setData('target_scope', e.target.value as typeof form.data.target_scope)} placeholder="">
              <option value="all">All Schools</option>
              <option value="district">One District</option>
              <option value="school">One School</option>
            </AppSelect>
            {form.data.target_scope === 'district' && (
              <AppSelect label="District" value={form.data.district_id} onChange={(e) => form.setData('district_id', e.target.value)} placeholder="">
                {districts.map((d) => (
                  <option key={d.id} value={d.id}>{d.name}</option>
                ))}
              </AppSelect>
            )}
            {form.data.target_scope === 'school' && (
              <AppSelect label="School" value={form.data.school_id} onChange={(e) => form.setData('school_id', e.target.value)} placeholder="">
                {schools.map((s) => (
                  <option key={s.id} value={s.id}>{s.name}</option>
                ))}
              </AppSelect>
            )}
            <AppButton type="submit" loading={form.processing}>Send Announcement</AppButton>
          </div>
        </form>

        <div className="card">
          <div className="card-header"><h2 className="card-title">Sent History</h2></div>
          <div className="card-body">
            <ul className="divide-y divide-gray-100 text-sm">
              {announcements.data.map((a) => (
                <li key={a.id} className="py-3">
                  <p className="font-medium">{a.title}</p>
                  <p className="text-gray-500 text-xs mt-1">
                    {a.sender?.name} · {a.created_at} · {a.target_audience ?? 'All Schools'}
                  </p>
                </li>
              ))}
            </ul>
          </div>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
