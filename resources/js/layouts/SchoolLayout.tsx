import React, { useState, useMemo } from 'react';
import { Link, router, usePage } from '@inertiajs/react';

// ─── Sidebar Link Sub-Component ───────────────────────────────────────────────

interface SidebarLinkProps {
  href: string;
  active: boolean;
  label: string;
  icon: React.ReactNode;
}

function SidebarLink({ href, active, label, icon }: SidebarLinkProps) {
  return (
    <Link
      href={href}
      className={`sl-nav-item${active ? ' sl-nav-active' : ''}`}
    >
      <span className="sl-nav-icon">{icon}</span>
      <span className="sl-nav-label">{label}</span>
    </Link>
  );
}

// ─── Icon SVGs ────────────────────────────────────────────────────────────────

const DashboardIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
  </svg>
);

const EnrollmentIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
  </svg>
);

const ExamIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
    <polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/>
    <line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/>
  </svg>
);

const InvoiceIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <rect x="2" y="5" width="20" height="14" rx="2"/>
    <line x1="2" y1="10" x2="22" y2="10"/>
  </svg>
);

const ReportsIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
    <line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>
  </svg>
);

const ProfileIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
    <polyline points="9,22 9,12 15,12 15,22"/>
  </svg>
);

const SignOutIcon = (
  <svg className="sl-signout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
    <polyline points="16,17 21,12 16,7"/>
    <line x1="21" y1="12" x2="9" y2="12"/>
  </svg>
);

// ─── Main Layout ──────────────────────────────────────────────────────────────

interface SchoolLayoutProps {
  children: React.ReactNode;
  breadcrumb?: React.ReactNode;
}

/**
 * School Admin sidebar layout — Navy + Gold theme with collapsible mobile sidebar.
 * Drop-in React replacement for SchoolLayout.vue.
 * All scoped CSS is preserved inline.
 */
