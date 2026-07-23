import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppButton } from '@/components/shared/AppButton';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import { ConfirmDialog } from '@/components/shared/ConfirmDialog';
import { usePermissions } from '@/hooks/usePermissions';
import type { AcademicYear, ExamCenter } from '@/types';

interface AllotmentHistoryItem {
  id: number;
  description: string;
  user: string;
  created_at: string;
  properties: {
    class_level: string;
    start_sequence: number;
    count: number;
    year: string;
    exam_center_id?: number | null;
  };
}

interface SeatAllotmentProps {
  stats: {
    total_candidates: number;
    allotted: number;
    pending: number;
  };
  centers: ExamCenter[];
  history: AllotmentHistoryItem[];
  activeYear?: AcademicYear | null;
}

export default function SeatAllotment({ stats, centers, history, activeYear }: SeatAllotmentProps) {
  usePermissions();
  const [showConfirm, setShowConfirm] = useState(false);

  const form = useForm({
    class_level: 'ssc_part1',
    start_sequence: '500001',
    exam_center_id: '',
  });

  function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setShowConfirm(true);
  }

  function handleConfirm() {
    form.post(route('superadmin.examination.seat-allotment.run'), {
      onSuccess: () => {
        setShowConfirm(false);
      }
    });
  }

  const classNames: Record<string, string> = {
    ssc_part1: 'SSC Part I (9th)',
    ssc_part2: 'SSC Part II (10th)',
    hsc_part1: 'HSC Part I (11th)',
    hsc_part2: 'HSC Part II (12th)',
  };

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Examination Hub / Seat Allotment</span>}>
      <PageHeader
        title="Candidate Seat Allotment"
        subtitle="Generate unique, sequential roll/seat numbers for confirmed examination candidates"
      />

      {activeYear ? (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
          {/* Stats Cards */}
          <div className="card bg-gradient-to-br from-indigo-900 to-indigo-800 text-white">
            <div className="card-body">
              <span className="text-xs font-semibold text-indigo-200 uppercase tracking-wider">Total Candidates Enrolled</span>
              <div className="text-3xl font-extrabold mt-1">{stats.total_candidates}</div>
              <p className="text-xs text-indigo-300 mt-2">Confirmed exam forms in {activeYear.label}</p>
            </div>
          </div>
          <div className="card bg-gradient-to-br from-emerald-800 to-emerald-700 text-white">
            <div className="card-body">
              <span className="text-xs font-semibold text-emerald-200 uppercase tracking-wider">Seat Numbers Assigned</span>
              <div className="text-3xl font-extrabold mt-1">{stats.allotted}</div>
              <p className="text-xs text-emerald-300 mt-2">Ready for admission slip printing</p>
            </div>
          </div>
          <div className="card bg-gradient-to-br from-amber-800 to-amber-700 text-white">
            <div className="card-body">
              <span className="text-xs font-semibold text-amber-200 uppercase tracking-wider">Pending Allotment</span>
              <div className="text-3xl font-extrabold mt-1">{stats.pending}</div>
              <p className="text-xs text-amber-300 mt-2">Awaiting seat number assignment</p>
            </div>
          </div>
        </div>
      ) : (
        <div className="alert alert-warning mb-6">
          Active operational year is missing. Please set an active year under Academic Year settings.
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left Column: Allotment Action form */}
        <div className="lg:col-span-1">
          <div className="card">
            <div className="card-header"><h2 className="card-title">Run Allotment Batch</h2></div>
            <form onSubmit={onSubmit} className="card-body space-y-4">
              <AppSelect
                label="Class Level"
                value={form.data.class_level}
                onChange={(e) => form.setData('class_level', e.target.value)}
                required
              >
                <option value="ssc_part1">SSC Part I (9th)</option>
                <option value="ssc_part2">SSC Part II (10th)</option>
                <option value="hsc_part1">HSC Part I (11th)</option>
                <option value="hsc_part2">HSC Part II (12th)</option>
              </AppSelect>

              <AppInput
                label="Start Seat Number Sequence"
                type="number"
                min="100000"
                value={form.data.start_sequence}
                onChange={(e) => form.setData('start_sequence', e.target.value)}
                required
              />

              <AppSelect
                label="Filter by Exam Center (Optional)"
                value={form.data.exam_center_id}
                onChange={(e) => form.setData('exam_center_id', e.target.value)}
              >
                <option value="">All Centers</option>
                {centers.map((c) => (
                  <option key={c.id} value={c.id}>{c.name} ({c.code})</option>
                ))}
              </AppSelect>

              <AppButton
                type="submit"
                variant="primary"
                className="w-full justify-center"
                disabled={!activeYear || stats.pending === 0 || form.processing}
              >
                Execute Allotment All
              </AppButton>
            </form>
          </div>
        </div>

        {/* Right Column: History table */}
        <div className="lg:col-span-2">
          <div className="card">
            <div className="card-header"><h2 className="card-title">Allotment Batch History</h2></div>
            <div className="card-body overflow-x-auto p-0">
              <table className="app-table w-full text-sm">
                <thead>
                  <tr>
                    <th className="pl-6">Executed At</th>
                    <th>Class Level</th>
                    <th>Allotted Count</th>
                    <th>Start Sequence</th>
                    <th className="pr-6">Triggered By</th>
                  </tr>
                </thead>
                <tbody>
                  {history.map((h) => (
                    <tr key={h.id}>
                      <td className="pl-6 text-xs whitespace-nowrap text-slate-500">{h.created_at}</td>
                      <td>
                        <span className="badge badge-neutral text-xs">
                          {classNames[h.properties.class_level] || h.properties.class_level.replace(/_/g, ' ').toUpperCase()}
                        </span>
                      </td>
                      <td className="font-semibold text-slate-800">{h.properties.count} Candidates</td>
                      <td className="font-mono text-xs">{h.properties.start_sequence}</td>
                      <td className="pr-6 text-slate-600">{h.user}</td>
                    </tr>
                  ))}
                  {history.length === 0 && (
                    <tr>
                      <td colSpan={5} className="text-center py-8 text-slate-400 text-sm">
                        No previous seat allotment records found.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <ConfirmDialog
        open={showConfirm}
        title="Confirm Seat Allotment"
        message={
          <div className="space-y-2">
            <p>You are about to sequential allotment rolls for <strong>{form.data.class_level.replace(/_/g, ' ').toUpperCase()}</strong> candidates.</p>
            <p className="text-xs text-amber-700 font-medium">
              Important: This is a high-volume database update. Please double check that the starting seat number does not overlap with any previously issued sequence range.
            </p>
          </div>
        }
        loading={form.processing}
        onConfirm={handleConfirm}
        onCancel={() => setShowConfirm(false)}
      />
    </SuperAdminLayout>
  );
}
