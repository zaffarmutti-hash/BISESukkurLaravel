import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import type { SystemHealth } from '@/types';

interface HealthProps {
  health: SystemHealth;
}

export default function Health({ health }: HealthProps) {
  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Overview / System Health</span>}>
      <PageHeader
        title="System Health"
        subtitle="Technical state of the platform — jobs, storage, and connectivity"
      />

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div className="card">
          <div className="card-body">
            <p className="text-xs font-semibold uppercase text-gray-500 mb-2">Overall</p>
            <StatusBadge
              label={health.healthy ? 'Healthy' : 'Issues Detected'}
              variant={health.healthy ? 'success' : 'danger'}
              pulse={!health.healthy}
            />
          </div>
        </div>
        <div className="card">
          <div className="card-body">
            <p className="text-xs font-semibold uppercase text-gray-500 mb-2">Database</p>
            <StatusBadge label={health.database ? 'Connected' : 'Failed'} variant={health.database ? 'success' : 'danger'} />
          </div>
        </div>
        <div className="card">
          <div className="card-body">
            <p className="text-xs font-semibold uppercase text-gray-500 mb-2">Storage</p>
            <StatusBadge label={health.disk ? 'Adequate' : 'Low Space'} variant={health.disk ? 'success' : 'warning'} />
            {health.disk_free != null && (
              <p className="text-xs text-gray-500 mt-2">{health.disk_free} GB free</p>
            )}
          </div>
        </div>
      </div>

      {!health.healthy && health.issues && health.issues.length > 0 && (
        <div className="card">
          <div className="card-header">
            <h2 className="card-title text-red-700">Active Issues</h2>
          </div>
          <div className="card-body">
            <ul className="list-disc pl-5 space-y-1 text-sm text-gray-700">
              {health.issues.map((issue) => (
                <li key={issue}>{issue}</li>
              ))}
            </ul>
          </div>
        </div>
      )}
    </SuperAdminLayout>
  );
}
