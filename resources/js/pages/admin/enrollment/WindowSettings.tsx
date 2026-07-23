import { router, usePage, useForm } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { ConfirmDialog } from '@/components/shared/ConfirmDialog';
import { AppButton } from '@/components/shared/AppButton';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import { AppModal } from '@/components/shared/AppModal';
import { useState } from 'react';
import type { AcademicYear, WindowOverride } from '@/types';

interface YearActions {
  enrollment_open?: string;
  enrollment_close?: string;
  examination_open?: string;
  examination_close?: string;
  exam_announce?: string;
  exam_unannounce?: string;
  update_window_dates?: string;
}

interface WindowSettingsProps {
  years: (AcademicYear & { 
    enrollment_window_open?: boolean; 
    examination_window_open?: boolean;
    exam_dates_announced?: boolean;
  })[];
  actions: YearActions;
  overrides?: WindowOverride[];
  districts?: { id: number; name: string }[];
  schools?: { id: number; name: string; username: string }[];
}

export default function WindowSettings({
  years,
  actions,
  overrides = [],
  districts = [],
  schools = [],
}: WindowSettingsProps) {
  const { url } = usePage();
  const isExam = url.includes('exam-windows');
  const [pendingAction, setPendingAction] = useState<{ url: string; label: string } | null>(null);
  const [showOverrideModal, setShowOverrideModal] = useState(false);
  const [showDatesModal, setShowDatesModal] = useState(false);
  const activeYear = years.find((y) => y.is_active);

  // Form for editing global dates
  const datesForm = useForm({
    window_type: isExam ? 'examination' : 'enrollment',
    enrollment_open_date: activeYear?.enrollment_open_date ? activeYear.enrollment_open_date.split('T')[0] : '',
    enrollment_close_date: activeYear?.enrollment_close_date ? activeYear.enrollment_close_date.split('T')[0] : '',
    enrollment_grace_end: activeYear?.enrollment_grace_end ? activeYear.enrollment_grace_end.substring(0, 16) : '',
    examination_open_date: activeYear?.examination_open_date ? activeYear.examination_open_date.split('T')[0] : '',
    examination_close_date: activeYear?.examination_close_date ? activeYear.examination_close_date.split('T')[0] : '',
    examination_grace_end: activeYear?.examination_grace_end ? activeYear.examination_grace_end.substring(0, 16) : '',
  });

  // Form for creating override
  const overrideForm = useForm({
    scope_type: 'district',
    scope_id: '',
    window_type: isExam ? 'examination' : 'enrollment',
    normal_start: '',
    normal_end: '',
    grace_end: '',
  });

  function runAction() {
    if (!pendingAction) return;
    router.post(pendingAction.url, {}, { onSuccess: () => setPendingAction(null) });
  }

  function handleSaveDates(e: React.FormEvent) {
    e.preventDefault();
    if (!activeYear || !actions.update_window_dates) return;
    
    const url = actions.update_window_dates.replace('__ID__', String(activeYear.id));
    datesForm.post(url, {
      onSuccess: () => {
        setShowDatesModal(false);
      }
    });
  }

  function handleSaveOverride(e: React.FormEvent) {
    e.preventDefault();
    overrideForm.post(route('superadmin.window-overrides.store'), {
      onSuccess: () => {
        setShowOverrideModal(false);
        overrideForm.reset();
      }
    });
  }

  function handleDeleteOverride(id: number) {
    if (confirm('Are you sure you want to remove this override? Scope will revert to parent configuration.')) {
      router.delete(route('superadmin.window-overrides.destroy', { override: id }));
    }
  }

  // Filter overrides for this window type
  const activeOverrides = overrides.filter((o) => o.window_type === (isExam ? 'examination' : 'enrollment'));

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">{isExam ? 'Examination Hub' : 'Enrollment Hub'} / Window Status</span>}>
      <PageHeader
        title={isExam ? 'Examination Window & Announcement Control' : 'Enrollment Window Control'}
        subtitle="Global window toggles apply to all schools unless a specific override exists"
        actions={
          activeYear && (
            <div className="flex gap-2">
              <AppButton variant="secondary" onClick={() => setShowDatesModal(true)}>
                Edit Window Dates
              </AppButton>
              <AppButton variant="primary" onClick={() => setShowOverrideModal(true)}>
                + Add Override
              </AppButton>
            </div>
          )
        }
      />

      {activeYear ? (
        <div className="space-y-6">
          <div className="card">
            <div className="card-header flex items-center justify-between">
              <h2 className="card-title">Operational Window Toggles</h2>
              <StatusBadge
                label={isExam
                  ? (activeYear.examination_window_open ? 'Exam Window Open' : 'Exam Window Closed')
                  : (activeYear.enrollment_window_open ? 'Enrollment Open' : 'Enrollment Closed')}
                variant={(isExam ? activeYear.examination_window_open : activeYear.enrollment_window_open) ? 'success' : 'neutral'}
              />
            </div>
            <div className="card-body">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                  <p className="text-sm text-slate-500 mb-2">
                    Operational year: <strong>{activeYear.label}</strong>. When open, schools can submit student records or exam applications directly.
                  </p>
                  <div className="flex flex-wrap gap-2">
                    {isExam ? (
                      <>
                        {actions.examination_open && (
                          <AppButton variant="primary" size="sm" onClick={() => setPendingAction({ url: actions.examination_open!.replace('__ID__', String(activeYear.id)), label: 'Open examination window' })}>
                            Open Examination Window
                          </AppButton>
                        )}
                        {actions.examination_close && (
                          <AppButton variant="secondary" size="sm" onClick={() => setPendingAction({ url: actions.examination_close!.replace('__ID__', String(activeYear.id)), label: 'Close examination window' })}>
                            Close Examination Window
                          </AppButton>
                        )}
                      </>
                    ) : (
                      <>
                        {actions.enrollment_open && (
                          <AppButton variant="primary" size="sm" onClick={() => setPendingAction({ url: actions.enrollment_open!.replace('__ID__', String(activeYear.id)), label: 'Open enrollment window' })}>
                            Open Enrollment Window
                          </AppButton>
                        )}
                        {actions.enrollment_close && (
                          <AppButton variant="secondary" size="sm" onClick={() => setPendingAction({ url: actions.enrollment_close!.replace('__ID__', String(activeYear.id)), label: 'Close enrollment window' })}>
                            Close Enrollment Window
                          </AppButton>
                        )}
                      </>
                    )}
                  </div>
                </div>

                <div className="bg-slate-50 p-4 rounded-xl border border-slate-100 text-sm">
                  <h4 className="font-bold text-slate-700 mb-2">Active Global Phase Boundaries</h4>
                  {isExam ? (
                    <div className="space-y-1">
                      <div><strong>Normal Start:</strong> {activeYear.examination_open_date || 'Not set'}</div>
                      <div><strong>Normal End:</strong> {activeYear.examination_close_date || 'Not set'}</div>
                      <div><strong>Grace Period End:</strong> {activeYear.examination_grace_end ? new Date(activeYear.examination_grace_end).toLocaleString() : 'No grace period (direct close)'}</div>
                    </div>
                  ) : (
                    <div className="space-y-1">
                      <div><strong>Normal Start:</strong> {activeYear.enrollment_open_date || 'Not set'}</div>
                      <div><strong>Normal End:</strong> {activeYear.enrollment_close_date || 'Not set'}</div>
                      <div><strong>Grace Period End:</strong> {activeYear.enrollment_grace_end ? new Date(activeYear.enrollment_grace_end).toLocaleString() : 'No grace period (direct close)'}</div>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>

          {/* District & School Overrides Panel */}
          <div className="card">
            <div className="card-header">
              <h2 className="card-title">Active Scope Overrides</h2>
            </div>
            <div className="card-body overflow-x-auto p-0">
              <table className="app-table w-full">
                <thead>
                  <tr>
                    <th className="pl-6">Scope Type</th>
                    <th>Name</th>
                    <th>Normal Window</th>
                    <th>Grace End</th>
                    <th>Status</th>
                    <th className="pr-6 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {activeOverrides.map((ov) => (
                    <tr key={ov.id}>
                      <td className="pl-6 capitalize font-semibold text-slate-700">{ov.scope_type}</td>
                      <td className="font-medium text-slate-900">{ov.scope_name}</td>
                      <td>
                        {new Date(ov.normal_start).toLocaleDateString()} to {new Date(ov.normal_end).toLocaleDateString()}
                      </td>
                      <td className="text-slate-600">
                        {ov.grace_end ? new Date(ov.grace_end).toLocaleString() : <em className="text-slate-400">No Grace</em>}
                      </td>
                      <td>
                        <StatusBadge label={ov.is_active ? 'Active' : 'Inactive'} variant={ov.is_active ? 'success' : 'neutral'} />
                      </td>
                      <td className="pr-6 text-right">
                        <AppButton variant="ghost" size="sm" onClick={() => handleDeleteOverride(ov.id)}>
                          Delete
                        </AppButton>
                      </td>
                    </tr>
                  ))}
                  {activeOverrides.length === 0 && (
                    <tr>
                      <td colSpan={6} className="text-center py-8 text-slate-400 text-sm">
                        No active district or school level overrides configured.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>

          {isExam && (
            <div className="card border-indigo-200">
              <div className="card-header bg-indigo-50/50 flex items-center justify-between">
                <h2 className="card-title text-indigo-950">Exam Dates Announcement Status</h2>
                <StatusBadge
                  label={activeYear.exam_dates_announced ? 'Dates Announced' : 'Not Announced'}
                  variant={activeYear.exam_dates_announced ? 'success' : 'neutral'}
                />
              </div>
              <div className="card-body">
                <p className="text-sm text-slate-500 mb-4">
                  Specifies whether exam timetables and official date sheets have been published to school dashboards. Keep closed during schedule drafting.
                </p>
                <div className="flex flex-wrap gap-2">
                  {!activeYear.exam_dates_announced && actions.exam_announce && (
                    <AppButton variant="primary" size="sm" onClick={() => setPendingAction({ url: actions.exam_announce!.replace('__ID__', String(activeYear.id)), label: 'Announce exam dates sheet' })}>
                      Announce &amp; Publish Dates Sheet
                    </AppButton>
                  )}
                  {activeYear.exam_dates_announced && actions.exam_unannounce && (
                    <AppButton variant="danger" size="sm" onClick={() => setPendingAction({ url: actions.exam_unannounce!.replace('__ID__', String(activeYear.id)), label: 'Withdraw exam dates announcement' })}>
                      Withdraw Announcement
                    </AppButton>
                  )}
                </div>
              </div>
            </div>
          )}
        </div>
      ) : (
        <p className="text-gray-500">No active academic year configured.</p>
      )}

      {/* Global Dates Editing Modal */}
      <AppModal open={showDatesModal} onClose={() => setShowDatesModal(false)} title="Edit Global Phase Dates">
        <form onSubmit={handleSaveDates} className="p-6 space-y-4">
          <input type="hidden" name="window_type" value={datesForm.data.window_type} />
          
          {isExam ? (
            <>
              <AppInput
                label="Normal Open Date"
                type="date"
                value={datesForm.data.examination_open_date}
                onChange={(e) => datesForm.setData('examination_open_date', e.target.value)}
                required
              />
              <AppInput
                label="Normal Close Date (Deadline)"
                type="date"
                value={datesForm.data.examination_close_date}
                onChange={(e) => datesForm.setData('examination_close_date', e.target.value)}
                required
              />
              <AppInput
                label="Grace Period End (Optional)"
                type="datetime-local"
                value={datesForm.data.examination_grace_end}
                onChange={(e) => datesForm.setData('examination_grace_end', e.target.value)}
                placeholder="Leave blank for no grace period"
              />
            </>
          ) : (
            <>
              <AppInput
                label="Normal Open Date"
                type="date"
                value={datesForm.data.enrollment_open_date}
                onChange={(e) => datesForm.setData('enrollment_open_date', e.target.value)}
                required
              />
              <AppInput
                label="Normal Close Date (Deadline)"
                type="date"
                value={datesForm.data.enrollment_close_date}
                onChange={(e) => datesForm.setData('enrollment_close_date', e.target.value)}
                required
              />
              <AppInput
                label="Grace Period End (Optional)"
                type="datetime-local"
                value={datesForm.data.enrollment_grace_end}
                onChange={(e) => datesForm.setData('enrollment_grace_end', e.target.value)}
                placeholder="Leave blank for no grace period"
              />
            </>
          )}

          {Object.keys(datesForm.errors).length > 0 && (
            <div className="bg-red-50 text-red-700 text-xs p-3 rounded border border-red-200">
              {Object.values(datesForm.errors).map((err, idx) => (
                <div key={idx}>{err}</div>
              ))}
            </div>
          )}

          <div className="flex justify-end gap-2 pt-4 border-t border-slate-100">
            <AppButton variant="secondary" onClick={() => setShowDatesModal(false)} disabled={datesForm.processing}>
              Cancel
            </AppButton>
            <AppButton type="submit" variant="primary" loading={datesForm.processing}>
              Save Window Dates
            </AppButton>
          </div>
        </form>
      </AppModal>

      {/* Override Configuration Modal */}
      <AppModal open={showOverrideModal} onClose={() => setShowOverrideModal(false)} title="Configure Window Override">
        <form onSubmit={handleSaveOverride} className="p-6 space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <AppSelect
              label="Scope Type"
              value={overrideForm.data.scope_type}
              onChange={(e) => {
                overrideForm.setData('scope_type', e.target.value);
                overrideForm.setData('scope_id', '');
              }}
              required
            >
              <option value="district">District Override</option>
              <option value="school">School Override</option>
            </AppSelect>

            <AppSelect
              label="Scope Target"
              value={overrideForm.data.scope_id}
              onChange={(e) => overrideForm.setData('scope_id', e.target.value)}
              required
            >
              <option value="">Select scope target...</option>
              {overrideForm.data.scope_type === 'district' ? (
                districts.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)
              ) : (
                schools.map((s) => <option key={s.id} value={s.id}>{s.name} ({s.username})</option>)
              )}
            </AppSelect>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <AppInput
              label="Normal Phase Start"
              type="date"
              value={overrideForm.data.normal_start}
              onChange={(e) => overrideForm.setData('normal_start', e.target.value)}
              required
            />
            <AppInput
              label="Normal Phase End"
              type="date"
              value={overrideForm.data.normal_end}
              onChange={(e) => overrideForm.setData('normal_end', e.target.value)}
              required
            />
          </div>

          <AppInput
            label="Grace Phase End (Optional)"
            type="datetime-local"
            value={overrideForm.data.grace_end}
            onChange={(e) => overrideForm.setData('grace_end', e.target.value)}
            placeholder="Leave blank for direct closed transition"
          />

          {(overrideForm.errors as Record<string, string>).error && (
            <div className="bg-red-50 text-red-700 text-xs p-3 rounded border border-red-200">
              {(overrideForm.errors as Record<string, string>).error}
            </div>
          )}

          <div className="flex justify-end gap-2 pt-4 border-t border-slate-100">
            <AppButton variant="secondary" onClick={() => setShowOverrideModal(false)} disabled={overrideForm.processing}>
              Cancel
            </AppButton>
            <AppButton type="submit" variant="primary" loading={overrideForm.processing}>
              Apply Override
            </AppButton>
          </div>
        </form>
      </AppModal>

      <ConfirmDialog
        open={!!pendingAction}
        title="Confirm Window/Status Change"
        message={`${pendingAction?.label} for all schools. Continue?`}
        onConfirm={runAction}
        onCancel={() => setPendingAction(null)}
      />
    </SuperAdminLayout>
  );
}
