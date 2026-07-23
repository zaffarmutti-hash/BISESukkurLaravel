import { Link } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { WindowStatusBar } from '@/components/shared/WindowStatusBar';
import { GradientStatCard } from '@/components/shared/GradientStatCard';
import type { AcademicYear } from '@/types';

interface HubStats {
  pending_verification: number;
  missing_exam_forms: number;
  total_students: number;
}

interface ExceptionRow {
  id: number;
  exception_type: string;
  school?: { name: string; code: string };
}

interface ExamHubProps {
  activeYear?: AcademicYear | null;
  stats: HubStats;
  active_exceptions: ExceptionRow[];
}

export default function ExamHub({ activeYear, stats, active_exceptions }: ExamHubProps) {
  const year = activeYear as AcademicYear & { enrollment_window_open?: boolean; examination_window_open?: boolean };

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Examination Hub</span>}>
      <PageHeader title="Examination Hub" subtitle="Exam windows, payments, centers, timetable, results, and certificates" />

      <WindowStatusBar
        activeYearLabel={year?.label}
        enrollmentOpen={year?.enrollment_window_open ?? year?.is_enrollment_open}
        examinationOpen={year?.examination_window_open ?? year?.is_examination_open}
        enrollmentPhase={year?.enrollment_phase}
        examinationPhase={year?.examination_phase}
        examinationHref={route('superadmin.settings.exam-windows')}
      />

      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <GradientStatCard label="Exam Payments Pending" value={stats.pending_verification} gradient={stats.pending_verification > 0 ? 'hot' : 'cyan'} href={route('superadmin.exam-invoice-verification')} />
        <GradientStatCard label="Missing Exam Forms" value={stats.missing_exam_forms} gradient={stats.missing_exam_forms > 0 ? 'red' : 'mint'} href={route('superadmin.reports.gap')} />
        <GradientStatCard label="Enrolled Students" value={stats.total_students} gradient="purple" href={route('superadmin.reports.gap')} />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="card">
          <div className="card-header"><h2 className="card-title">Hub Navigation</h2></div>
          <div className="card-body grid grid-cols-1 sm:grid-cols-2 gap-2">
            <Link href={route('superadmin.settings.exam-windows')} className="btn btn-secondary text-center">Window Status</Link>
            <Link href={route('superadmin.exam-invoice-verification')} className="btn btn-secondary text-center">Exam Payments</Link>
            <Link href={route('superadmin.examination.centers.index')} className="btn btn-secondary text-center">Exam Centers</Link>
            <Link href={route('superadmin.examination.timetable.index')} className="btn btn-secondary text-center">Timetable</Link>
            <Link href={route('superadmin.examination.results.index')} className="btn btn-secondary text-center">Result Entry</Link>
            <Link href={route('superadmin.reports.gap')} className="btn btn-secondary text-center">Exam Reports</Link>
          </div>
        </div>

        <div className="card">
          <div className="card-header"><h2 className="card-title">Active Exam Exceptions</h2></div>
          <div className="card-body">
            <ul className="divide-y divide-gray-100 text-sm">
              {active_exceptions.map((ex) => (
                <li key={ex.id} className="py-2">
                  <span className="font-medium">{ex.school?.name}</span>
                  <span className="text-gray-400 ml-2">Extended deadline</span>
                </li>
              ))}
              {!active_exceptions.length && <li className="py-4 text-gray-400">No active examination exceptions.</li>}
            </ul>
          </div>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
