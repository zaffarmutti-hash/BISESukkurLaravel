import React from 'react';
import { router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import type { Paginated, Student, School, District, AcademicYear } from '@/types';

interface ExamGapReportProps {
  records: Paginated<Student & { school?: School & { district?: District } }>;
  districts: District[];
  schools: School[];
  activeYear?: AcademicYear | null;
  filters: {
    district_id?: string;
    school_id?: string;
    search?: string;
  };
}

export default function ExamGapReport({ records, districts, schools, activeYear, filters }: ExamGapReportProps) {
  function handleFilter(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    router.get(route('superadmin.reports.gap'), Object.fromEntries(formData), { preserveState: true });
  }

  function downloadCSV() {
    const headers = ['Enrollment Number', 'Student Name', 'Father Name', 'Phone', 'School', 'District'];
    const rows = records.data.map(r => [
      r.enrollment_number ?? '',
      r.full_name,
      r.father_name,
      r.phone ?? '',
      r.school?.name ?? '',
      r.school?.district?.name ?? '',
    ]);

    const csvContent = [headers.join(','), ...rows.map(e => e.map(val => `"${val.replace(/"/g, '""')}"`).join(','))].join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `exam_gap_report_${activeYear?.label ?? 'active_year'}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Reports / Exam Gap Report</span>}>
      <div className="no-print">
        <PageHeader
          title="Missing Exam Forms (Gap Analysis)"
          subtitle="Identify students holding enrollment numbers who have not yet submitted exam registration forms"
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

      <form onSubmit={handleFilter} className="card mb-6 bg-slate-50 border-slate-200 no-print">
        <div className="card-body grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
          <AppInput
            label="Search Student"
            name="search"
            defaultValue={filters.search ?? ''}
            placeholder="Name or enrollment..."
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
          <button type="submit" className="btn btn-primary h-[42px] justify-center">
            Filter Report
          </button>
        </div>
      </form>

      <div className="card">
        <div className="card-header print-only hidden p-6 border-b border-slate-100 flex-col">
          <div className="text-xl font-bold text-slate-800">BISE Sukkur Exam Gap Analysis Report</div>
          <div className="text-sm text-slate-500 mt-1">Operational Year: {activeYear?.label}</div>
        </div>
        <div className="card-body overflow-x-auto p-0">
          <table className="app-table w-full text-sm">
            <thead>
              <tr>
                <th className="pl-6">Enrollment Number</th>
                <th>Candidate Name</th>
                <th>Father Name</th>
                <th>Contact Phone</th>
                <th>School Name & Code</th>
                <th className="pr-6">District</th>
              </tr>
            </thead>
            <tbody>
              {records.data.map((r) => (
                <tr key={r.id}>
                  <td className="pl-6 font-mono text-sm font-semibold text-slate-800">{r.enrollment_number}</td>
                  <td className="font-semibold text-slate-900">{r.full_name}</td>
                  <td>{r.father_name}</td>
                  <td className="font-mono text-xs">{r.phone ?? r.guardian_phone ?? '—'}</td>
                  <td>
                    <div className="font-medium text-slate-800">{r.school?.name}</div>
                    <div className="text-xs text-slate-400">Code: {r.school?.code}</div>
                  </td>
                  <td className="pr-6 font-medium text-slate-600">{r.school?.district?.name}</td>
                </tr>
              ))}
              {records.data.length === 0 && (
                <tr>
                  <td colSpan={6} className="text-center py-8 text-slate-400 text-sm">
                    No matching candidates found without an exam form.
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
