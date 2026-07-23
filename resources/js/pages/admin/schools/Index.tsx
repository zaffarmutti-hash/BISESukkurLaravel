import { Link, router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import type { District, Paginated, School } from '@/types';

interface SchoolsIndexProps {
  schools: Paginated<School & { district?: District; admin_user?: { name: string; email: string } }>;
  districts: District[];
  filters: Record<string, string | boolean | undefined>;
}

export default function SchoolsIndex({ schools, districts, filters }: SchoolsIndexProps) {
  function applyFilters(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    router.get(route('superadmin.schools'), Object.fromEntries(form), { preserveState: true });
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Schools & Users / All Schools</span>}>
      <PageHeader
        title="Registered Schools"
        subtitle="Master list across all five districts — filter, configure, or suspend"
        actions={
          <Link href={route('superadmin.schools.create')} className="btn btn-primary">
            Register New School
          </Link>
        }
      />

      <form onSubmit={applyFilters} className="card mb-6">
        <div className="card-body grid grid-cols-1 md:grid-cols-5 gap-3">
          <AppInput name="search" defaultValue={String(filters.search ?? '')} placeholder="Search name or code" />
          <AppSelect name="district_id" defaultValue={String(filters.district_id ?? '')} placeholder="">
            <option value="">All districts</option>
            {districts.map((d) => (
              <option key={d.id} value={d.id}>{d.name}</option>
            ))}
          </AppSelect>
          <AppSelect name="type" defaultValue={String(filters.type ?? '')} placeholder="">
            <option value="">All types</option>
            <option value="school">School</option>
            <option value="college">College</option>
          </AppSelect>
          <AppSelect name="is_active" defaultValue={String(filters.is_active ?? '')} placeholder="">
            <option value="">Any status</option>
            <option value="1">Active</option>
            <option value="0">Suspended</option>
          </AppSelect>
          <button type="submit" className="btn btn-secondary">Filter</button>
        </div>
      </form>

      <div className="card">
        <div className="card-body overflow-x-auto">
          <table className="data-table w-full">
            <thead>
              <tr>
                <th>Code</th>
                <th>Name</th>
                <th>District</th>
                <th>Type</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {schools.data.map((school) => (
                <tr key={school.id}>
                  <td className="font-mono text-xs">{school.code}</td>
                  <td className="font-medium">{school.name}</td>
                  <td>{school.district?.name}</td>
                  <td className="capitalize">{school.type}</td>
                  <td>
                    <StatusBadge label={school.is_active ? 'Active' : 'Suspended'} variant={school.is_active ? 'success' : 'danger'} />
                  </td>
                  <td className="space-x-2 whitespace-nowrap">
                    <Link href={route('superadmin.schools.show', school.id)} className="text-amber-600 text-xs font-semibold">View</Link>
                    <Link href={route('superadmin.schools.edit', school.id)} className="text-blue-600 text-xs font-semibold">Edit</Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
