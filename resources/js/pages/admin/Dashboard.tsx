import { Link } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { GradientStatCard } from '@/components/shared/GradientStatCard';
import { WindowStatusBar } from '@/components/shared/WindowStatusBar';
import type { AcademicYear } from '@/types';

interface AdminStats {
  active_year?: string;
  total_students_enrolled?: number;
  students_by_district?: { district: string; count: number }[];
  pending_enrollment_challans?: number;
  pending_exam_challans?: number;
  students_awaiting_enroll_no?: number;
  students_without_exam_form?: number;
  total_schools?: number;
}

interface DashboardProps {
  stats: AdminStats;
  activeYear?: AcademicYear | null;
}

export default function Dashboard({ stats, activeYear }: DashboardProps) {
  const year = activeYear as (AcademicYear & {
    enrollment_window_open?: boolean;
    examination_window_open?: boolean;
  }) | null | undefined;

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Admin / Dashboard</span>}>
      <PageHeader
        title="Admin Control Center"
        subtitle="Board administration, enrollment oversight, and examination management"
      />

      <WindowStatusBar
        activeYearLabel={activeYear?.label ?? stats.active_year}
        enrollmentOpen={year?.enrollment_window_open ?? year?.is_enrollment_open}
        examinationOpen={year?.examination_window_open ?? year?.is_examination_open}
        enrollmentHref={route('superadmin.settings.enrollment-windows')}
        examinationHref={route('superadmin.settings.exam-windows')}
      />

      {/* Stat Cards Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <GradientStatCard
          label="Total Students Enrolled"
          value={stats.total_students_enrolled ?? 0}
          gradient="purple"
          href={route('superadmin.reports.enrollment')}
        />

        <GradientStatCard
          label="Registered Schools"
          value={stats.total_schools ?? 0}
          gradient="pink"
          href={route('superadmin.schools')}
        />

        <GradientStatCard
          label="Pending Enrollment Verification"
          value={stats.pending_enrollment_challans ?? 0}
          gradient={(stats.pending_enrollment_challans ?? 0) > 0 ? 'hot' : 'cyan'}
          href={route('superadmin.invoice-verification')}
        />

        <GradientStatCard
          label="Pending Exam Verification"
          value={stats.pending_exam_challans ?? 0}
          gradient={(stats.pending_exam_challans ?? 0) > 0 ? 'orange' : 'mint'}
          href={route('superadmin.exam-invoice-verification')}
        />
      </div>

      {/* Secondary Cards & Quick Actions */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 space-y-6">
          <div className="card">
            <div className="card-header">
              <h2 className="card-title">Attention Required</h2>
            </div>
            <div className="card-body">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Link
                  href={route('superadmin.allotment-history')}
                  className="p-4 rounded-xl border border-amber-200 bg-amber-50/50 hover:bg-amber-50 transition-colors flex items-center justify-between"
                >
                  <div>
                    <div className="text-sm font-bold text-slate-800">Awaiting Enrollment Nos</div>
                    <div className="text-xs text-slate-500 mt-0.5">Students awaiting board assignment</div>
                  </div>
                  <span className="px-3 py-1 bg-amber-500 text-white font-extrabold text-sm rounded-full">
                    {stats.students_awaiting_enroll_no ?? 0}
                  </span>
                </Link>

                <Link
                  href={route('superadmin.reports.gap')}
                  className="p-4 rounded-xl border border-rose-200 bg-rose-50/50 hover:bg-rose-50 transition-colors flex items-center justify-between"
                >
                  <div>
                    <div className="text-sm font-bold text-slate-800">Missing Exam Forms</div>
                    <div className="text-xs text-slate-500 mt-0.5">Enrolled but no exam form started</div>
                  </div>
                  <span className="px-3 py-1 bg-rose-500 text-white font-extrabold text-sm rounded-full">
                    {stats.students_without_exam_form ?? 0}
                  </span>
                </Link>
              </div>
            </div>
          </div>

          <div className="card">
            <div className="card-header flex items-center justify-between">
              <h2 className="card-title">Enrollment Overview</h2>
              <Link href={route('superadmin.reports.enrollment')} className="text-xs font-semibold text-amber-600 hover:underline">
                View Detailed Report →
              </Link>
            </div>
            <div className="card-body">
              <p className="text-sm text-slate-600">
                Active Academic Year: <strong>{activeYear?.label ?? stats.active_year ?? 'Not Set'}</strong>. All enrollment numbers and exam form allotments are verified server-side.
              </p>
            </div>
          </div>
        </div>

        <div>
          <div className="card">
            <div className="card-header">
              <h2 className="card-title">Quick Actions</h2>
            </div>
            <div className="card-body space-y-2.5">
              <Link href={route('superadmin.invoice-verification')} className="btn btn-primary w-full justify-center text-center block">
                Verify Enrollment Payments
              </Link>
              <Link href={route('superadmin.exam-invoice-verification')} className="btn btn-secondary w-full justify-center text-center block">
                Verify Exam Payments
              </Link>
              <Link href={route('superadmin.allotment-history')} className="btn btn-secondary w-full justify-center text-center block">
                Generate Enrollment Numbers
              </Link>
              <Link href={route('superadmin.schools')} className="btn btn-secondary w-full justify-center text-center block">
                Manage Schools
              </Link>
            </div>
          </div>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
