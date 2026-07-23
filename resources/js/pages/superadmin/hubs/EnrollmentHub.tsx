import { Link } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { WindowStatusBar } from '@/components/shared/WindowStatusBar';
import { GradientStatCard } from '@/components/shared/GradientStatCard';
import type { AcademicYear } from '@/types';

interface HubStats {
  pending_verification: number;
  pending_enrollment_numbers: number;
  verified_payments: number;
  total_students: number;
}

interface ExceptionRow {
  id: number;
  exception_type: string;
  reason: string;
  school?: { name: string; code: string };
  granted_by?: { name: string };
}

interface EnrollmentHubProps {
  activeYear?: AcademicYear | null;
  stats: HubStats;
  active_exceptions: ExceptionRow[];
}

export default function EnrollmentHub({ activeYear, stats, active_exceptions }: EnrollmentHubProps) {
  const year = activeYear as AcademicYear & { enrollment_window_open?: boolean; examination_window_open?: boolean };

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Enrollment Hub</span>}>
      <PageHeader title="Enrollment Hub" subtitle="Window control, fees, verification, allotment, and enrollment reports" />

      <WindowStatusBar
        activeYearLabel={year?.label}
        enrollmentOpen={year?.enrollment_window_open ?? year?.is_enrollment_open}
        examinationOpen={year?.examination_window_open ?? year?.is_examination_open}
        enrollmentPhase={year?.enrollment_phase}
        examinationPhase={year?.examination_phase}
        enrollmentHref={route('superadmin.settings.enrollment-windows')}
      />

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <GradientStatCard label="Pending Verification" value={stats.pending_verification} gradient={stats.pending_verification > 0 ? 'hot' : 'cyan'} href={route('superadmin.invoice-verification')} />
        <GradientStatCard label="Missing Enrollment Nos." value={stats.pending_enrollment_numbers} gradient={stats.pending_enrollment_numbers > 0 ? 'red' : 'orange'} href={route('superadmin.allotment-history')} />
        <GradientStatCard label="Verified Fees (Rs)" value={stats.verified_payments} gradient="green" href={route('superadmin.reports.fee-collection')} />
        <GradientStatCard label="Students Enrolled" value={stats.total_students} gradient="purple" href={route('superadmin.reports.enrollment')} />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="card">
          <div className="card-header"><h2 className="card-title">Hub Navigation</h2></div>
          <div className="card-body grid grid-cols-1 sm:grid-cols-2 gap-2">
            <Link href={route('superadmin.settings.enrollment-windows')} className="btn btn-secondary text-center">Window Status</Link>
            <Link href={route('superadmin.fee-rates.index')} className="btn btn-secondary text-center">Fee Configuration</Link>
            <Link href={route('superadmin.invoice-verification')} className="btn btn-secondary text-center">Payment Verification</Link>
            <Link href={route('superadmin.allotment-history')} className="btn btn-secondary text-center">Allotment History</Link>
            <Link href={route('superadmin.special-permissions.index')} className="btn btn-secondary text-center">Special Permissions</Link>
            <Link href={route('superadmin.reports.enrollment')} className="btn btn-secondary text-center">Enrollment Reports</Link>
          </div>
        </div>

        <div className="card">
          <div className="card-header"><h2 className="card-title">Active Enrollment Exceptions</h2></div>
          <div className="card-body">
            <ul className="divide-y divide-gray-100 text-sm">
              {active_exceptions.map((ex) => (
                <li key={ex.id} className="py-2">
                  <span className="font-medium">{ex.school?.name}</span>
                  <span className="text-gray-400 ml-2">{ex.exception_type.replace(/_/g, ' ')}</span>
                </li>
              ))}
              {!active_exceptions.length && <li className="py-4 text-gray-400">No active enrollment exceptions.</li>}
            </ul>
          </div>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
