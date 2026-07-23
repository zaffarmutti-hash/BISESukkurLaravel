import { router, useForm } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { ConfirmDialog } from '@/components/shared/ConfirmDialog';
import { AppModal } from '@/components/shared/AppModal';
import { AppInput } from '@/components/shared/AppInput';
import { AppButton } from '@/components/shared/AppButton';
import { useState } from 'react';
import { usePermissions } from '@/hooks/usePermissions';
import type { AcademicYear } from '@/types';

interface YearActions {
  store?: string;
  activate?: string;
  enrollment_open?: string;
  enrollment_close?: string;
  examination_open?: string;
  examination_close?: string;
}

interface YearsIndexProps {
  years: (AcademicYear & {
    year_start?: number;
    enrollment_window_open?: boolean;
    examination_window_open?: boolean;
  })[];
  actions: YearActions;
}

export default function YearsIndex({ years, actions }: YearsIndexProps) {
  const { can } = usePermissions();
  const [pendingAction, setPendingAction] = useState<{ url: string; label: string } | null>(null);
  const [showCreateModal, setShowCreateModal] = useState(false);

  const form = useForm({
    label: '',
    year_start: '',
    year_end: '',
    enrollment_open_date: '',
    enrollment_close_date: '',
    examination_open_date: '',
    examination_close_date: '',
  });

  function runAction() {
    if (!pendingAction) return;
    router.post(pendingAction.url, {}, { onSuccess: () => setPendingAction(null) });
  }

  function openCreateModal() {
    const currentYear = new Date().getFullYear();
    form.setData({
      label: `${currentYear}-${currentYear + 1}`,
      year_start: String(currentYear),
      year_end: String(currentYear + 1),
      enrollment_open_date: `${currentYear}-08-01`,
      enrollment_close_date: `${currentYear}-09-30`,
      examination_open_date: `${currentYear + 1}-02-01`,
      examination_close_date: `${currentYear + 1}-03-31`,
    });
    form.clearErrors();
    setShowCreateModal(true);
  }

  function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!actions.store) return;
    form.post(actions.store, {
      onSuccess: () => {
        setShowCreateModal(false);
        form.reset();
      }
    });
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Academic Year Control / Manage Years</span>}>
      <PageHeader
        title="Manage Academic Years"
        subtitle="Create operational years, inspect window limits, and choose active year"
        actions={
          can('academicyear.create_next') && actions.store && (
            <AppButton variant="primary" onClick={openCreateModal}>
              + Add Academic Year
            </AppButton>
          )
        }
      />

      <div className="card">
        <div className="card-body overflow-x-auto p-0">
          <table className="app-table w-full">
            <thead>
              <tr>
                <th className="pl-6">Year Span</th>
                <th>Status</th>
                <th>Enrollment window</th>
                <th>Exam window</th>
                <th className="pr-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {years.map((year) => (
                <tr key={year.id}>
                  <td className="pl-6 font-bold text-slate-800">{year.label}</td>
                  <td>
                    <StatusBadge label={year.is_active ? 'Active' : 'Inactive'} variant={year.is_active ? 'success' : 'neutral'} />
                  </td>
                  <td>
                    <div className="flex flex-col">
                      <StatusBadge label={year.enrollment_window_open ? 'Open' : 'Closed'} variant={year.enrollment_window_open ? 'success' : 'neutral'} />
                      {year.enrollment_open_date && (
                        <span className="text-[10px] text-slate-400 mt-1">
                          {new Date(year.enrollment_open_date).toLocaleDateString()} to {new Date(year.enrollment_close_date ?? year.enrollment_open_date).toLocaleDateString()}
                        </span>
                      )}
                    </div>
                  </td>
                  <td>
                    <div className="flex flex-col">
                      <StatusBadge label={year.examination_window_open ? 'Open' : 'Closed'} variant={year.examination_window_open ? 'success' : 'neutral'} />
                      {year.examination_open_date && (
                        <span className="text-[10px] text-slate-400 mt-1">
                          {new Date(year.examination_open_date).toLocaleDateString()} to {new Date(year.examination_close_date ?? year.examination_open_date).toLocaleDateString()}
                        </span>
                      )}
                    </div>
                  </td>
                  <td className="pr-6 text-right whitespace-nowrap text-xs">
                    {!year.is_active && actions.activate && can('academicyear.create_next') && (
                      <AppButton
                        variant="secondary"
                        size="sm"
                        onClick={() => setPendingAction({ url: actions.activate!.replace('__ID__', String(year.id)), label: `Activate ${year.label}` })}
                      >
                        Set Active
                      </AppButton>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Create Modal */}
      <AppModal open={showCreateModal} onClose={() => setShowCreateModal(false)} title="Register Academic Year">
        <form onSubmit={onSubmit} className="px-6 py-4 space-y-4">
          <div className="grid grid-cols-3 gap-4">
            <AppInput
              label="Start Year"
              type="number"
              min="2000"
              max="2100"
              value={form.data.year_start}
              onChange={(e) => form.setData('year_start', e.target.value)}
              required
            />
            <AppInput
              label="End Year"
              type="number"
              min="2000"
              max="2100"
              value={form.data.year_end}
              onChange={(e) => form.setData('year_end', e.target.value)}
              required
            />
            <AppInput
              label="Display Label"
              placeholder="e.g. 2026-2027"
              value={form.data.label}
              onChange={(e) => form.setData('label', e.target.value)}
              required
            />
          </div>

          <div className="card bg-slate-50 p-4 border border-slate-100 space-y-4">
            <div className="text-xs font-bold text-slate-500 uppercase tracking-wide">Enrollment Window Schedule</div>
            <div className="grid grid-cols-2 gap-4">
              <AppInput
                label="Opening Date"
                type="date"
                value={form.data.enrollment_open_date}
                onChange={(e) => form.setData('enrollment_open_date', e.target.value)}
                required
              />
              <AppInput
                label="Closing Date"
                type="date"
                value={form.data.enrollment_close_date}
                onChange={(e) => form.setData('enrollment_close_date', e.target.value)}
                required
              />
            </div>
          </div>

          <div className="card bg-slate-50 p-4 border border-slate-100 space-y-4">
            <div className="text-xs font-bold text-slate-500 uppercase tracking-wide">Examination Window Schedule</div>
            <div className="grid grid-cols-2 gap-4">
              <AppInput
                label="Opening Date"
                type="date"
                value={form.data.examination_open_date}
                onChange={(e) => form.setData('examination_open_date', e.target.value)}
                required
              />
              <AppInput
                label="Closing Date"
                type="date"
                value={form.data.examination_close_date}
                onChange={(e) => form.setData('examination_close_date', e.target.value)}
                required
              />
            </div>
          </div>

          {Object.keys(form.errors).length > 0 && (
            <div className="bg-red-50 text-red-700 text-xs p-3 rounded border border-red-200">
              {Object.values(form.errors).map((err, idx) => (
                <div key={idx}>{err}</div>
              ))}
            </div>
          )}

          <div className="flex justify-end gap-2 pt-2 border-t border-slate-100">
            <AppButton variant="secondary" onClick={() => setShowCreateModal(false)} disabled={form.processing}>
              Cancel
            </AppButton>
            <AppButton type="submit" variant="primary" loading={form.processing}>
              Register Year
            </AppButton>
          </div>
        </form>
      </AppModal>

      <ConfirmDialog
        open={!!pendingAction}
        title="Confirm Activation"
        message={`This will set ${pendingAction?.label} as the active year system-wide. Every school tenant will operate under this year immediately. Continue?`}
        onConfirm={runAction}
        onCancel={() => setPendingAction(null)}
      />
    </SuperAdminLayout>
  );
}
