import { router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppSelect } from '@/components/shared/AppSelect';
import type { AcademicYear, District } from '@/types';

interface ReportsAnalyticsProps {
  years: AcademicYear[];
  districts: District[];
  selectedYearId?: number | null;
  districtBreakdown: District[];
  summary: {
    total_students: number;
    verified_payments: number;
    pending_verification: number;
    missing_exam_forms: number;
  };
}

export default function ReportsAnalytics({
  years,
  selectedYearId,
  districtBreakdown,
  summary,
}: ReportsAnalyticsProps) {
  function changeYear(e: React.ChangeEvent<HTMLSelectElement>) {
    router.get(route('superadmin.reports-analytics'), { academic_year_id: e.target.value }, { preserveState: true });
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Reports & Analytics</span>}>
      <PageHeader
        title="Cross-Domain Reports"
        subtitle="Compare enrollment, fees, and examination data across districts and years"
        actions={
          <AppSelect value={String(selectedYearId ?? '')} onChange={changeYear} placeholder="">
            {years.map((y) => (
              <option key={y.id} value={y.id}>{y.label}{y.is_active ? ' (Active)' : ''}</option>
            ))}
          </AppSelect>
        }
      />

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div className="card"><div className="card-body"><p className="text-xs text-gray-500 uppercase">Students</p><p className="text-2xl font-bold">{summary.total_students.toLocaleString()}</p></div></div>
        <div className="card"><div className="card-body"><p className="text-xs text-gray-500 uppercase">Verified Fees (Rs)</p><p className="text-2xl font-bold">{summary.verified_payments.toLocaleString()}</p></div></div>
        <div className="card"><div className="card-body"><p className="text-xs text-gray-500 uppercase">Pending Verification</p><p className="text-2xl font-bold text-amber-600">{summary.pending_verification.toLocaleString()}</p></div></div>
        <div className="card"><div className="card-body"><p className="text-xs text-gray-500 uppercase">Missing Exam Forms</p><p className="text-2xl font-bold text-red-600">{summary.missing_exam_forms.toLocaleString()}</p></div></div>
      </div>

      <div className="card">
        <div className="card-header flex items-center justify-between">
          <h2 className="card-title">District Comparison</h2>
          <button type="button" className="btn btn-secondary btn-sm" onClick={() => window.print()}>
            Export / Print
          </button>
        </div>
        <div className="card-body overflow-x-auto">
          <table className="data-table w-full">
            <thead>
              <tr>
                <th>District</th>
                <th>Schools</th>
                <th>Students</th>
                <th>Verified (Rs)</th>
                <th>Pending</th>
                <th>Missing Forms</th>
              </tr>
            </thead>
            <tbody>
              {districtBreakdown.map((d) => (
                <tr key={d.id}>
                  <td className="font-medium">{d.name}</td>
                  <td>{d.school_count}</td>
                  <td>{d.student_count?.toLocaleString()}</td>
                  <td>{d.verified_amount?.toLocaleString()}</td>
                  <td>{d.pending_invoices}</td>
                  <td>{d.missing_exam_forms}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
