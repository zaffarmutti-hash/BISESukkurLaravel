import React from 'react';
import { router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import { StatusBadge } from '@/components/shared/StatusBadge';
import type { Paginated, StudentAcademicRecord, Student, School, District, AcademicYear } from '@/types';

interface EnrollmentReportProps {
  records: Paginated<StudentAcademicRecord & { student?: Student & { school?: School & { district?: District } } }>;
  districts: District[];
  schools: School[];
  activeYear?: AcademicYear | null;
  filters: {
    district_id?: string;
    school_id?: string;
    class_level?: string;
    search?: string;
  };
}

export default function EnrollmentReport({ records, districts, schools, activeYear, filters }: EnrollmentReportProps) {
  function handleFilter(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    router.get(route('superadmin.reports.enrollment'), Object.fromEntries(formData), { preserveState: true });
  }

  function downloadCSV() {
    const headers = ['Enrollment Number', 'Student Name', 'Father Name', 'Class Level', 'Subject Group', 'School', 'District'];
    const rows = records.data.map(r => [
      r.student?.enrollment_number ?? 'Pending',
      r.student?.full_name ?? '',
      r.student?.father_name ?? '',
      r.class_level.toUpperCase(),
      r.subject_group,
      r.student?.school?.name ?? '',
      r.student?.school?.district?.name ?? '',
    ]);

    const csvContent = [headers.join(','), ...rows.map(e => e.map(val => `"${val.replace(/"/g, '""')}"`).join(','))].join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `enrollment_report_${activeYear?.label ?? 'active_year'}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  const classNames: Record<string, string> = {
    ssc_part1: 'SSC Part I (9th)',
    ssc_part2: 'SSC Part II (10th)',
    hsc_part1: 'HSC Part I (11th)',
    hsc_part2: 'HSC Part II (12th)',
  };

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Reports / Enrollment Report</span>}>
      <div className="no-print">
        <PageHeader
          title="Candidate Enrollment Report"
          subtitle="Detailed list of all candidates operating within the active academic year"
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
        <div className="card-body grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
          <AppInput
            label="Search Student"
            name="search"
            defaultValue={filters.search ?? ''}
            placeholder="Name or roll..."
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
          <AppSelect
            label="Class Level"
            name="class_level"
            defaultValue={filters.class_level ?? ''}
            placeholder=""
          >
            <option value="">All Classes</option>
            <option value="ssc_part1">SSC Part I (9th)</option>
            <option value="ssc_part2">SSC Part II (10th)</option>
            <option value="hsc_part1">HSC Part I (11th)</option>
            <option value="hsc_part2">HSC Part II (12th)</option>
          </AppSelect>
          <button type="submit" className="btn btn-primary h-[42px] justify-center">
            Filter Report
          </button>
        </div>
      </form>

      <div className="card">
        <div className="card-header print-only hidden p-6 border-b border-slate-100 flex-col">
          <div className="text-xl font-bold text-slate-800">BISE Sukkur Enrollment Report</div>
          <div className="text-sm text-slate-500 mt-1">Operational Year: {activeYear?.label}</div>
        </div>
        <div className="card-body overflow-x-auto p-0">
          <table className="app-table w-full text-sm">
            <thead>
              <tr>
                <th className="pl-6">Enrollment Number</th>
                <th>Candidate Name</th>
                <th>Father Name</th>
                <th>Class Level</th>
                <th>Subject Group</th>
                <th>School & District</th>
                <th className="pr-6">Status</th>
              </tr>
            </thead>
            <tbody>
              {records.data.map((r) => (
                <tr key={r.id}>
                  <td className="pl-6 font-mono text-sm font-semibold text-slate-800">{r.student?.enrollment_number ?? 'Pending'}</td>
                  <td className="font-semibold text-slate-900">{r.student?.full_name}</td>
                  <td>{r.student?.father_name}</td>
                  <td>
                    <span className="badge badge-neutral text-xs">
                      {classNames[r.class_level] || r.class_level.replace(/_/g, ' ').toUpperCase()}
                    </span>
                  </td>
                  <td className="capitalize text-slate-600">{r.subject_group}</td>
                  <td>
                    <div className="font-medium text-slate-800">{r.student?.school?.name}</div>
                    <div className="text-xs text-slate-400">{r.student?.school?.district?.name}</div>
                  </td>
                  <td className="pr-6">
                    <StatusBadge label={r.status} variant={r.status === 'enrolled' ? 'success' : (r.status === 'draft' ? 'neutral' : 'warning')} />
                  </td>
                </tr>
              ))}
              {records.data.length === 0 && (
                <tr>
                  <td colSpan={7} className="text-center py-8 text-slate-400 text-sm">
                    No enrollment records found matching the criteria.
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
