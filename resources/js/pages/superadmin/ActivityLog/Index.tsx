import { router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppInput } from '@/components/shared/AppInput';
import type { ActivityLog, Paginated } from '@/types';

interface ActivityLogIndexProps {
  activities: Paginated<ActivityLog & { causer?: { name: string; username: string } }>;
  filters: Record<string, string | undefined>;
}

export default function ActivityLogIndex({ activities, filters }: ActivityLogIndexProps) {
  function applyFilters(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    router.get(route('superadmin.activity-log'), Object.fromEntries(form), { preserveState: true });
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">System & Audit / Audit Trail</span>}>
      <PageHeader
        title="Audit Trail"
        subtitle="Every meaningful administrative action across the entire system"
      />

      <form onSubmit={applyFilters} className="card mb-6">
        <div className="card-body grid grid-cols-1 md:grid-cols-4 gap-3">
          <AppInput name="search" defaultValue={filters.search ?? ''} placeholder="Search description" />
          <AppInput name="log_name" defaultValue={filters.log_name ?? ''} placeholder="Log category" />
          <AppInput name="date_from" type="date" defaultValue={filters.date_from ?? ''} />
          <AppInput name="date_to" type="date" defaultValue={filters.date_to ?? ''} />
          <button type="submit" className="btn btn-secondary md:col-span-4 w-fit">Apply Filters</button>
        </div>
      </form>

      <div className="card">
        <div className="card-body overflow-x-auto">
          <table className="data-table w-full">
            <thead>
              <tr>
                <th>When</th>
                <th>Action</th>
                <th>By</th>
                <th>Subject</th>
              </tr>
            </thead>
            <tbody>
              {activities.data.map((item) => (
                <tr key={item.id}>
                  <td className="text-xs whitespace-nowrap">{item.created_at}</td>
                  <td>{item.description}</td>
                  <td>{item.user ?? item.causer?.name ?? 'System'}</td>
                  <td className="text-xs text-gray-500">{item.subject_type}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
