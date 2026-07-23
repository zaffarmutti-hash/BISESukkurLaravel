import { Link } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import type { District, School } from '@/types';

interface ShowProps {
  school: School & { district?: District; admin_user?: { name: string; email: string } };
}

export default function SchoolsShow({ school }: ShowProps) {
  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Schools & Users / School Detail</span>}>
      <PageHeader
        title={school.name}
        subtitle={`${school.code} · ${school.district?.name ?? ''}`}
        actions={
          <Link href={route('superadmin.schools.edit', school.id)} className="btn btn-primary">
            Edit Configuration
          </Link>
        }
      />

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="card">
          <div className="card-header"><h2 className="card-title">School Profile</h2></div>
          <div className="card-body space-y-2 text-sm">
            <p><span className="text-gray-500">Status:</span> <StatusBadge label={school.is_active ? 'Active' : 'Suspended'} variant={school.is_active ? 'success' : 'danger'} /></p>
            <p><span className="text-gray-500">Type:</span> {school.type}</p>
            <p><span className="text-gray-500">Principal:</span> {school.principal_name ?? '—'}</p>
            <p><span className="text-gray-500">Phone:</span> {school.phone ?? '—'}</p>
            <p><span className="text-gray-500">Email:</span> {school.email ?? '—'}</p>
          </div>
        </div>
        <div className="card">
          <div className="card-header"><h2 className="card-title">School Admin Account</h2></div>
          <div className="card-body text-sm">
            <p className="font-medium">{school.admin_user?.name ?? 'No admin assigned'}</p>
            <p className="text-gray-500">{school.admin_user?.email}</p>
          </div>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
