import React from 'react';
import { Link, router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import type { Paginated, Challan } from '@/types';

interface PendingChallansProps {
  challans: Paginated<Challan & { school?: { name: string; code: string } }>;
  filters: {
    status?: string;
    search?: string;
  };
}

export default function PendingChallans({ challans, filters }: PendingChallansProps) {
  function handleFilter(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    router.get(route('superadmin.invoice-verification'), Object.fromEntries(form), { preserveState: true });
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Enrollment Hub / Payment Verification</span>}>
      <PageHeader
        title="Payment Verification (Enrollment)"
        subtitle="Review, approve, or reject school enrollment challans and bank payments"
      />

      <form onSubmit={handleFilter} className="card mb-6 bg-slate-50 border-slate-200">
        <div className="card-body grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
          <AppInput
            label="Search Challan / School"
            name="search"
            defaultValue={filters.search ?? ''}
            placeholder="Search by challan # or school..."
          />
          <AppSelect
            label="Status"
            name="status"
            defaultValue={filters.status ?? 'submitted'}
            placeholder=""
          >
            <option value="submitted">Pending Verification</option>
            <option value="confirmed">Confirmed / Approved</option>
            <option value="rejected">Rejected</option>
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
                <th className="pl-6">Challan Number</th>
                <th>School Code & Name</th>
                <th>Students Count</th>
                <th>Total Amount</th>
                <th>Payment Date</th>
                <th>Status</th>
                <th className="pr-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {challans.data.map((c) => (
                <tr key={c.id}>
                  <td className="pl-6 font-mono text-sm font-semibold text-slate-800">{c.challan_number}</td>
                  <td>
                    <div className="text-sm font-medium text-slate-900">{c.school?.name}</div>
                    <div className="text-xs text-slate-400">Code: {c.school?.code}</div>
                  </td>
                  <td>
                    <span className="badge badge-neutral">{c.student_count} Students</span>
                  </td>
                  <td className="font-semibold text-slate-900">
                    Rs. {(c.total_amount_paisas / 100).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                  </td>
                  <td className="text-sm text-slate-600">
                    {c.payment_date ? new Date(c.payment_date).toLocaleDateString() : '—'}
                  </td>
                  <td>
                    <StatusBadge label={c.status} variant={c.status === 'verified' ? 'success' : (c.status === 'submitted' ? 'warning' : 'danger')} />
                  </td>
                  <td className="pr-6 text-right whitespace-nowrap">
                    <Link
                      href={route('superadmin.invoice-verification.show', c.id)}
                      className="btn btn-secondary btn-sm"
                    >
                      Verify Details →
                    </Link>
                  </td>
                </tr>
              ))}
              {challans.data.length === 0 && (
                <tr>
                  <td colSpan={7} className="text-center py-8 text-slate-400 text-sm">
                    No pending enrollment challans found.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
