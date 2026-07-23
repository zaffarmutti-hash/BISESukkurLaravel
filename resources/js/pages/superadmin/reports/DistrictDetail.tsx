import React from 'react';
import { Link, router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppInput } from '@/components/shared/AppInput';
import type { Paginated, District, AcademicYear } from '@/types';

interface SchoolDetailRow {
  id: number;
  name: string;
  code: string;
  student_count: number;
  verified_amount: number;
  pending_invoices: number;
  missing_exam_forms: number;
}

interface DistrictDetailProps {
  district: District;
  schools: Paginated<SchoolDetailRow>;
  activeYear?: AcademicYear | null;
  filters: {
    district_id: string;
    search?: string;
  };
}

export default function DistrictDetail({ district, schools, activeYear, filters }: DistrictDetailProps) {
  function handleFilter(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    router.get(route('superadmin.districts'), Object.fromEntries(formData), { preserveState: true });
  }

  function downloadCSV() {
    const headers = ['School Code', 'School Name', 'Students Enrolled', 'Verified amount (PKR)', 'Pending Invoices', 'Missing Forms'];
    const rows = schools.data.map(s => [
      s.code,
      s.name,
      s.student_count,
      s.verified_amount,
      s.pending_invoices,
      s.missing_exam_forms,
    ]);

    const csvContent = [headers.join(','), ...rows.map(e => e.map(val => `"${String(val).replace(/"/g, '""')}"`).join(','))].join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `schools_report_district_${district.name.replace(/\s+/g, '_')}_${activeYear?.label ?? 'active_year'}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  return (
    <SuperAdminLayout breadcrumb={
      <nav className="flex items-center gap-2 text-sm text-gray-500">
        <Link href={route('superadmin.districts')} className="hover:text-gray-700">District Comparison</Link>
        <span>/</span>
        <span className="text-gray-800 font-medium">{district.name} Details</span>
      </nav>
    }>
      <div className="no-print">
        <PageHeader
          title={`District: ${district.name}`}
          subtitle={`School-by-school operational audit inside ${district.name}`}
          actions={
            <div className="flex gap-2">
              <button type="button" className="btn btn-secondary" onClick={downloadCSV} disabled={schools.data.length === 0}>
                Export CSV 📥
              </button>
              <button type="button" className="btn btn-secondary" onClick={() => window.print()}>
                Print Report ⎙
              </button>
            </div>
          }
        />
      </div>

      <form onSubmit={handleFilter} className="card mb-6 bg-slate-50 border-slate-200 no-print">
        <input type="hidden" name="district_id" value={district.id} />
        <div className="card-body grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
          <AppInput
            label="Search School / College"
            name="search"
            defaultValue={filters.search ?? ''}
            placeholder="Search school name or code..."
            className="md:col-span-2"
          />
          <button type="submit" className="btn btn-primary h-[42px] justify-center">
            Filter Schools
          </button>
        </div>
      </form>

      <div className="card">
        <div className="card-header print-only hidden p-6 border-b border-slate-100 flex-col">
          <div className="text-xl font-bold text-slate-800">BISE Sukkur School Audit Report - {district.name}</div>
          <div className="text-sm text-slate-500 mt-1">Operational Year: {activeYear?.label}</div>
        </div>
        <div className="card-body overflow-x-auto p-0">
          <table className="app-table w-full text-sm">
            <thead>
              <tr>
                <th className="pl-6">Code</th>
                <th>School Name</th>
                <th>Enrolled Students</th>
                <th>Verified Fees (PKR)</th>
                <th>Pending Verification</th>
                <th className="pr-6">Missing Forms</th>
              </tr>
            </thead>
            <tbody>
              {schools.data.map((s) => (
                <tr key={s.id}>
                  <td className="pl-6 font-mono text-sm font-semibold text-slate-800">{s.code}</td>
                  <td className="font-semibold text-slate-900">{s.name}</td>
                  <td>{s.student_count} Candidates</td>
                  <td className="font-bold text-slate-950">Rs. {s.verified_amount.toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                  <td>
                    <span className={`badge ${s.pending_invoices > 0 ? 'badge-warning' : 'badge-neutral'}`}>
                      {s.pending_invoices} Pending
                    </span>
                  </td>
                  <td className="pr-6">
                    <span className={`badge ${s.missing_exam_forms > 0 ? 'badge-danger' : 'badge-neutral'}`}>
                      {s.missing_exam_forms} Missing
                    </span>
                  </td>
                </tr>
              ))}
              {schools.data.length === 0 && (
                <tr>
                  <td colSpan={6} className="text-center py-8 text-slate-400 text-sm">
                    No schools matching criteria found.
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
