import React, { useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { AppButton } from '@/components/shared/AppButton';
import { AppModal } from '@/components/shared/AppModal';
import { ConfirmDialog } from '@/components/shared/ConfirmDialog';
import { usePermissions } from '@/hooks/usePermissions';
import type { Challan, Student, StudentAcademicRecord } from '@/types';

interface ChallanStudentDetail {
  id: number;
  student?: Student;
  studentAcademicRecord?: StudentAcademicRecord;
}

interface ChallanDetailProps {
  challan: Challan & {
    school?: { name: string; code: string; district?: { name: string } };
    challan_students: ChallanStudentDetail[];
    approved_by?: { name: string };
  };
}

export default function ChallanDetail({ challan }: ChallanDetailProps) {
  const { can } = usePermissions();
  const [showConfirm, setShowConfirm] = useState(false);
  const [showRejectModal, setShowRejectModal] = useState(false);
  const [processing, setProcessing] = useState(false);

  const rejectForm = useForm({
    rejection_reason: '',
  });

  function handleConfirm() {
    setProcessing(true);
    router.post(route('superadmin.invoice-verification.confirm', challan.id), {}, {
      onSuccess: () => {
        setProcessing(false);
        setShowConfirm(false);
      },
      onError: () => setProcessing(false)
    });
  }

  function handleReject(e: React.FormEvent) {
    e.preventDefault();
    rejectForm.post(route('superadmin.invoice-verification.reject', challan.id), {
      onSuccess: () => {
        setShowRejectModal(false);
        rejectForm.reset();
      }
    });
  }

  return (
    <SuperAdminLayout breadcrumb={
      <nav className="flex items-center gap-2 text-sm text-gray-500">
        <Link href={route('superadmin.invoice-verification')} className="hover:text-gray-700">Verification List</Link>
        <span>/</span>
        <span className="text-gray-800 font-medium">Challan Details</span>
      </nav>
    }>
      <PageHeader
        title={`Challan: ${challan.challan_number}`}
        subtitle={`Submitted by ${challan.school?.name} (${challan.school?.code})`}
      />

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {/* Left Column: Challan & Payment Info */}
        <div className="lg:col-span-2 space-y-6">
          <div className="card">
            <div className="card-header"><h2 className="card-title">Payment Information</h2></div>
            <div className="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <span className="text-xs text-slate-400 block uppercase font-semibold">Challan Type</span>
                <span className="text-sm font-semibold capitalize text-slate-800">{challan.challan_type}</span>
              </div>
              <div>
                <span className="text-xs text-slate-400 block uppercase font-semibold">Status</span>
                <StatusBadge label={challan.status} variant={challan.status === 'verified' ? 'success' : (challan.status === 'submitted' ? 'warning' : 'danger')} />
              </div>
              <div>
                <span className="text-xs text-slate-400 block uppercase font-semibold">Total Amount</span>
                <span className="text-lg font-bold text-slate-900">
                  Rs. {(challan.total_amount_paisas / 100).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                </span>
              </div>
              <div>
                <span className="text-xs text-slate-400 block uppercase font-semibold">Students Count</span>
                <span className="text-sm font-medium text-slate-800">{challan.student_count} Candidates</span>
              </div>
              <div className="border-t border-slate-100 pt-2 col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <span className="text-xs text-slate-400 block uppercase font-semibold">Bank Name & Branch</span>
                  <span className="text-sm font-medium text-slate-800">{challan.bank_name ?? '—'} ({challan.bank_branch ?? '—'})</span>
                </div>
                <div>
                  <span className="text-xs text-slate-400 block uppercase font-semibold">Bank Reference / Scroll #</span>
                  <span className="text-sm font-mono text-slate-800 font-semibold">{challan.bank_reference ?? '—'}</span>
                </div>
                <div>
                  <span className="text-xs text-slate-400 block uppercase font-semibold">Payment Date</span>
                  <span className="text-sm font-medium text-slate-800">
                    {challan.payment_date ? new Date(challan.payment_date).toLocaleDateString() : '—'}
                  </span>
                </div>
                {challan.rejection_reason && (
                  <div className="col-span-1 md:col-span-2 bg-red-50 text-red-800 p-3 rounded-lg border border-red-200">
                    <span className="text-xs font-semibold block uppercase">Rejection Reason</span>
                    <p className="text-sm font-medium">{challan.rejection_reason}</p>
                  </div>
                )}
              </div>
            </div>
          </div>

          <div className="card">
            <div className="card-header"><h2 className="card-title">Enrolled Students List</h2></div>
            <div className="card-body overflow-x-auto p-0">
              <table className="app-table w-full text-sm">
                <thead>
                  <tr>
                    <th className="pl-6">Student Name</th>
                    <th>Father Name</th>
                    <th>CNIC / B-Form</th>
                    <th>Class Level</th>
                    <th className="pr-6">Subject Group</th>
                  </tr>
                </thead>
                <tbody>
                  {challan.challan_students.map((cs) => (
                    <tr key={cs.id}>
                      <td className="pl-6 font-semibold text-slate-800">{cs.student?.full_name}</td>
                      <td>{cs.student?.father_name}</td>
                      <td className="font-mono text-xs">{cs.student?.cnic ?? cs.student?.b_form ?? '—'}</td>
                      <td className="uppercase text-xs font-semibold text-slate-500">
                        {cs.studentAcademicRecord?.class_level.replace(/_/g, ' ')}
                      </td>
                      <td className="pr-6 capitalize text-slate-600">
                        {cs.studentAcademicRecord?.subject_group}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>

        {/* Right Column: Evidence & Verification Controls */}
        <div className="space-y-6">
          <div className="card">
            <div className="card-header"><h2 className="card-title">Deposit Slip Evidence</h2></div>
            <div className="card-body">
              {challan.deposit_slip_path ? (
                <div className="space-y-3">
                  <a
                    href={`/storage/${challan.deposit_slip_path}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="block group relative overflow-hidden rounded-lg border border-slate-200 bg-slate-50 hover:border-slate-400 transition"
                  >
                    <img
                      src={`/storage/${challan.deposit_slip_path}`}
                      alt="Deposit Slip Evidence"
                      className="w-full h-auto max-h-[300px] object-contain mx-auto"
                    />
                    <div className="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white font-medium text-xs">
                      Click to Open Original ↗
                    </div>
                  </a>
                </div>
              ) : (
                <p className="text-sm text-slate-400 text-center py-6">No deposit slip document uploaded.</p>
              )}
            </div>
          </div>

          {challan.status === 'submitted' && can('invoice.verify') && (
            <div className="card border-amber-200">
              <div className="card-header bg-amber-50">
                <h2 className="card-title text-amber-800">Verification Actions</h2>
              </div>
              <div className="card-body space-y-3">
                <p className="text-xs text-slate-500">
                  Verify the bank scroll statement matches the challan number and amount above before confirming.
                </p>
                <AppButton
                  variant="primary"
                  className="w-full justify-center"
                  onClick={() => setShowConfirm(true)}
                >
                  Confirm &amp; Approve Payment
                </AppButton>
                <AppButton
                  variant="danger"
                  className="w-full justify-center"
                  onClick={() => setShowRejectModal(true)}
                >
                  Reject Deposit Slip
                </AppButton>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Confirmation Dialog */}
      <ConfirmDialog
        open={showConfirm}
        title="Confirm Payment Approval"
        message={
          <div className="space-y-2">
            <p>Are you sure you want to approve this challan of <strong>Rs. {(challan.total_amount_paisas / 100).toLocaleString()}</strong>?</p>
            <p className="text-xs text-slate-400">
              This will update all associated student academic records to <strong>enrolled</strong> and trigger their enrollment number generation.
            </p>
          </div>
        }
        loading={processing}
        onConfirm={handleConfirm}
        onCancel={() => setShowConfirm(false)}
      />

      {/* Rejection Modal */}
      <AppModal open={showRejectModal} onClose={() => setShowRejectModal(false)} title="Reject Deposit Slip">
        <form onSubmit={handleReject} className="px-6 py-4 space-y-4">
          <div className="form-group">
            <label className="form-label required">Rejection Reason</label>
            <textarea
              className="form-textarea w-full"
              rows={3}
              placeholder="e.g. Deposited amount does not match; Reference number invalid; Image illegible..."
              value={rejectForm.data.rejection_reason}
              onChange={(e) => rejectForm.setData('rejection_reason', e.target.value)}
              required
            />
            {rejectForm.errors.rejection_reason && (
              <span className="form-error">{rejectForm.errors.rejection_reason}</span>
            )}
          </div>
          <div className="flex justify-end gap-2 pt-2">
            <AppButton variant="secondary" onClick={() => setShowRejectModal(false)} disabled={rejectForm.processing}>
              Cancel
            </AppButton>
            <AppButton type="submit" variant="danger" loading={rejectForm.processing}>
              Reject Challan
            </AppButton>
          </div>
        </form>
      </AppModal>
    </SuperAdminLayout>
  );
}
