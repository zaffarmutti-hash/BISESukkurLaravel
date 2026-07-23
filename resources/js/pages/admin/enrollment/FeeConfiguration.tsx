import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { AppButton } from '@/components/shared/AppButton';
import { AppModal } from '@/components/shared/AppModal';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import { usePermissions } from '@/hooks/usePermissions';
import type { AcademicYear, FeeStructure } from '@/types';

interface FeeConfigurationProps {
  fees: FeeStructure[];
  activeYear?: AcademicYear | null;
  actions: {
    store: string;
    update: string;
  };
}

export default function FeeConfiguration({ fees, activeYear, actions }: FeeConfigurationProps) {
  const { can } = usePermissions();
  const [showModal, setShowModal] = useState(false);
  const [editingFee, setEditingFee] = useState<FeeStructure | null>(null);

  const form = useForm({
    class_level: 'ssc_part1',
    student_type: 'fresh',
    fee_type: 'enrollment',
    amount_rupees: '',
    late_fee_surcharge_rupees: '',
    is_active: true,
  });

  function openCreate() {
    setEditingFee(null);
    form.setData({
      class_level: 'ssc_part1',
      student_type: 'fresh',
      fee_type: 'enrollment',
      amount_rupees: '',
      late_fee_surcharge_rupees: '',
      is_active: true,
    });
    form.clearErrors();
    setShowModal(true);
  }

  function openEdit(fee: FeeStructure) {
    setEditingFee(fee);
    form.setData({
      class_level: fee.class_level,
      student_type: fee.student_type,
      fee_type: fee.fee_type,
      amount_rupees: String(fee.amount_paisas / 100),
      late_fee_surcharge_rupees: String((fee.late_fee_surcharge_paisas ?? 0) / 100),
      is_active: !!fee.is_active,
    });
    form.clearErrors();
    setShowModal(true);
  }
  function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!activeYear) return;

    form.transform((oldData) => ({
      class_level: oldData.class_level,
      student_type: oldData.student_type,
      fee_type: oldData.fee_type,
      is_active: oldData.is_active,
      academic_year_id: Number(activeYear.id),
      amount_paisas: Math.round(Number(oldData.amount_rupees) * 100),
      late_fee_surcharge_paisas: Math.round(Number(oldData.late_fee_surcharge_rupees || 0) * 100),
    }));

    if (editingFee) {
      const url = actions.update.replace('__ID__', String(editingFee.id));
      form.put(url, {
        onSuccess: () => {
          setShowModal(false);
          form.reset();
        },
      });
    } else {
      form.post(actions.store, {
        onSuccess: () => {
          setShowModal(false);
          form.reset();
        },
      });
    }
  }

  const classNames: Record<string, string> = {
    ssc_part1: 'SSC Part I (9th)',
    ssc_part2: 'SSC Part II (10th)',
    hsc_part1: 'HSC Part I (11th)',
    hsc_part2: 'HSC Part II (12th)',
  };

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Enrollment Hub / Fee Configuration</span>}>
      <PageHeader
        title="Fee Rates Configuration"
        subtitle="Manage official Board registration and exam fees applied to all schools"
        actions={
          activeYear && can('feerate.create') && (
            <AppButton variant="primary" onClick={openCreate}>
              + Add Fee Rate
            </AppButton>
          )
        }
      />

      {activeYear ? (
        <div className="card mb-6 bg-slate-50 border-slate-200">
          <div className="card-body">
            <p className="text-sm text-slate-700">
              Active operational year: <strong>{activeYear.label}</strong>. Modifying these values updates challan generation instantly.
            </p>
          </div>
        </div>
      ) : (
        <div className="alert alert-warning mb-6">
          Set an active academic year before configuring fee rates.
        </div>
      )}

      <div className="card">
        <div className="card-body overflow-x-auto p-0">
          <table className="app-table w-full">
            <thead>
              <tr>
                <th className="pl-6">Class Level</th>
                <th>Fee Type</th>
                <th>Candidate Type</th>
                <th>Standard Fee (Rs.)</th>
                <th>Late Surcharge (Rs.)</th>
                <th>Status</th>
                <th className="pr-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {fees.map((fee) => (
                <tr key={fee.id}>
                  <td className="pl-6 font-semibold text-slate-800">
                    {classNames[fee.class_level] || fee.class_level.replace(/_/g, ' ').toUpperCase()}
                  </td>
                  <td className="capitalize text-slate-600 font-medium">{fee.fee_type}</td>
                  <td className="capitalize text-slate-500">{fee.student_type}</td>
                  <td className="font-bold text-slate-900">
                    Rs. {(fee.amount_paisas / 100).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                  </td>
                  <td className="font-bold text-amber-700">
                    Rs. {((fee.late_fee_surcharge_paisas ?? 0) / 100).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                  </td>
                  <td>
                    <StatusBadge label={fee.is_active ? 'Active' : 'Inactive'} variant={fee.is_active ? 'success' : 'neutral'} />
                  </td>
                  <td className="pr-6 text-right">
                    {can('feerate.edit') && (
                      <AppButton variant="ghost" size="sm" onClick={() => openEdit(fee)}>
                        Edit
                      </AppButton>
                    )}
                  </td>
                </tr>
              ))}
              {fees.length === 0 && (
                <tr>
                  <td colSpan={6} className="text-center py-8 text-slate-400 text-sm">
                    No fee rates configured for this academic year.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Create / Edit Modal */}
      <AppModal open={showModal} onClose={() => setShowModal(false)} title={editingFee ? 'Edit Fee Rate' : 'Configure New Fee Rate'}>
        <form onSubmit={onSubmit} className="px-6 py-4 space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <AppSelect
              label="Class Level"
              value={form.data.class_level}
              onChange={(e) => form.setData('class_level', e.target.value)}
              disabled={!!editingFee}
              required
            >
              <option value="ssc_part1">SSC Part-I (9th)</option>
              <option value="ssc_part2">SSC Part-II (10th)</option>
              <option value="hsc_part1">HSC Part-I (11th)</option>
              <option value="hsc_part2">HSC Part-II (12th)</option>
            </AppSelect>

            <AppSelect
              label="Fee Type"
              value={form.data.fee_type}
              onChange={(e) => form.setData('fee_type', e.target.value)}
              disabled={!!editingFee}
              required
            >
              <option value="enrollment">Enrollment</option>
              <option value="examination">Examination</option>
            </AppSelect>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <AppSelect
              label="Student Type"
              value={form.data.student_type}
              onChange={(e) => form.setData('student_type', e.target.value)}
              disabled={!!editingFee}
              required
            >
              <option value="fresh">Regular / Fresh</option>
              <option value="repeater">Repeater</option>
              <option value="private">Private</option>
            </AppSelect>

            <AppInput
              label="Standard Fee (PKR)"
              type="number"
              min="1"
              step="0.01"
              value={form.data.amount_rupees}
              onChange={(e) => form.setData('amount_rupees', e.target.value)}
              placeholder="e.g. 1500"
              required
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <AppInput
              label="Late Surcharge (PKR)"
              type="number"
              min="0"
              step="0.01"
              value={form.data.late_fee_surcharge_rupees}
              onChange={(e) => form.setData('late_fee_surcharge_rupees', e.target.value)}
              placeholder="e.g. 500"
              required
            />
          </div>

          <div className="flex items-center gap-2 pt-2">
            <input
              type="checkbox"
              id="is_active"
              checked={form.data.is_active}
              onChange={(e) => form.setData('is_active', e.target.checked)}
              className="rounded text-amber-600 focus:ring-amber-500"
            />
            <label htmlFor="is_active" className="text-sm font-semibold text-slate-700 select-none">
              This rate is active immediately
            </label>
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
              {editingFee ? 'Save Changes' : 'Create Configuration'}
            </AppButton>
          </div>
        </form>
      </AppModal>
    </SuperAdminLayout>
  );
}
