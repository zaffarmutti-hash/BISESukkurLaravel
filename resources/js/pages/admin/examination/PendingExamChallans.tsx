import React from 'react';
import { Link, router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import type { Paginated, Challan } from '@/types';

interface PendingExamChallansProps {
  challans: Paginated<Challan & { school?: { name: string; code: string } }>;
  filters: {
    status?: string;
    search?: string;
  };
}

export default function PendingExamChallans({ challans, filters }: PendingExamChallansProps) {
  function handleFilter(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    router.get(route('superadmin.exam-invoice-verification'), Object.fromEntries(form), { preserveState: true });
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Examination Hub / Payment Verification</span>}>
      <PageHeader
        title="Payment Verification (Examination)"
        subtitle="Review, approve, or reject school exam entry fee challans and bank payments"
      />

      <form onSubmit={handleFilter} className="card mb-6 bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div className="card-body p-5 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
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
          <div className="flex gap-2">
            <button type="submit" className="btn btn-primary h-[42px] justify-center flex-1 font-bold">
              Apply Filters
            </button>
            <button
              type="button"
              onClick={() => router.get(route('superadmin.exam-invoice-verification'))}
              className="btn btn-ghost h-[42px] px-3 font-semibold border border-slate-200 text-slate-600 hover:bg-slate-50"
            >
              Reset
            </button>
          </div>
        </div>
      </form>

      <div className="card border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div className="card-body overflow-x-auto p-0">
          <table className="app-table w-full">
            <thead className="bg-slate-50 border-b border-slate-200">
              <tr>
                <th className="pl-6 py-3.5 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Challan Number</th>
                <th className="py-3.5 text-xs font-extrabold text-slate-500 uppercase tracking-wider">School Code & Name</th>
                <th className="py-3.5 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Candidates</th>
                <th className="py-3.5 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Total Amount</th>
                <th className="py-3.5 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Payment Date</th>
                <th className="py-3.5 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Status</th>
                <th className="pr-6 py-3.5 text-right text-xs font-extrabold text-slate-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {challans.data.map((c) => (
                <tr key={c.id} className="hover:bg-slate-50/80 transition-colors">
                  <td className="pl-6 font-mono text-sm font-bold text-indigo-900 tracking-wide">{c.challan_number}</td>
                  <td>
                    <div className="text-sm font-bold text-slate-900">{c.school?.name}</div>
                    <div className="text-xs text-slate-400 font-medium">Code: <span className="font-mono text-slate-600">{c.school?.code}</span></div>
                  </td>
                  <td>
                    <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200">
                      {c.student_count} Candidates
                    </span>
                  </td>
                  <td className="font-extrabold text-slate-900 text-sm">
                    Rs. {(c.total_amount_paisas / 100).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                  </td>
                  <td className="text-sm text-slate-600">
                    {c.payment_date ? new Date(c.payment_date).toLocaleDateString() : '—'}
                  </td>
                  <td>
                    <StatusBadge label={c.status} variant={(c.status as string) === 'verified' || (c.status as string) === 'confirmed' ? 'success' : (c.status === 'submitted' ? 'warning' : 'danger')} />
                  </td>
                  <td className="pr-6 text-right whitespace-nowrap">
                    <Link
                      href={route('superadmin.invoice-verification.show', c.id)}
                      className="inline-flex items-center gap-1 text-amber-700 font-bold bg-amber-50 hover:bg-amber-100 border border-amber-200 px-3.5 py-1.5 rounded-lg text-xs transition-colors"
                    >
                      Verify Details →
                    </Link>
                  </td>
                </tr>
              ))}
              {challans.data.length === 0 && (
                <tr>
                  <td colSpan={7} className="text-center py-12 text-slate-400 text-sm">
                    No pending examination challans found.
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
