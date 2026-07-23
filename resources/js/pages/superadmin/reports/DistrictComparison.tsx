import { Link } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import type { AcademicYear } from '@/types';

interface DistrictBreakdownRow {
  id: number;
  name: string;
  school_count: number;
  student_count: number;
  verified_amount: number;
  pending_invoices: number;
  missing_exam_forms: number;
}

interface DistrictComparisonProps {
  districtBreakdown: DistrictBreakdownRow[];
  activeYear?: AcademicYear | null;
}

export default function DistrictComparison({ districtBreakdown, activeYear }: DistrictComparisonProps) {
  function downloadCSV() {
    const headers = ['District', 'Schools Count', 'Students Count', 'Verified (PKR)', 'Pending Invoices', 'Missing Forms'];
    const rows = districtBreakdown.map(d => [
      d.name,
      d.school_count,
      d.student_count,
      d.verified_amount,
      d.pending_invoices,
      d.missing_exam_forms,
    ]);

    const csvContent = [headers.join(','), ...rows.map(e => e.map(val => `"${String(val).replace(/"/g, '""')}"`).join(','))].join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `district_comparison_report_${activeYear?.label ?? 'active_year'}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Reports / District Comparison</span>}>
      <div className="no-print">
        <PageHeader
          title="District Comparison"
          subtitle="Overview metrics showing school counts, registered students, and fee tallies per district"
          actions={
            <div className="flex gap-2">
              <button type="button" className="btn btn-secondary" onClick={downloadCSV} disabled={districtBreakdown.length === 0}>
                Export CSV 📥
              </button>
              <button type="button" className="btn btn-secondary" onClick={() => window.print()}>
                Print Report ⎙
              </button>
            </div>
          }
        />
      </div>

      <div className="card">
        <div className="card-header print-only hidden p-6 border-b border-slate-100 flex-col">
          <div className="text-xl font-bold text-slate-800">BISE Sukkur District Comparison Report</div>
          <div className="text-sm text-slate-500 mt-1">Operational Year: {activeYear?.label}</div>
        </div>
        <div className="card-body overflow-x-auto p-0">
          <table className="app-table w-full text-sm">
            <thead>
              <tr>
                <th className="pl-6">District</th>
                <th>Schools</th>
                <th>Enrolled Students</th>
                <th>Verified Fees (PKR)</th>
                <th>Pending Verification</th>
                <th>Missing Exam Forms</th>
                <th className="pr-6 text-right no-print">Actions</th>
              </tr>
            </thead>
            <tbody>
              {districtBreakdown.map((d) => (
                <tr key={d.id}>
                  <td className="pl-6 font-semibold text-slate-800">{d.name}</td>
                  <td>{d.school_count} Schools</td>
                  <td className="font-medium text-slate-700">{d.student_count.toLocaleString()}</td>
                  <td className="font-bold text-slate-900">Rs. {d.verified_amount.toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                  <td>
                    <span className={`badge ${d.pending_invoices > 0 ? 'badge-warning' : 'badge-neutral'}`}>
                      {d.pending_invoices} Pending
                    </span>
                  </td>
                  <td>
                    <span className={`badge ${d.missing_exam_forms > 0 ? 'badge-danger' : 'badge-neutral'}`}>
                      {d.missing_exam_forms} Missing
                    </span>
                  </td>
                  <td className="pr-6 text-right no-print">
                    <Link
                      href={route('superadmin.districts', { district_id: d.id })}
                      className="text-amber-600 font-semibold text-xs hover:underline"
                    >
                      View Details →
                    </Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
