import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { NavItem } from '@/components/shared/NavItem';
import { AppAlert } from '@/components/shared/AppAlert';

interface AdminLayoutProps {
  children: React.ReactNode;
  breadcrumb?: React.ReactNode;
  stats?: {
    pending_enrollment_challans?: number;
    pending_exam_challans?: number;
  };
}

/**
 * Admin sidebar layout — Deep Navy + Amber Gold theme.
 * Drop-in React replacement for AdminLayout.vue.
 */
export default function AdminLayout({ children, breadcrumb, stats }: AdminLayoutProps) {
  const page = usePage();
  const props = page.props as unknown as {
    auth: { user: { name: string } };
    flash: { success?: string; error?: string; warning?: string; info?: string };
    activeYear?: { label: string } | null;
  };

  function logout() {
    router.post(route('logout'));
  }

  return (
    <div className="theme-admin app-shell">
      {/* ── Sidebar ─────────────────────────────────────────────────────── */}
      <aside className="app-sidebar">
        <div className="app-sidebar-header">
          <Link href={route('admin.dashboard')} className="app-sidebar-logo">
            <img src="/images/bise-sukkur-logo.png" alt="BISE Sukkur Logo" className="app-sidebar-logo-image" />
            <div className="app-sidebar-logo-text">
              <span className="app-sidebar-logo-title">BISE Sukkur</span>
              <span className="app-sidebar-logo-subtitle">Admin Console</span>
            </div>
          </Link>
        </div>

        <nav className="app-nav">
          {/* Overview */}
          <div className="app-nav-section">
            <NavItem href={route('admin.dashboard')} icon="grid" label="Dashboard" />
          </div>

          {/* Enrollment */}
          <div className="app-nav-section">
            <p className="app-nav-section-label">Enrollment</p>
            <NavItem href={route('admin.years.index')} icon="calendar" label="Academic Years" />
            <NavItem href={route('admin.fees.index')} icon="currency" label="Fee Structures" />
            <NavItem
              href={route('admin.enrollment.challans')}
              icon="check-circle"
              label="Pending Payments"
              badge={stats?.pending_enrollment_challans}
            />
            <NavItem href={route('admin.enrollment.reports')} icon="chart-bar" label="Enrollment Reports" />
          </div>

          {/* Examination */}
          <div className="app-nav-section">
            <p className="app-nav-section-label">Examination</p>
            <NavItem
              href={route('admin.examination.challans')}
              icon="check-circle"
              label="Exam Payments"
              badge={stats?.pending_exam_challans}
            />
            <NavItem href={route('admin.examination.centers')} icon="building" label="Exam Centers" />
            <NavItem href={route('admin.examination.timetable')} icon="clock" label="Timetable" />
            <NavItem href={route('admin.examination.seats.assign')} icon="user-group" label="Seat Assignment" />
            <NavItem href={route('admin.examination.results.index')} icon="pencil" label="Results Entry" />
            <NavItem href={route('admin.examination.certificates.generate')} icon="badge-check" label="Certificates" />
          </div>

          {/* System */}
          <div className="app-nav-section">
            <p className="app-nav-section-label">System</p>
            <NavItem href={route('admin.schools.index')} icon="library" label="Schools" />
            <NavItem href={route('admin.exceptions.index')} icon="shield-exclamation" label="Exceptions" />
            <NavItem href={route('admin.reports.audit')} icon="document-text" label="Audit Log" />
          </div>
        </nav>

        {/* Sidebar Footer */}
        <div className="p-4 border-t border-white/10">
          {props.activeYear ? (
            <div className="text-xs text-amber-400 font-semibold mb-2 opacity-90">
              ⬤ Active Year: {props.activeYear.label}
            </div>
          ) : (
            <div className="text-xs text-red-400 font-semibold mb-2">⚠ No Active Year Set</div>
          )}
          <button
            type="button"
            onClick={logout}
            className="app-nav-item w-full text-left opacity-70 hover:opacity-100"
          >
            <span className="nav-icon">↩</span> Logout
          </button>
        </div>
      </aside>

      {/* ── Main Content ─────────────────────────────────────────────────── */}
      <div className="app-main">
        <header className="app-topbar">
          <div className="flex-1">{breadcrumb}</div>
          <div className="flex items-center gap-3">
            {props.flash.success && (
              <div className="text-sm text-emerald-600 font-medium">✓ {props.flash.success}</div>
            )}
            {props.flash.error && (
              <div className="text-sm text-red-600 font-medium">✗ {props.flash.error}</div>
            )}
            <span className="text-sm text-gray-500 font-medium">{props.auth.user?.name}</span>
          </div>
        </header>

        <main className="app-content">
          {props.flash.warning && (
            <AppAlert type="warning" message={props.flash.warning} className="mb-4" />
          )}
          {props.flash.info && (
            <AppAlert type="info" message={props.flash.info} className="mb-4" />
          )}
          {children}
        </main>
      </div>
    </div>
  );
}
