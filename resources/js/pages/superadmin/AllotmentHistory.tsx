import { useState } from 'react';
import { router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppButton } from '@/components/shared/AppButton';
import { ConfirmDialog } from '@/components/shared/ConfirmDialog';
import { usePermissions } from '@/hooks/usePermissions';
import type { AcademicYear } from '@/types';

interface AllotmentHistoryItem {
  id: number;
  description: string;
  user: string;
  created_at: string;
  properties: {
    count: number;
    year: string;
  };
}

interface AllotmentHistoryProps {
  stats: {
    total_students: number;
    allotted: number;
    pending: number;
  };
  history: AllotmentHistoryItem[];
  activeYear?: AcademicYear | null;
}

export default function AllotmentHistory({ stats, history, activeYear }: AllotmentHistoryProps) {
  const { can } = usePermissions();
  const [showConfirm, setShowConfirm] = useState(false);
  const [processing, setProcessing] = useState(false);

  function handleRunAllotment() {
    setProcessing(true);
    router.post(route('superadmin.allotment.run'), {}, {
      onSuccess: () => {
        setProcessing(false);
        setShowConfirm(false);
      },
      onError: () => setProcessing(false)
    });
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Enrollment Hub / Allotment History</span>}>
      <PageHeader
        title="Enrollment Allotment Runner"
        subtitle="Issue official BISE enrollment numbers for candidates whose fee payments have been verified"
      />

      {activeYear ? (
        <div className="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
          <div className="card bg-slate-50 border-slate-200">
            <div className="card-body">
              <span className="text-xs font-semibold text-slate-500 uppercase">Total Operational Candidates</span>
              <div className="text-2xl font-bold text-slate-800 mt-1">{stats.total_students}</div>
              <p className="text-xs text-slate-400 mt-1">Students in academic year {activeYear.label}</p>
            </div>
          </div>
          <div className="card bg-slate-50 border-slate-200">
            <div className="card-body">
              <span className="text-xs font-semibold text-slate-500 uppercase">Enrollment Issued</span>
              <div className="text-2xl font-bold text-slate-800 mt-1">{stats.allotted}</div>
              <p className="text-xs text-slate-400 mt-1">Candidates issued official IDs</p>
            </div>
          </div>
          <div className="card bg-slate-50 border-slate-200">
            <div className="card-body">
              <span className="text-xs font-semibold text-slate-500 uppercase">Pending Verification / Issue</span>
              <div className="text-2xl font-bold text-slate-800 mt-1">{stats.pending}</div>
              <p className="text-xs text-slate-400 mt-1">Stuck paid candidates awaiting run</p>
            </div>
          </div>
          <div className="card bg-amber-50 border-amber-200 flex flex-col justify-center p-4">
            <AppButton
              variant="primary"
              className="w-full justify-center"
              disabled={stats.pending === 0 || processing || !can('allotment.history')}
              onClick={() => setShowConfirm(true)}
            >
              Run Allotment Batch
            </AppButton>
          </div>
        </div>
      ) : (
        <div className="alert alert-warning mb-6">
          Active operational year is missing. Please configure it under Academic Years.
        </div>
      )}

      <div className="card">
        <div className="card-header"><h2 className="card-title">Allotment Run Logs</h2></div>
        <div className="card-body overflow-x-auto p-0">
          <table className="app-table w-full text-sm">
            <thead>
              <tr>
                <th className="pl-6">Executed At</th>
                <th>Academic Year</th>
                <th>Allotted Count</th>
                <th>Action Message</th>
                <th className="pr-6">Triggered By</th>
              </tr>
            </thead>
            <tbody>
              {history.map((h) => (
                <tr key={h.id}>
                  <td className="pl-6 text-xs whitespace-nowrap text-slate-500">{h.created_at}</td>
                  <td className="font-semibold text-slate-800">{h.properties.year}</td>
                  <td className="font-bold text-slate-900">{h.properties.count} Candidates</td>
                  <td>{h.description}</td>
                  <td className="pr-6 text-slate-600 font-medium">{h.user}</td>
                </tr>
              ))}
              {history.length === 0 && (
                <tr>
                  <td colSpan={5} className="text-center py-8 text-slate-400 text-sm">
                    No previous allotment history logs.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <ConfirmDialog
        open={showConfirm}
        title="Confirm Allotment Execution"
        message={
          <div className="space-y-2">
            <p>You are about to issue unique enrollment numbers for <strong>{stats.pending}</strong> candidates.</p>
            <p className="text-xs text-indigo-700">
              Each student will receive a unique sequential number retrieved from PostgreSQL sequence generator. Their operational status will advance to "enrolled" instantly.
            </p>
          </div>
        }
        loading={processing}
        onConfirm={handleRunAllotment}
        onCancel={() => setShowConfirm(false)}
      />
    </SuperAdminLayout>
  );
}
