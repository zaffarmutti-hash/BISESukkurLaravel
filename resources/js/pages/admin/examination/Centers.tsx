import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { AppButton } from '@/components/shared/AppButton';
import { AppModal } from '@/components/shared/AppModal';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import { ConfirmDialog } from '@/components/shared/ConfirmDialog';
import { usePermissions } from '@/hooks/usePermissions';
import type { Paginated, ExamCenter, District } from '@/types';

interface CentersProps {
  centers: Paginated<ExamCenter & { district?: District }>;
  districts: District[];
  filters: {
    district_id?: string;
    search?: string;
  };
}

export default function Centers({ centers, districts, filters }: CentersProps) {
  const { can } = usePermissions();
  const [showModal, setShowModal] = useState(false);
  const [editingCenter, setEditingCenter] = useState<ExamCenter | null>(null);
  const [deletingCenter, setDeletingCenter] = useState<ExamCenter | null>(null);

  const form = useForm({
    district_id: '',
    name: '',
    code: '',
    address: '',
    capacity: '',
    invigilator_name: '',
    contact_phone: '',
    is_active: true,
  });

  function openCreate() {
    setEditingCenter(null);
    form.setData({
      district_id: districts[0]?.id ? String(districts[0].id) : '',
      name: '',
      code: '',
      address: '',
      capacity: '200',
      invigilator_name: '',
      contact_phone: '',
      is_active: true,
    });
    form.clearErrors();
    setShowModal(true);
  }

  function openEdit(center: ExamCenter) {
    setEditingCenter(center);
    form.setData({
      district_id: String(center.district_id),
      name: center.name,
      code: center.code,
      address: center.address ?? '',
      capacity: String(center.capacity),
      invigilator_name: center.invigilator_name ?? '',
      contact_phone: center.contact_phone ?? '',
      is_active: !!center.is_active,
    });
    form.clearErrors();
    setShowModal(true);
  }

  function handleFilter(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    router.get(route('superadmin.examination.centers.index'), Object.fromEntries(formData), { preserveState: true });
  }

  function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (editingCenter) {
      form.put(route('superadmin.examination.centers.update', editingCenter.id), {
        onSuccess: () => {
          setShowModal(false);
          form.reset();
        }
      });
    } else {
      form.post(route('superadmin.examination.centers.store'), {
        onSuccess: () => {
          setShowModal(false);
          form.reset();
        }
      });
    }
  }

  function handleDelete() {
    if (!deletingCenter) return;
    router.delete(route('superadmin.examination.centers.destroy', deletingCenter.id), {
      onSuccess: () => setDeletingCenter(null),
    });
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Examination Hub / Exam Centers</span>}>
      <PageHeader
        title="Manage Exam Centers"
        subtitle="Configure physical centers, seat capacities, and center superintendents across districts"
        actions={
          can('examination.view') && (
            <AppButton variant="primary" onClick={openCreate}>
              + Register Center
            </AppButton>
          )
        }
      />

      <form onSubmit={handleFilter} className="card mb-6 bg-slate-50 border-slate-200">
        <div className="card-body grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
          <AppInput
            label="Search Name / Code"
            name="search"
            defaultValue={filters.search ?? ''}
            placeholder="Search center name or code..."
          />
          <AppSelect
            label="District"
            name="district_id"
            defaultValue={filters.district_id ?? ''}
            placeholder=""
          >
            <option value="">All Districts</option>
            {districts.map((d) => (
              <option key={d.id} value={d.id}>{d.name}</option>
            ))}
          </AppSelect>
          <button type="submit" className="btn btn-primary h-[42px] justify-center">
            Apply Filters
          </button>
        </div>
      </form>

      <div className="card">
        <div className="card-body overflow-x-auto p-0">
          <table className="app-table w-full">
            <thead>
              <tr>
                <th className="pl-6">Code</th>
                <th>Center Name</th>
                <th>District</th>
                <th>Max Capacity</th>
                <th>Superintendent</th>
                <th>Status</th>
                <th className="pr-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {centers.data.map((c) => (
                <tr key={c.id}>
                  <td className="pl-6 font-mono text-sm font-semibold text-slate-800">{c.code}</td>
                  <td>
                    <div className="text-sm font-semibold text-slate-900">{c.name}</div>
                    <div className="text-xs text-slate-400">{c.address ?? 'No address configured'}</div>
                  </td>
                  <td>{c.district?.name}</td>
                  <td>
                    <span className="font-semibold text-slate-900">{c.capacity}</span>
                    <span className="text-xs text-slate-400 block">candidates</span>
                  </td>
                  <td>
                    <div className="text-sm text-slate-700">{c.invigilator_name ?? '—'}</div>
                    <div className="text-xs text-slate-400 font-mono">{c.contact_phone}</div>
                  </td>
                  <td>
                    <StatusBadge label={c.is_active ? 'Active' : 'Inactive'} variant={c.is_active ? 'success' : 'neutral'} />
                  </td>
                  <td className="pr-6 text-right whitespace-nowrap space-x-2">
                    <AppButton variant="ghost" size="sm" onClick={() => openEdit(c)}>
                      Edit
                    </AppButton>
                    <AppButton variant="ghost" className="text-red-600 hover:bg-red-50" size="sm" onClick={() => setDeletingCenter(c)}>
                      Delete
                    </AppButton>
                  </td>
                </tr>
              ))}
              {centers.data.length === 0 && (
                <tr>
                  <td colSpan={7} className="text-center py-8 text-slate-400 text-sm">
                    No examination centers registered.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Create / Edit Modal */}
      <AppModal open={showModal} onClose={() => setShowModal(false)} title={editingCenter ? 'Edit Exam Center' : 'Register New Exam Center'}>
        <form onSubmit={onSubmit} className="px-6 py-4 space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <AppInput
              label="Center Code"
              placeholder="e.g. SUK-CTR-01"
              value={form.data.code}
              onChange={(e) => form.setData('code', e.target.value)}
              required
            />
            <AppSelect
              label="District"
              value={form.data.district_id}
              onChange={(e) => form.setData('district_id', e.target.value)}
              required
            >
              {districts.map((d) => (
                <option key={d.id} value={d.id}>{d.name}</option>
              ))}
            </AppSelect>
          </div>

          <AppInput
            label="Center Name"
            placeholder="e.g. Government Degree College"
            value={form.data.name}
            onChange={(e) => form.setData('name', e.target.value)}
            required
          />

          <div className="grid grid-cols-2 gap-4">
            <AppInput
              label="Max Seating Capacity"
              type="number"
              min="10"
              value={form.data.capacity}
              onChange={(e) => form.setData('capacity', e.target.value)}
              required
            />
            <div className="flex items-center gap-2 pt-6">
              <input
                type="checkbox"
                id="center_is_active"
                checked={form.data.is_active}
                onChange={(e) => form.setData('is_active', e.target.checked)}
                className="rounded text-amber-600 focus:ring-amber-500"
              />
              <label htmlFor="center_is_active" className="text-sm font-semibold text-slate-700">
                Center is active for allocation
              </label>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <AppInput
              label="Superintendent / Invigilator Name"
              placeholder="Mr. / Ms. Principal"
              value={form.data.invigilator_name}
              onChange={(e) => form.setData('invigilator_name', e.target.value)}
            />
            <AppInput
              label="Superintendent Contact Phone"
              placeholder="e.g. 03xxxxxxxxx"
              value={form.data.contact_phone}
              onChange={(e) => form.setData('contact_phone', e.target.value)}
            />
          </div>

          <div className="form-group">
            <label className="form-label">Physical Address</label>
            <textarea
              className="form-textarea w-full"
              rows={2}
              placeholder="Complete center address..."
              value={form.data.address}
              onChange={(e) => form.setData('address', e.target.value)}
            />
          </div>

          {Object.keys(form.errors).length > 0 && (
            <div className="bg-red-50 text-red-700 text-xs p-3 rounded border border-red-200">
              {Object.values(form.errors).map((err, idx) => (
                <div key={idx}>{err}</div>
              ))}
            </div>
          )}

          <div className="flex justify-end gap-2 pt-2 border-t border-slate-100">
            <AppButton variant="secondary" onClick={() => setShowModal(false)} disabled={form.processing}>
              Cancel
            </AppButton>
            <AppButton type="submit" variant="primary" loading={form.processing}>
              {editingCenter ? 'Save Changes' : 'Create Center'}
            </AppButton>
          </div>
        </form>
      </AppModal>

      {/* Delete Confirmation */}
      <ConfirmDialog
        open={!!deletingCenter}
        title="Delete Exam Center"
        variant="danger"
        message={`Are you sure you want to delete exam center "${deletingCenter?.name}"? This action is permanent and cannot be undone.`}
        onConfirm={handleDelete}
        onCancel={() => setDeletingCenter(null)}
      />
    </SuperAdminLayout>
  );
}
