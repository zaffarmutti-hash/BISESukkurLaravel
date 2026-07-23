import { usePage } from '@inertiajs/react';
import SchoolLayout from '@/layouts/SchoolLayout';
import type { AcademicYear } from '@/types';
import type { PageProps } from '@inertiajs/core';

// ─── Types ────────────────────────────────────────────────────────────────────

interface SchoolStats {
  ssc_students?: number;
  hsc_students?: number;
  total_enrolled?: number;
  total_challans?: number;
  awaiting_enrollment_no?: number;
}

interface DashboardProps {
  stats: SchoolStats;
  allowedLevels?: ('matric' | 'intermediate')[];
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatNum(n: number | null | undefined): string {
  return Number(n ?? 0).toLocaleString();
}

// ─── Component ────────────────────────────────────────────────────────────────

/**
 * School Admin Dashboard
 * File: resources/js/pages/school/Dashboard.tsx
 */
export default function Dashboard({
  stats,
  allowedLevels = ['matric', 'intermediate'],
}: DashboardProps) {
  const { props: rawProps } = usePage<PageProps>();
  const props = rawProps as {
    auth: { user: { school?: { name: string } | null } };
    activeYear?: AcademicYear | null;
  };

  const activeYear = props.activeYear;
  const hasHsc     = allowedLevels?.includes('intermediate') ?? true;

  return (
    <SchoolLayout
      breadcrumb={
        <nav className="flex items-center gap-1.5 text-sm">
          <span className="text-gray-400">School</span>
          <span className="text-gray-300">/</span>
          <span className="text-gray-700 font-medium">Dashboard</span>
        </nav>
      }
    >
      {/* ── Page Header ─────────────────────────────────────────────────── */}
      <div className="db-page-header">
        <div>
          <h1 className="db-page-title">
            {props.auth.user?.school?.name ?? 'School Dashboard'}
          </h1>
          <p className="db-page-subtitle">
            Academic Year: <strong>{activeYear?.label ?? 'No active year'}</strong>
          </p>
        </div>

        {/* Window status pills */}
        <div className="db-window-pills">
          {(() => {
            const ep = activeYear?.enrollment_phase;
            const isGrace = ep?.phase === 'grace';
            const isOpen = ep?.phase === 'normal';
            let pillClass = 'db-pill-gray';
            let pillLabel = 'Enrollment Closed';
            if (isOpen) {
              pillClass = 'db-pill-green';
              pillLabel = 'Enrollment Open';
            } else if (isGrace) {
              pillClass = 'db-pill-amber';
              pillLabel = ep.is_exception_based ? 'Enrollment (Extension)' : 'Enrollment (Late Fee)';
            }
            return (
              <div className={`db-pill ${pillClass}`}>
                <span className="db-pill-dot" />
                {pillLabel}
              </div>
            );
          })()}

          {(() => {
            const xp = activeYear?.examination_phase;
            const isGrace = xp?.phase === 'grace';
            const isOpen = xp?.phase === 'normal';
            let pillClass = 'db-pill-gray';
            let pillLabel = 'Exam Closed';
            if (isOpen) {
              pillClass = 'db-pill-green';
              pillLabel = 'Exam Open';
            } else if (isGrace) {
              pillClass = 'db-pill-amber';
              pillLabel = xp.is_exception_based ? 'Exam (Extension)' : 'Exam (Late Fee)';
            }
            return (
              <div className={`db-pill ${pillClass}`}>
                <span className="db-pill-dot" />
                {pillLabel}
              </div>
            );
          })()}
        </div>
      </div>

      {/* Grace Phase Surcharge Warning Banner */}
      {((activeYear?.enrollment_phase?.phase === 'grace') || (activeYear?.examination_phase?.phase === 'grace')) && (
        <div className="db-grace-warning">
          <div className="db-grace-warning-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div>
            <h3 className="db-grace-warning-title">Late Fee Phase Active</h3>
            <p className="db-grace-warning-text">
              {activeYear?.enrollment_phase?.phase === 'grace' && 'The enrollment window is currently in the grace period. New enrollment invoices will automatically include the late fee surcharge. '}
              {activeYear?.examination_phase?.phase === 'grace' && 'The examination window is currently in the grace period. New exam challans generated will include the late fee surcharge.'}
            </p>
          </div>
        </div>
      )}

      {/* ── Stat Cards Grid ──────────────────────────────────────────────── */}
      <div className="db-cards-grid">

        {/* Card 1: SSC Students */}
        <a href={route('school.students.index')} className="db-stat-card db-card-blue">
          <div className="db-card-top">
            <div className="db-card-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
              </svg>
            </div>
            <span className="db-card-label">SSC Students</span>
          </div>
          <div className="db-card-number">{formatNum(stats.ssc_students ?? stats.total_enrolled)}</div>
          <div className="db-card-footer">
            <span>Matric Level</span>
            <svg className="db-card-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
          <div className="db-card-circle" />
        </a>

        {/* Card 2: HSC Students */}
        {hasHsc && (
          <a href={route('school.students.index')} className="db-stat-card db-card-orange">
            <div className="db-card-top">
              <div className="db-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
              </div>
              <span className="db-card-label">HSC Students</span>
            </div>
            <div className="db-card-number">{formatNum(stats.hsc_students)}</div>
            <div className="db-card-footer">
              <span>Intermediate Level</span>
              <svg className="db-card-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </div>
            <div className="db-card-circle" />
          </a>
        )}

        {/* Card 3: Total Invoices */}
        <a href={route('school.enrollment.challans')} className="db-stat-card db-card-green">
          <div className="db-card-top">
            <div className="db-card-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
              </svg>
            </div>
            <span className="db-card-label">Total Invoices</span>
          </div>
          <div className="db-card-number">{formatNum(stats.total_challans)}</div>
          <div className="db-card-footer">
            <span>All challans</span>
            <svg className="db-card-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
          <div className="db-card-circle" />
        </a>

        {/* Card 4: Pending Enrollment Numbers */}
        <a
          href={route('school.enrollment.challans')}
          className={`db-stat-card db-card-red${(stats.awaiting_enrollment_no ?? 0) > 0 ? ' db-card-pulse' : ''}`}
        >
          <div className="db-card-top">
            <div className="db-card-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
            </div>
            <span className="db-card-label">Pending Enrollment Nos.</span>
          </div>
          <div className="db-card-number">{formatNum(stats.awaiting_enrollment_no)}</div>
          <div className="db-card-footer">
            <span>Awaiting board assignment</span>
            <svg className="db-card-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
          <div className="db-card-circle" />
        </a>
      </div>

      {/* Scoped CSS */}
      <style>{`
        .db-page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.75rem;flex-wrap:wrap}
        .db-page-title{font-size:1.55rem;font-weight:800;color:#0F172A;line-height:1.2}
        .db-page-subtitle{font-size:.92rem;color:#64748B;margin-top:.35rem}
        .db-window-pills{display:flex;gap:.625rem;flex-wrap:wrap}
        .db-pill{display:flex;align-items:center;gap:.375rem;font-size:.76rem;font-weight:700;padding:.45rem .9rem;border-radius:9999px;box-shadow:0 8px 20px rgba(148,163,184,.12)}
        .db-pill-green{background:#DCFCE7;color:#166534}
        .db-pill-amber{background:#FEF3C7;color:#92400E}
        .db-pill-gray{background:#FFFFFF;color:#64748B;border:1px solid #E2E8F0}
        .db-pill-dot{width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.7}
        .db-grace-warning{display:flex;align-items:center;gap:1rem;background:linear-gradient(135deg,#FFFBEB 0%,#FEF3C7 100%);border:1px solid #FCD34D;border-radius:18px;padding:1.15rem;margin-bottom:1.5rem;box-shadow:0 10px 25px rgba(245,158,11,.08);animation:db-fade-in .5s ease both}
        .db-grace-warning-icon{width:40px;height:40px;background:#F59E0B;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0}
        .db-grace-warning-icon svg{width:20px;height:20px;stroke:#fff}
        .db-grace-warning-title{font-size:.95rem;font-weight:800;color:#78350F;margin:0}
        .db-grace-warning-text{font-size:.82rem;color:#92400E;margin-top:.15rem;line-height:1.45}
        .db-cards-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1.25rem;margin-bottom:1.5rem}
        .db-stat-card{border-radius:24px;padding:1.5rem 1.5rem 1.25rem;color:#fff;text-decoration:none;display:flex;flex-direction:column;gap:.55rem;cursor:pointer;transition:transform .2s ease,box-shadow .2s ease;position:relative;overflow:hidden;animation:db-fade-in .5s ease both;box-shadow:0 20px 45px rgba(79,70,229,.14)}
        .db-stat-card:hover{transform:translateY(-4px);box-shadow:0 22px 46px rgba(15,23,42,.18)}
        .db-card-blue{background:linear-gradient(135deg,#4F7CFF 0%,#6D9DFF 100%)}
        .db-card-orange{background:linear-gradient(135deg,#9B8CFF 0%,#7C6CFF 100%)}
        .db-card-green{background:linear-gradient(135deg,#14B8A6 0%,#0EA5E9 100%)}
        .db-card-red{background:linear-gradient(135deg,#F97316 0%,#FB7185 100%)}
        .db-card-pulse{animation:db-fade-in .5s ease both,db-pulse 2.5s ease-in-out infinite}
        @keyframes db-pulse{0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,0)}50%{box-shadow:0 0 0 8px rgba(239,68,68,.25)}}
        @keyframes db-fade-in{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        .db-card-top{display:flex;align-items:center;gap:.65rem}
        .db-card-icon{width:40px;height:40px;background:rgba(255,255,255,.16);backdrop-filter:blur(6px);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:inset 0 1px 0 rgba(255,255,255,.22)}
        .db-card-icon svg{width:18px;height:18px;stroke:#fff}
        .db-card-label{font-size:.78rem;font-weight:700;opacity:.92;text-transform:uppercase;letter-spacing:.08em}
        .db-card-number{font-size:2.8rem;font-weight:800;line-height:1;letter-spacing:-.03em;margin:.45rem 0 .25rem}
        .db-card-footer{display:flex;align-items:center;justify-content:space-between;font-size:.8rem;opacity:.84;margin-top:auto}
        .db-card-arrow{width:14px;height:14px;stroke:currentColor;stroke-width:2;opacity:.75}
        .db-card-circle{position:absolute;bottom:-18px;right:-14px;width:130px;height:130px;border-radius:50%;background:rgba(255,255,255,.1);pointer-events:none}
        @media(max-width:1100px){.db-cards-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:640px){.db-cards-grid{grid-template-columns:1fr}}
      `}</style>
    </SchoolLayout>
  );
}
