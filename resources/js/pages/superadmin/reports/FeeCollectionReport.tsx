import React from 'react';
import { router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import type { Paginated, Challan, School, District, AcademicYear } from '@/types';

interface FeeCollectionReportProps {
  records: Paginated<Challan & { school?: School & { district?: District } }>;
  districts: District[];
  schools: School[];
  activeYear?: AcademicYear | null;
  filters: {
    district_id?: string;
    school_id?: string;
    date_from?: string;
    date_to?: string;
  };
}

export default function FeeCollectionReport({ records, districts, schools, activeYear, filters }: FeeCollectionReportProps) {
  function handleFilter(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    router.get(route('superadmin.reports.fee-collection'), Object.fromEntries(formData), { preserveState: true });
  }

  function downloadCSV() {
    const headers = ['Challan Number', 'Challan Type', 'School', 'District', 'Students Count', 'Amount (PKR)', 'Payment Date'];
    const rows = records.data.map(r => [
      r.challan_number,
      r.challan_type.toUpperCase(),
      r.school?.name ?? '',
      r.school?.district?.name ?? '',
      r.student_count,
      r.total_amount_paisas / 100,
      r.payment_date ?? '',
    ]);

    const csvContent = [headers.join(','), ...rows.map(e => e.map(val => `"${String(val).replace(/"/g, '""')}"`).join(','))].join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `fee_collection_report_${activeYear?.label ?? 'active_year'}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  const totalCollected = records.data.reduce((acc, curr) => acc + (curr.total_amount_paisas / 100), 0);

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Reports / Fee Collection</span>}>
      <div className="no-print">
        <PageHeader
          title="Board Fee Collection Report"
          subtitle="Audit verified payments, transaction records, and total revenue collected from schools"
          actions={
            <div className="flex gap-2">
              <button type="button" className="btn btn-secondary" onClick={downloadCSV} disabled={records.data.length === 0}>
                Export CSV 📥
              </button>
              <button type="button" className="btn btn-secondary" onClick={() => window.print()}>
                Print Report ⎙
              </button>
            </div>
          }
        />
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 no-print">
        <div className="card bg-slate-50 border-slate-200">
          <div className="card-body">
            <span className="text-xs font-semibold text-slate-500 uppercase">Operational Year</span>
            <div className="text-lg font-bold text-slate-800 mt-1">{activeYear?.label ?? 'None'}</div>
          </div>
        </div>
        <div className="card bg-slate-50 border-slate-200">
          <div className="card-body">
            <span className="text-xs font-semibold text-slate-500 uppercase">Challans Count (In Page)</span>
            <div className="text-lg font-bold text-slate-800 mt-1">{records.data.length} Confirmed</div>
          </div>
        </div>
        <div className="card bg-indigo-900 text-white">
          <div className="card-body">
            <span className="text-xs font-semibold text-indigo-200 uppercase">Total Revenue (In Page)</span>
            <div className="text-xl font-black mt-1">Rs. {totalCollected.toLocaleString(undefined, { minimumFractionDigits: 2 })}</div>
          </div>
        </div>
      </div>

      <form onSubmit={handleFilter} className="card mb-6 bg-slate-50 border-slate-200 no-print">
        <div className="card-body grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
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
          <AppSelect
            label="School"
            name="school_id"
            defaultValue={filters.school_id ?? ''}
            placeholder=""
          >
            <option value="">All Schools</option>
            {schools
              .filter(s => !filters.district_id || String(s.district_id) === filters.district_id)
              .map((s) => (
                <option key={s.id} value={s.id}>{s.name} ({s.code})</option>
              ))}
          </AppSelect>
          <AppInput
            label="From Date"
            type="date"
            name="date_from"
            defaultValue={filters.date_from ?? ''}
          />
          <AppInput
            label="To Date"
            type="date"
            name="date_to"
            defaultValue={filters.date_to ?? ''}
          />
          <button type="submit" className="btn btn-primary h-[42px] justify-center">
            Filter Collection
          </button>
        </div>
      </form>

      <div className="card">
        <div className="card-header print-only hidden p-6 border-b border-slate-100 flex-col">
          <div className="text-xl font-bold text-slate-800">BISE Sukkur Fee Collection Report</div>
          <div className="text-sm text-slate-500 mt-1">Operational Year: {activeYear?.label}</div>
        </div>
        <div className="card-body overflow-x-auto p-0">
          <table className="app-table w-full text-sm">
            <thead>
              <tr>
                <th className="pl-6">Challan Number</th>
                <th>Challan Type</th>
                <th>School Name & Code</th>
                <th>District</th>
                <th>Students Count</th>
                <th>Payment Date</th>
                <th className="pr-6 text-right">Amount (PKR)</th>
              </tr>
            </thead>
            <tbody>
              {records.data.map((r) => (
                <tr key={r.id}>
                  <td className="pl-6 font-mono text-sm font-semibold text-slate-800">{r.challan_number}</td>
                  <td className="capitalize text-xs font-semibold text-slate-500">{r.challan_type}</td>
                  <td>
                    <div className="font-medium text-slate-900">{r.school?.name}</div>
                    <div className="text-xs text-slate-400">Code: {r.school?.code}</div>
                  </td>
                  <td>{r.school?.district?.name}</td>
                  <td>
                    <span className="badge badge-neutral text-xs">{r.student_count} Candidates</span>
                  </td>
                  <td className="text-slate-600">
                    {r.payment_date ? new Date(r.payment_date).toLocaleDateString() : '—'}
                  </td>
                  <td className="pr-6 text-right font-bold text-slate-950">
                    Rs. {(r.total_amount_paisas / 100).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                  </td>
                </tr>
              ))}
              {records.data.length === 0 && (
                <tr>
                  <td colSpan={7} className="text-center py-8 text-slate-400 text-sm">
                    No matching fee collections found.
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
