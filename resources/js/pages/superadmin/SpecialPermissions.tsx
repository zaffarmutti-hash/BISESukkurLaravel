import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { ConfirmDialog } from '@/components/shared/ConfirmDialog';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import type { AcademicYear, Paginated, School, SelectOption } from '@/types';

interface ExceptionItem {
  id: number;
  exception_type: string;
  reason: string;
  expires_at?: string | null;
  school?: School;
  granted_by?: { name: string };
  academic_year?: AcademicYear;
}

interface SpecialPermissionsProps {
  exceptions: Paginated<ExceptionItem>;
  filters: Record<string, string | boolean | undefined>;
  schools: School[];
  years: AcademicYear[];
  types: SelectOption[];
}

export default function SpecialPermissions({ exceptions, filters: _filters, schools, years, types }: SpecialPermissionsProps) {
  const [showGrant, setShowGrant] = useState(false);
  const [revokeId, setRevokeId] = useState<number | null>(null);

  const form = useForm({
    school_id: '',
    academic_year_id: years.find((y) => y.is_active)?.id?.toString() ?? '',
    exception_type: 'extend_enrollment_deadline',
    reason: '',
    expires_at: '',
  });

  function grant(e: React.FormEvent) {
    e.preventDefault();
    form.post(route('superadmin.special-permissions.store'), {
      onSuccess: () => {
        setShowGrant(false);
        form.reset();
      },
    });
  }

  function revoke() {
    if (!revokeId) return;
    router.post(route('superadmin.special-permissions.revoke', revokeId), {}, {
      onSuccess: () => setRevokeId(null),
    });
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Enrollment Hub / Special Permissions</span>}>
      <PageHeader
        title="Special Permissions"
        subtitle="Time-bound, reasoned exceptions granted to individual schools — audit at a glance"
        actions={
          <button type="button" className="btn btn-primary" onClick={() => setShowGrant(true)}>
            Grant Exception
          </button>
        }
      />

      <div className="card">
        <div className="card-body overflow-x-auto">
          <table className="data-table w-full">
            <thead>
              <tr>
                <th>School</th>
                <th>Type</th>
                <th>Year</th>
                <th>Granted By</th>
                <th>Expires</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {exceptions.data.map((ex) => (
                <tr key={ex.id}>
                  <td className="font-medium">{ex.school?.name}</td>
                  <td><StatusBadge label={ex.exception_type.replace(/_/g, ' ')} variant="warning" /></td>
                  <td>{ex.academic_year?.label}</td>
                  <td>{ex.granted_by?.name}</td>
                  <td>{ex.expires_at ?? 'No expiry'}</td>
                  <td>
                    <button type="button" onClick={() => setRevokeId(ex.id)} className="text-red-600 text-xs font-semibold">
                      Revoke
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      <ConfirmDialog
        open={showGrant}
        title="Grant Special Permission"
        message={
          <form id="grant-form" onSubmit={grant} className="space-y-3">
            <AppSelect label="School" required value={form.data.school_id} onChange={(e) => form.setData('school_id', e.target.value)} placeholder="">
              {schools.map((s) => (
                <option key={s.id} value={s.id}>{s.name} ({s.code})</option>
              ))}
            </AppSelect>
            <AppSelect label="Academic Year" required value={form.data.academic_year_id} onChange={(e) => form.setData('academic_year_id', e.target.value)} placeholder="">
              {years.map((y) => (
                <option key={y.id} value={y.id}>{y.label}</option>
              ))}
            </AppSelect>
            <AppSelect label="Exception Type" required value={form.data.exception_type} onChange={(e) => form.setData('exception_type', e.target.value)} options={types} placeholder="" />
            <label className="block text-sm font-medium text-gray-700">Reason (required)</label>
            <textarea
              className="form-input w-full min-h-24"
              required
              value={form.data.reason}
              onChange={(e) => form.setData('reason', e.target.value)}
            />
            <AppInput label="Expires At (optional)" type="date" value={form.data.expires_at} onChange={(e) => form.setData('expires_at', e.target.value)} />
          </form>
        }
        confirmLabel="Grant Exception"
        onConfirm={() => document.getElementById('grant-form')?.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }))}
        onCancel={() => setShowGrant(false)}
      />

      <ConfirmDialog
        open={revokeId !== null}
        title="Revoke Exception"
        message="This will immediately remove the school's special permission. Continue?"
        confirmLabel="Revoke Now"
        variant="danger"
        onConfirm={revoke}
        onCancel={() => setRevokeId(null)}
      />
    </SuperAdminLayout>
  );
}
