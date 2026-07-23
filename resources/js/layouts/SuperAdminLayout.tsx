import React, { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { NavItem } from '@/components/shared/NavItem';
import { AppAlert } from '@/components/shared/AppAlert';
import { usePermissions } from '@/hooks/usePermissions';

interface SuperAdminLayoutProps {
  children: React.ReactNode;
  breadcrumb?: React.ReactNode;
}

export default function SuperAdminLayout({ children, breadcrumb }: SuperAdminLayoutProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const page = usePage();
  const props = page.props as unknown as {
    auth: { user: { name: string; username: string } };
    flash: { success?: string; error?: string; warning?: string };
    activeYear?: {
      label: string;
      enrollment_window_open?: boolean;
      examination_window_open?: boolean;
    } | null;
  };

  const { can } = usePermissions();

  function logout() {
    router.post(route('logout'));
  }

  function closeSidebar() {
    setSidebarOpen(false);
  }

  const nav = (
    <>
      {/* Overview */}
      <div className="app-nav-section">
        <p className="app-nav-section-label">Overview</p>
        <NavItem href={route('superadmin.dashboard')} icon="grid" label="Command Center" onClick={closeSidebar} />
        <NavItem href={route('superadmin.health')} icon="heart" label="System Health" onClick={closeSidebar} />
      </div>

      {/* Enrollment Hub */}
      {can(['enrollment.view', 'invoice.verify', 'feerate.view', 'academicyear.manage_windows', 'allotment.history', 'report.enrollment', 'settings.system_settings']) && (
        <div className="app-nav-section">
          <p className="app-nav-section-label">Enrollment Hub</p>
          {can('enrollment.view') && (
            <NavItem href={route('superadmin.enrollment-hub')} icon="clipboard" label="Hub Overview" onClick={closeSidebar} />
          )}
          {can('academicyear.manage_windows') && (
            <NavItem href={route('superadmin.settings.enrollment-windows')} icon="clock" label="Window Status" onClick={closeSidebar} />
          )}
          {can('feerate.view') && (
            <NavItem href={route('superadmin.fee-rates.index')} icon="currency" label="Fee Configuration" onClick={closeSidebar} />
          )}
          {can('invoice.verify') && (
            <NavItem href={route('superadmin.invoice-verification')} icon="check-circle" label="Payment Verification" onClick={closeSidebar} />
          )}
          {can('allotment.history') && (
            <NavItem href={route('superadmin.allotment-history')} icon="chart-bar" label="Allotment History" onClick={closeSidebar} />
          )}
          {can('settings.system_settings') && (
            <NavItem href={route('superadmin.special-permissions.index')} icon="shield" label="Special Permissions" onClick={closeSidebar} />
          )}
          {can('report.enrollment') && (
            <NavItem href={route('superadmin.reports.enrollment')} icon="document-text" label="Enrollment Reports" onClick={closeSidebar} />
          )}
        </div>
      )}

      {/* Examination Hub */}
      {can(['examination.view', 'invoice.verify', 'academicyear.manage_windows', 'report.missing_examforms']) && (
        <div className="app-nav-section">
          <p className="app-nav-section-label">Examination Hub</p>
          {can('examination.view') && (
            <NavItem href={route('superadmin.examination-hub')} icon="academic-cap" label="Hub Overview" onClick={closeSidebar} />
          )}
          {can('academicyear.manage_windows') && (
            <NavItem href={route('superadmin.settings.exam-windows')} icon="clock" label="Window Status" onClick={closeSidebar} />
          )}
          {can('invoice.verify') && (
            <NavItem href={route('superadmin.exam-invoice-verification')} icon="check-circle" label="Exam Payments" onClick={closeSidebar} />
          )}
          {can('examination.view') && (
            <>
              <NavItem href={route('superadmin.examination.centers.index')} icon="building" label="Exam Centers" onClick={closeSidebar} />
              <NavItem href={route('superadmin.examination.timetable.index')} icon="calendar" label="Timetable" onClick={closeSidebar} />
              <NavItem href={route('superadmin.examination.seat-allotment.index')} icon="users" label="Seat Allotment" onClick={closeSidebar} />
              <NavItem href={route('superadmin.examination.results.index')} icon="pencil" label="Result Entry" onClick={closeSidebar} />
              <NavItem href={route('superadmin.examination.certificates.index')} icon="clipboard" label="Certificates" onClick={closeSidebar} />
            </>
          )}
          {can('report.missing_examforms') && (
            <NavItem href={route('superadmin.reports.gap')} icon="document-text" label="Exam Reports" onClick={closeSidebar} />
          )}
        </div>
      )}

      {/* Schools and Users */}
      {can(['school.view', 'user.view']) && (
        <div className="app-nav-section">
          <p className="app-nav-section-label">Schools & Users</p>
          {can('school.view') && (
            <>
              <NavItem href={route('superadmin.schools')} icon="library" label="All Schools" onClick={closeSidebar} />
              {can('school.create') && (
                <NavItem href={route('superadmin.schools.create')} icon="plus-circle" label="Register School" onClick={closeSidebar} />
              )}
            </>
          )}
          {can('user.view') && (
            <NavItem href={route('superadmin.users.index')} icon="users" label="User Accounts" onClick={closeSidebar} />
          )}
        </div>
      )}

      {/* Reports and Analytics */}
      {can(['report.enrollment', 'report.fee_collection', 'report.missing_examforms', 'district.view']) && (
        <div className="app-nav-section">
          <p className="app-nav-section-label">Reports & Analytics</p>
          {can('report.enrollment') && (
            <NavItem href={route('superadmin.reports-analytics')} icon="chart-bar" label="Cross-Domain Reports" onClick={closeSidebar} />
          )}
          {can('district.view') && (
            <NavItem href={route('superadmin.districts')} icon="building" label="District Comparison" onClick={closeSidebar} />
          )}
          {can('report.fee_collection') && (
            <NavItem href={route('superadmin.reports.fee-collection')} icon="currency" label="Fee Collection" onClick={closeSidebar} />
          )}
        </div>
      )}

      {/* Academic Year Control */}
      {can(['academicyear.view', 'academicyear.create_next']) && (
        <div className="app-nav-section">
          <p className="app-nav-section-label">Academic Year Control</p>
          {can('academicyear.view') && (
            <NavItem href={route('superadmin.academic-years.index')} icon="calendar" label="Manage Years" onClick={closeSidebar} />
          )}
          {can('academicyear.create_next') && (
            <NavItem href={route('superadmin.academic-year-transition')} icon="refresh" label="Year Transition" onClick={closeSidebar} />
          )}
        </div>
      )}

      {/* System and Audit */}
      <div className="app-nav-section">
        <p className="app-nav-section-label">System & Audit</p>
        {can('activitylog.view') && (
          <NavItem href={route('superadmin.activity-log')} icon="document-text" label="Audit Trail" onClick={closeSidebar} />
        )}
        {can('settings.notifications') && (
          <NavItem href={route('superadmin.announcements')} icon="document-add" label="Announcements" onClick={closeSidebar} />
        )}
        {can('settings.notifications') && (
          <NavItem href={route('superadmin.settings.notifications')} icon="clock" label="Notification Settings" onClick={closeSidebar} />
        )}
        {can('settings.system_settings') && (
          <NavItem href={route('superadmin.settings.system')} icon="dot" label="System Settings" onClick={closeSidebar} />
        )}
      </div>
    </>
  );

  return (
    <div className="theme-admin app-shell">
      <div
        className={`app-sidebar-overlay ${sidebarOpen ? 'open' : ''}`}
        onClick={closeSidebar}
        aria-hidden="true"
      />

      <aside className={`app-sidebar ${sidebarOpen ? 'open' : ''}`} style={{ background: '#0f172a' }}>
        <div className="app-sidebar-header">
          <Link href={route('superadmin.dashboard')} className="app-sidebar-logo" onClick={closeSidebar}>
            <img src="/images/bise-sukkur-logo.png" alt="BISE Sukkur Logo" className="app-sidebar-logo-image" />
            <div className="app-sidebar-logo-text">
              <span className="app-sidebar-logo-title">BISE Sukkur</span>
              <span className="app-sidebar-logo-subtitle">Super Admin</span>
            </div>
          </Link>
        </div>

        <nav className="app-nav">{nav}</nav>

        <div className="p-4 border-t border-white/10 mt-auto">
          {props.activeYear && (
            <div className="text-xs text-amber-400 font-semibold mb-2">
              Active Year: {props.activeYear.label}
            </div>
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

      <div className="app-main">
        <header className="app-topbar sticky top-0 z-30 bg-white border-b border-gray-200">
          <div className="flex items-center gap-3 flex-1 min-w-0">
            <button
              type="button"
              className="app-mobile-toggle"
              onClick={() => setSidebarOpen(true)}
              aria-label="Open navigation"
            >
              ☰
            </button>
            <div className="min-w-0 flex-1">{breadcrumb}</div>
          </div>
          <div className="flex items-center gap-3 shrink-0">
            {props.activeYear && (
              <span className="hidden sm:inline text-xs font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-800">
                {props.activeYear.label}
              </span>
            )}
            {props.flash.success && (
              <div className="hidden md:block text-sm text-emerald-600 font-medium">✓ {props.flash.success}</div>
            )}
            {props.flash.error && (
              <div className="hidden md:block text-sm text-red-600 font-medium">✗ {props.flash.error}</div>
            )}
            <div className="relative hidden sm:block">
              <span className="text-sm text-gray-700 font-medium">{props.auth.user?.name}</span>
              <span className="text-xs text-gray-400 block text-right">{props.auth.user?.username}</span>
            </div>
          </div>
        </header>

        <main className="app-content bg-white min-h-screen">
          {props.flash.warning && (
            <AppAlert type="warning" message={props.flash.warning} className="mb-4" />
          )}
          {children}
        </main>
      </div>
    </div>
  );
}
