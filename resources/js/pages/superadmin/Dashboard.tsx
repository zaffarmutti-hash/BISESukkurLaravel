import { Link, Deferred } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { GradientStatCard } from '@/components/shared/GradientStatCard';
import { WindowStatusBar } from '@/components/shared/WindowStatusBar';
import type { District, ActivityLog, AcademicYear } from '@/types';
import { usePermissions } from '@/hooks/usePermissions';

interface SchoolStats {
  total: number;
  active: number;
  inactive: number;
}

interface DashboardProps {
  activeYear?: AcademicYear | null;
  totalStudents?: number;
  schoolStats?: SchoolStats;
  pendingEnrollmentVerification?: number;
  pendingExamVerification?: number;
  verifiedPayments?: number;
  pendingEnrollmentNumbers?: number;
  missingExamForms?: number;
  districtBreakdown?: District[];
  recentActivity?: ActivityLog[];
}

export default function Dashboard({
  activeYear,
  totalStudents,
  schoolStats,
  pendingEnrollmentVerification,
  pendingExamVerification,
  verifiedPayments,
  pendingEnrollmentNumbers,
  missingExamForms,
  districtBreakdown,
  recentActivity,
}: DashboardProps) {
  const year = activeYear as (AcademicYear & {
    enrollment_window_open?: boolean;
    examination_window_open?: boolean;
  }) | null | undefined;

  const { can } = usePermissions();

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Overview / Command Center</span>}>
      <PageHeader
        title="Command Center"
        subtitle="What across the entire province needs your attention right now"
      />

      <WindowStatusBar
        activeYearLabel={year?.label}
        enrollmentOpen={year?.enrollment_window_open ?? year?.is_enrollment_open}
        examinationOpen={year?.examination_window_open ?? year?.is_examination_open}
        enrollmentHref={route('superadmin.settings.enrollment-windows')}
        examinationHref={route('superadmin.settings.exam-windows')}
      />

      <div className="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <div className="xl:col-span-3 space-y-6">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <Deferred data="totalStudents" fallback={<GradientStatCard label="Students Enrolled" loading gradient="purple" />}>
              <GradientStatCard label="Students Enrolled" value={totalStudents} gradient="purple" href={route('superadmin.reports.enrollment')} />
            </Deferred>

            <Deferred data="schoolStats" fallback={<GradientStatCard label="Registered Schools" loading gradient="pink" />}>
              <GradientStatCard
                label="Schools (Active / Total)"
                value={schoolStats ? `${schoolStats.active} / ${schoolStats.total}` : undefined}
                gradient="pink"
                href={route('superadmin.schools')}
              />
            </Deferred>

            <Deferred data="pendingEnrollmentVerification" fallback={<GradientStatCard label="Enrollment Payments Pending" loading gradient="cyan" />}>
              <GradientStatCard
                label="Enrollment Payments Pending"
                value={pendingEnrollmentVerification}
                gradient={(pendingEnrollmentVerification ?? 0) > 0 ? 'hot' : 'cyan'}
                href={route('superadmin.invoice-verification')}
              />
            </Deferred>

            <Deferred data="pendingExamVerification" fallback={<GradientStatCard label="Exam Payments Pending" loading gradient="orange" />}>
              <GradientStatCard
                label="Exam Payments Pending"
                value={pendingExamVerification}
                gradient={(pendingExamVerification ?? 0) > 0 ? 'hot' : 'orange'}
                href={route('superadmin.exam-invoice-verification')}
              />
            </Deferred>

            <Deferred data="pendingEnrollmentNumbers" fallback={<GradientStatCard label="Missing Enrollment Nos." loading gradient="red" />}>
              <GradientStatCard
                label="Missing Enrollment Nos."
                value={pendingEnrollmentNumbers}
                gradient={(pendingEnrollmentNumbers ?? 0) > 0 ? 'red' : 'mint'}
                href={route('superadmin.allotment-history')}
              />
            </Deferred>

            <Deferred data="missingExamForms" fallback={<GradientStatCard label="No Exam Form Started" loading gradient="mint" />}>
              <GradientStatCard
                label="No Exam Form Started"
                value={missingExamForms}
                gradient={(missingExamForms ?? 0) > 0 ? 'red' : 'mint'}
                href={route('superadmin.reports.gap')}
              />
            </Deferred>

            <Deferred data="verifiedPayments" fallback={<GradientStatCard label="Verified Fees (Rs)" loading gradient="green" />}>
              <GradientStatCard label="Verified Fees (Rs)" value={verifiedPayments} gradient="green" href={route('superadmin.reports.fee-collection')} />
            </Deferred>
          </div>

          <div className="card">
            <div className="card-header">
              <h2 className="card-title">District Breakdown</h2>
            </div>
            <div className="card-body overflow-x-auto">
              <Deferred data="districtBreakdown" fallback={<p className="text-gray-400 text-sm">Loading district data…</p>}>
                <table className="data-table w-full text-sm">
                  <thead>
                    <tr>
                      <th>District</th>
                      <th>Schools</th>
                      <th>Students</th>
                      <th>Verified (Rs)</th>
                      <th>Pending</th>
                      <th>Missing Forms</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    {districtBreakdown?.map((d) => (
                      <tr key={d.id}>
                        <td className="font-medium">{d.name}</td>
                        <td>{d.school_count}</td>
                        <td>{d.student_count?.toLocaleString()}</td>
                        <td>{d.verified_amount?.toLocaleString()}</td>
                        <td>{d.pending_invoices}</td>
                        <td>{d.missing_exam_forms}</td>
                        <td>
                          <Link href={route('superadmin.districts', { district_id: d.id })} className="text-amber-600 text-xs font-semibold whitespace-nowrap">
                            View →
                          </Link>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </Deferred>
            </div>
          </div>

          <div className="card">
            <div className="card-header flex items-center justify-between">
              <h2 className="card-title">Recent Activity</h2>
              <Link href={route('superadmin.activity-log')} className="text-xs font-semibold text-amber-600">
                Full audit trail →
              </Link>
            </div>
            <div className="card-body">
              <Deferred data="recentActivity" fallback={<p className="text-gray-400 text-sm">Loading activity…</p>}>
                <ul className="divide-y divide-gray-100">
                  {recentActivity?.map((item) => (
                    <li key={item.id} className="py-3 flex flex-col sm:flex-row sm:justify-between gap-1 text-sm">
                      <div>
                        <span className="font-medium text-gray-800">{item.description}</span>
                        <span className="text-gray-400 ml-2">by {item.user}</span>
                      </div>
                      <span className="text-gray-400 whitespace-nowrap text-xs">{item.created_at}</span>
                    </li>
                  ))}
                  {!recentActivity?.length && (
                    <li className="py-4 text-sm text-gray-400">No recent administrative activity.</li>
                  )}
                </ul>
              </Deferred>
            </div>
          </div>
        </div>

        <div className="space-y-4">
          <div className="card">
            <div className="card-header">
              <h2 className="card-title">Quick Actions</h2>
            </div>
            <div className="card-body space-y-2">
              {can('invoice.verify') && (
                <Link href={route('superadmin.invoice-verification')} className="btn btn-primary w-full text-center block">
                  Verify Enrollment Payments
                </Link>
              )}
              {can('allotment.history') && (
                <Link href={route('superadmin.allotment-history')} className="btn btn-secondary w-full text-center block">
                  Run Enrollment Allotment
                </Link>
              )}
              {can('academicyear.create_next') && (
                <Link href={route('superadmin.academic-year-transition')} className="btn btn-secondary w-full text-center block">
                  Academic Year Transition
                </Link>
              )}
              {can('settings.notifications') && (
                <Link href={route('superadmin.announcements')} className="btn btn-secondary w-full text-center block">
                  Send Announcement
                </Link>
              )}
            </div>
          </div>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