export default function SchoolLayout({ children, breadcrumb }: SchoolLayoutProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { url, props: rawProps } = usePage();

  const props = rawProps as unknown as {
    auth: { user: { name: string; school?: { name: string } | null } };
    flash: { success?: string; error?: string; warning?: string; info?: string };
    activeYear?: {
      label: string;
      is_enrollment_open: boolean;
      is_examination_open: boolean;
    } | null;
  };

  const enrollmentOpen  = props.activeYear?.is_enrollment_open  ?? false;
  const examinationOpen = props.activeYear?.is_examination_open ?? false;

  const userInitial = useMemo(() => {
    const name = props.auth.user?.name ?? 'U';
    return name.charAt(0).toUpperCase();
  }, [props.auth.user?.name]);

  function isActive(segment: string, exact = false): boolean {
    if (exact) return url === '/' + segment || url.startsWith('/' + segment + '?');
    return url.includes(segment);
  }

  function logout() {
    router.post(route('logout'));
  }

  return (
    <div className={`sl-shell${sidebarOpen ? ' sl-mobile-open' : ''}`}>

      {/* ── Mobile Overlay ──────────────────────────────────────────────── */}
      {sidebarOpen && (
        <div className="sl-overlay" onClick={() => setSidebarOpen(false)} />
      )}

      {/* ── Sidebar ─────────────────────────────────────────────────────── */}
      <aside className={`sl-sidebar${sidebarOpen ? ' sl-sidebar-open' : ''}`}>

        {/* Logo / School Name */}
        <div className="sl-brand">
          <img src="/images/bise-sukkur-logo.png" alt="BISE Sukkur Logo" className="sl-brand-image" />
          <div className="sl-brand-text">
            <span className="sl-brand-name">
              {props.auth.user?.school?.name ?? 'My School'}
            </span>
            <span className="sl-brand-sub">BISE Sukkur Portal</span>
          </div>
        </div>

        {/* Navigation */}
        <nav className="sl-nav">
          <SidebarLink
            href={route('school.dashboard')}
            active={isActive('school.dashboard', true)}
            label="Dashboard"
            icon={DashboardIcon}
          />
          <SidebarLink
            href={route('school.students.index')}
            active={isActive('school/enrollment') || isActive('school/students')}
            label="Enrollment Form"
            icon={EnrollmentIcon}
          />
          <SidebarLink
            href={route('school.examination.forms')}
            active={isActive('school/examination')}
            label="Examination Form"
            icon={ExamIcon}
          />
          <SidebarLink
            href={route('school.enrollment.challans')}
            active={isActive('school/enrollment/challans') || isActive('school/examination/challans')}
            label="Invoices"
            icon={InvoiceIcon}
          />
          <SidebarLink
            href={route('school.enrollment.challans')}
            active={isActive('school/reports')}
            label="Reports"
            icon={ReportsIcon}
          />
          <SidebarLink
            href={route('school.dashboard')}
            active={isActive('school/profile')}
            label="School Profile"
            icon={ProfileIcon}
          />
        </nav>

        {/* Sidebar Footer */}
        <div className="sl-sidebar-footer">
          <div className="sl-footer-divider" />

          {/* Window status indicators */}
          <div className="sl-status-pills">
            <div className="sl-status-pill">
              <span className={`sl-status-dot ${enrollmentOpen ? 'sl-dot-open' : 'sl-dot-closed'}`} />
              <span className="sl-status-label">Enrollment {enrollmentOpen ? 'Open' : 'Closed'}</span>
            </div>
            <div className="sl-status-pill">
              <span className={`sl-status-dot ${examinationOpen ? 'sl-dot-open' : 'sl-dot-closed'}`} />
              <span className="sl-status-label">Exam {examinationOpen ? 'Open' : 'Closed'}</span>
            </div>
          </div>

          {/* User info */}
          <div className="sl-user-row">
            <div className="sl-user-avatar">{userInitial}</div>
            <div className="sl-user-info">
              <div className="sl-user-name">{props.auth.user?.name}</div>
              <div className="sl-user-role">School Administrator</div>
            </div>
          </div>

          {/* Sign Out */}
          <button type="button" onClick={logout} className="sl-signout-btn">
            {SignOutIcon}
            Sign Out
          </button>
        </div>
      </aside>

      {/* ── Main Content ─────────────────────────────────────────────────── */}
      <div className="sl-main">

        {/* Top Bar */}
        <header className="sl-topbar">
          {/* Mobile hamburger */}
          <button
            type="button"
            className="sl-hamburger"
            onClick={() => setSidebarOpen((v) => !v)}
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
              <line x1="3" y1="6" x2="21" y2="6"/>
              <line x1="3" y1="12" x2="21" y2="12"/>
              <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
          </button>

          {/* Breadcrumb */}
          <div className="sl-breadcrumb">{breadcrumb}</div>

          {/* Right side */}
          <div className="sl-topbar-right">
            {props.flash.success && (
              <div className="sl-flash sl-flash-success">
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                  <polyline points="20,6 9,17 4,12"/>
                </svg>
                {props.flash.success}
              </div>
            )}
            {props.flash.error && (
              <div className="sl-flash sl-flash-error">
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                {props.flash.error}
              </div>
            )}

            {/* Active Year Badge */}
            {props.activeYear && (
              <div className="sl-year-badge">
                <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <rect x="3" y="4" width="18" height="18" rx="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                {props.activeYear.label}
              </div>
            )}

            {/* School name chip */}
            <div className="sl-school-chip">
              {props.auth.user?.school?.name ?? '—'}
            </div>
          </div>
        </header>

        {/* Page content */}
        <main className="sl-content">
          {props.flash.warning && (
            <div className="sl-alert sl-alert-warning mb-4">
              <svg className="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
              {props.flash.warning}
            </div>
          )}
          {props.flash.info && (
            <div className="sl-alert sl-alert-info mb-4">
              <svg className="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              {props.flash.info}
            </div>
          )}
          {children}
        </main>
      </div>

      {/* ── All original scoped CSS preserved ──────────────────────────── */}
      <style>{`
        .sl-shell { display:flex; min-height:100vh; background:linear-gradient(180deg,#F8FBFF 0%,#EFF4FF 100%); font-family:'Inter',ui-sans-serif,system-ui,sans-serif; color:#0F172A; }
        .sl-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.14); z-index:39; backdrop-filter:blur(4px); }
        .sl-sidebar { width:278px; min-height:100vh; background:linear-gradient(180deg,#FFFFFF 0%,#F8FBFF 100%); border-right:1px solid rgba(148,163,184,0.18); box-shadow:18px 0 45px rgba(15,23,42,0.06); display:flex; flex-direction:column; position:fixed; left:0; top:0; bottom:0; z-index:40; transition:transform 0.25s cubic-bezier(.4,0,.2,1); overflow:hidden; }
        .sl-brand { display:flex; align-items:center; gap:.875rem; padding:1.25rem; margin:1rem 1rem .25rem; border:1px solid #E2E8F0; border-radius:22px; background:linear-gradient(135deg,#FFFFFF 0%,#F6FAFF 100%); box-shadow:0 10px 30px rgba(148,163,184,0.12); }
        .sl-brand-image { width:42px; height:42px; object-fit:contain; flex-shrink:0; padding:.35rem; border-radius:14px; background:#FFFFFF; box-shadow:0 6px 18px rgba(59,130,246,0.12); }
        .sl-brand-mark { width:38px; height:38px; border-radius:10px; background:#D1FAE5; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 2px 10px rgba(16,185,129,0.12); }
        .sl-brand-text { display:flex; flex-direction:column; min-width:0; }
        .sl-brand-name { font-size:.85rem; font-weight:800; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.3; }
        .sl-brand-sub { font-size:.68rem; color:#64748B; text-transform:uppercase; letter-spacing:.09em; font-weight:600; margin-top:2px; }
        .sl-nav { flex:1; padding:1rem; overflow-y:auto; display:flex; flex-direction:column; gap:.45rem; }
        .sl-nav-item { display:flex; align-items:center; gap:.75rem; padding:.8rem .9rem; border-radius:16px; text-decoration:none; color:#475569; font-size:.9rem; font-weight:600; transition:all .18s ease; cursor:pointer; border:1px solid transparent; background:rgba(255,255,255,0.72); width:100%; text-align:left; position:relative; box-shadow:0 4px 16px rgba(148,163,184,0.08); }
        .sl-nav-item:hover { background:#FFFFFF; color:#0F172A; border-color:#DBEAFE; box-shadow:0 10px 22px rgba(148,163,184,0.14); transform:translateY(-1px); }
        .sl-nav-active { background:linear-gradient(135deg,#EFF6FF 0%,#F0F9FF 100%) !important; color:#1D4ED8 !important; font-weight:700; border-color:#BFDBFE !important; box-shadow:0 14px 28px rgba(59,130,246,0.16); }
        .sl-nav-active .sl-nav-icon { opacity:1 !important; }
        .sl-nav-icon { width:20px; height:20px; flex-shrink:0; opacity:.8; display:flex; align-items:center; justify-content:center; color:inherit; }
        .sl-nav-icon svg { width:20px; height:20px; }
        .sl-nav-label { white-space:nowrap; }
        .sl-sidebar-footer { padding:1rem; }
        .sl-footer-divider { border-top:1px solid rgba(148,163,184,0.16); margin-bottom:1rem; }
        .sl-status-pills { display:flex; flex-direction:column; gap:.55rem; margin-bottom:1rem; padding:1rem; background:rgba(255,255,255,0.82); border:1px solid #E2E8F0; border-radius:18px; box-shadow:0 10px 26px rgba(148,163,184,0.1); }
        .sl-status-pill { display:flex; align-items:center; gap:.6rem; }
        .sl-status-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
        .sl-dot-open { background:#10B981; box-shadow:0 0 0 2px rgba(16,185,129,0.16); }
        .sl-dot-closed { background:#EF4444; }
        .sl-status-label { font-size:.76rem; color:#475569; font-weight:600; }
        .sl-user-row { display:flex; align-items:center; gap:.75rem; margin-bottom:1rem; padding:.85rem .9rem; background:linear-gradient(135deg,#FFFFFF 0%,#F8FBFF 100%); border:1px solid #E2E8F0; border-radius:18px; box-shadow:0 10px 22px rgba(148,163,184,0.09); }
        .sl-user-avatar { width:38px; height:38px; border-radius:14px; background:linear-gradient(135deg,#DBEAFE 0%,#E0F2FE 100%); color:#1D4ED8; display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:800; flex-shrink:0; box-shadow:inset 0 1px 0 rgba(255,255,255,0.7); }
        .sl-user-info { min-width:0; }
        .sl-user-name { font-size:.84rem; font-weight:700; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .sl-user-role { font-size:.72rem; color:#64748B; margin-top:2px; }
        .sl-signout-btn { display:flex; align-items:center; justify-content:center; gap:.5rem; width:100%; padding:.8rem .9rem; border-radius:16px; border:1px solid #FECACA; background:linear-gradient(135deg,#FFF5F5 0%,#FFFFFF 100%); color:#B91C1C; font-size:.85rem; font-weight:700; cursor:pointer; transition:all .18s ease; font-family:'Inter',ui-sans-serif,system-ui,sans-serif; box-shadow:0 10px 22px rgba(248,113,113,0.08); }
        .sl-signout-btn:hover { background:#FFFFFF; border-color:#FCA5A5; color:#991B1B; transform:translateY(-1px); }
        .sl-signout-icon { width:16px; height:16px; flex-shrink:0; }
        .sl-main { flex:1; margin-left:278px; display:flex; flex-direction:column; min-height:100vh; }
        .sl-topbar { height:74px; margin:1rem 1rem 0; background:rgba(255,255,255,0.82); border:1px solid rgba(226,232,240,0.95); border-radius:22px; backdrop-filter:blur(14px); box-shadow:0 12px 30px rgba(148,163,184,0.1); display:flex; align-items:center; padding:0 1.25rem; gap:1rem; position:sticky; top:1rem; z-index:30; }
        .sl-hamburger { display:none; width:40px; height:40px; align-items:center; justify-content:center; border:none; background:#F8FAFC; cursor:pointer; color:#475569; border-radius:12px; box-shadow:inset 0 0 0 1px #E2E8F0; }
        .sl-hamburger svg { width:20px; height:20px; }
        .sl-hamburger:hover { background:#FFFFFF; color:#0F172A; }
        .sl-breadcrumb { flex:1; font-size:.875rem; color:#475569; }
        .sl-topbar-right { display:flex; align-items:center; gap:.75rem; flex-shrink:0; }
        .sl-flash { display:flex; align-items:center; gap:.375rem; font-size:.8125rem; font-weight:600; padding:.45rem .8rem; border-radius:9999px; }
        .sl-flash-success { background:#DCFCE7; color:#064E3B; }
        .sl-flash-error { background:#FEE2E2; color:#991B1B; }
        .sl-year-badge { display:flex; align-items:center; gap:.375rem; font-size:.75rem; font-weight:700; color:#0F172A; background:#ECFDF5; border:1px solid #BBF7D0; padding:.45rem .8rem; border-radius:9999px; box-shadow:0 8px 20px rgba(16,185,129,0.12); }
        .sl-school-chip { font-size:.8125rem; font-weight:700; color:#0F172A; background:#FFFFFF; border:1px solid #E2E8F0; padding:.45rem .85rem; border-radius:9999px; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; box-shadow:0 8px 20px rgba(148,163,184,0.08); }
        .sl-content { flex:1; padding:1.75rem 2rem 2rem; background:transparent; }
        .sl-alert { display:flex; align-items:flex-start; gap:.625rem; padding:.85rem 1rem; border-radius:16px; font-size:.875rem; font-weight:600; box-shadow:0 10px 24px rgba(148,163,184,0.08); }
        .sl-alert-warning { background:#FEFCE8; color:#92400E; border:1px solid #FDE68A; }
        .sl-alert-info { background:#EFF6FF; color:#1E40AF; border:1px solid #93C5FD; }
        @media (max-width:768px) {
          .sl-sidebar { transform:translateX(-100%); }
          .sl-sidebar-open { transform:translateX(0); box-shadow:4px 0 24px rgba(15,23,42,0.12); }
          .sl-main { margin-left:0; }
          .sl-hamburger { display:flex; }
          .sl-content { padding:1.25rem 1rem 1.5rem; }
          .sl-topbar { height:68px; margin:.75rem .75rem 0; padding:0 1rem; top:.75rem; }
          .sl-school-chip { display:none; }
        }
      `}</style>
    </div>
  );
}
