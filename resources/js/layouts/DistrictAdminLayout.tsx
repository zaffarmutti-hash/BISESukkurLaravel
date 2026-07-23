
import React, { useState, useMemo } from 'react';
import { Link, router, usePage } from '@inertiajs/react';

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
      className={`da-nav-item${active ? ' da-nav-active' : ''}`}
    >
      <span className="da-nav-icon">{icon}</span>
      <span className="da-nav-label">{label}</span>
    </Link>
  );
}

const DashboardIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
  </svg>
);

const ReportsIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
    <line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>
  </svg>
);

const SchoolsIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16"/>
    <path d="M12 8v4l3 3"/>
  </svg>
);

const AnnouncementsIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
  </svg>
);


const SignOutIcon = (
  <svg className="da-signout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
    <polyline points="16,17 21,12 16,7"/>
    <line x1="21" y1="12" x2="9" y2="12"/>
  </svg>
);

interface DistrictAdminLayoutProps {
  children: React.ReactNode;
  breadcrumb?: React.ReactNode;
}

export default function DistrictAdminLayout({ children, breadcrumb }: DistrictAdminLayoutProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { url, props: rawProps } = usePage();

  const props = rawProps as unknown as {
    auth: { user: { name: string; district?: { name: string } | null } };
    flash: { success?: string; error?: string; warning?: string; info?: string };
    activeYear?: {
      label: string;
      is_enrollment_open: boolean;
      is_examination_open: boolean;
    } | null;
  };

  const enrollmentOpen = props.activeYear?.is_enrollment_open ?? false;
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
    <div className={`da-shell${sidebarOpen ? ' da-mobile-open' : ''}`}>
      {sidebarOpen && (
        <div className="da-overlay" onClick={() => setSidebarOpen(false)} />
      )}

      <aside className={`da-sidebar${sidebarOpen ? ' da-sidebar-open' : ''}`}>
        <div className="da-brand">
          <img src="/images/bise-sukkur-logo.png" alt="BISE Sukkur Logo" className="da-brand-image" />
          <div className="da-brand-text">
            <span className="da-brand-name">
              {props.auth.user?.district?.name ?? 'District Admin'}
            </span>
            <span className="da-brand-sub">BISE Sukkur Portal</span>
          </div>
        </div>

        <nav className="da-nav">
          <SidebarLink
            href={route('district.dashboard')}
            active={isActive('district.dashboard', true)}
            label="Dashboard"
            icon={DashboardIcon}
          />
          <SidebarLink
            href={route('district.schools')}
            active={isActive('district/schools')}
            label="Schools"
            icon={SchoolsIcon}
          />
          <SidebarLink
            href={route('district.reports')}
            active={isActive('district/reports')}
            label="Reports"
            icon={ReportsIcon}
          />
          <SidebarLink
            href={route('district.announcements')}
            active={isActive('district/announcements')}
            label="Announcements"
            icon={AnnouncementsIcon}
          />
        </nav>

        <div className="da-sidebar-footer">
          <div className="da-footer-divider" />

          <div className="da-status-pills">
            <div className="da-status-pill">
              <span className={`da-status-dot ${enrollmentOpen ? 'da-dot-open' : 'da-dot-closed'}`} />
              <span className="da-status-label">Enrollment {enrollmentOpen ? 'Open' : 'Closed'}</span>
            </div>
            <div className="da-status-pill">
              <span className={`da-status-dot ${examinationOpen ? 'da-dot-open' : 'da-dot-closed'}`} />
              <span className="da-status-label">Exam {examinationOpen ? 'Open' : 'Closed'}</span>
            </div>
          </div>

          <div className="da-user-row">
            <div className="da-user-avatar">{userInitial}</div>
            <div className="da-user-info">
              <div className="da-user-name">{props.auth.user?.name}</div>
              <div className="da-user-role">District Admin</div>
            </div>
          </div>

          <button type="button" onClick={logout} className="da-signout-btn">
            {SignOutIcon}
            Sign Out
          </button>
        </div>
      </aside>

      <div className="da-main">
        <header className="da-topbar">
          <button
            type="button"
            className="da-hamburger"
            onClick={() => setSidebarOpen((v) => !v)}
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
              <line x1="3" y1="6" x2="21" y2="6"/>
              <line x1="3" y1="12" x2="21" y2="12"/>
              <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
          </button>

          <div className="da-breadcrumb">{breadcrumb}</div>

          <div className="da-topbar-right">
            {props.flash.success && (
              <div className="da-flash da-flash-success">
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                  <polyline points="20,6 9,17 4,12"/>
                </svg>
                {props.flash.success}
              </div>
            )}
            {props.flash.error && (
              <div className="da-flash da-flash-error">
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                {props.flash.error}
              </div>
            )}

            {props.activeYear && (
              <div className="da-year-badge">
                <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <rect x="3" y="4" width="18" height="18" rx="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                {props.activeYear.label}
              </div>
            )}
          </div>
        </header>

        <main className="da-content">
          {props.flash.warning && (
            <div className="da-alert da-alert-warning mb-4">
              <svg className="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
              {props.flash.warning}
            </div>
          )}
          {props.flash.info && (
            <div className="da-alert da-alert-info mb-4">
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

      <style>{`
        .da-shell { display:flex; min-height:100vh; background:linear-gradient(180deg,#FFF8F0 0%,#FFF5E6 100%); font-family:'Inter',ui-sans-serif,system-ui,sans-serif; color:#1F2937; }
        .da-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.14); z-index:39; backdrop-filter:blur(4px); }
        .da-sidebar { width:278px; min-height:100vh; background:linear-gradient(180deg,#FFFFFF 0%,#FFF8F0 100%); border-right:1px solid rgba(234,179,8,0.2); box-shadow:18px 0 45px rgba(217,119,6,0.08); display:flex; flex-direction:column; position:fixed; left:0; top:0; bottom:0; z-index:40; transition:transform 0.25s cubic-bezier(.4,0,.2,1); overflow:hidden; }
        .da-brand { display:flex; align-items:center; gap:.875rem; padding:1.25rem; margin:1rem 1rem .25rem; border:1px solid #FDE68A; border-radius:22px; background:linear-gradient(135deg,#FFFFFF 0%,#FEFCE8 100%); box-shadow:0 10px 30px rgba(245,158,11,0.12); }
        .da-brand-image { width:42px; height:42px; object-fit:contain; flex-shrink:0; padding:.35rem; border-radius:14px; background:#FFFFFF; box-shadow:0 6px 18px rgba(245,158,11,0.12); }
        .da-brand-text { display:flex; flex-direction:column; min-width:0; }
        .da-brand-name { font-size:.85rem; font-weight:800; color:#92400E; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.3; }
        .da-brand-sub { font-size:.68rem; color:#A16207; text-transform:uppercase; letter-spacing:.09em; font-weight:600; margin-top:2px; }
        .da-nav { flex:1; padding:1rem; overflow-y:auto; display:flex; flex-direction:column; gap:.45rem; }
        .da-nav-item { display:flex; align-items:center; gap:.75rem; padding:.8rem .9rem; border-radius:16px; text-decoration:none; color:#78350F; font-size:.9rem; font-weight:600; transition:all .18s ease; cursor:pointer; border:1px solid transparent; background:rgba(255,255,255,0.85); width:100%; text-align:left; position:relative; box-shadow:0 4px 16px rgba(245,158,11,0.06); }
        .da-nav-item:hover { background:#FFFFFF; color:#78350F; border-color:#FDE68A; box-shadow:0 10px 22px rgba(245,158,11,0.12); transform:translateY(-1px); }
        .da-nav-active { background:linear-gradient(135deg,#FEF3C7 0%,#FDE68A 100%) !important; color:#78350F !important; font-weight:700; border-color:#F59E0B !important; box-shadow:0 14px 28px rgba(245,158,11,0.2); }
        .da-nav-active .da-nav-icon { opacity:1 !important; }
        .da-nav-icon { width:20px; height:20px; flex-shrink:0; opacity:.8; display:flex; align-items:center; justify-content:center; color:inherit; }
        .da-nav-icon svg { width:20px; height:20px; }
        .da-nav-label { white-space:nowrap; }
        .da-sidebar-footer { padding:1rem; }
        .da-footer-divider { border-top:1px solid rgba(245,158,11,0.2); margin-bottom:1rem; }
        .da-status-pills { display:flex; flex-direction:column; gap:.55rem; margin-bottom:1rem; padding:1rem; background:rgba(255,255,255,0.9); border:1px solid #FDE68A; border-radius:18px; box-shadow:0 10px 26px rgba(245,158,11,0.08); }
        .da-status-pill { display:flex; align-items:center; gap:.6rem; }
        .da-status-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
        .da-dot-open { background:#059669; box-shadow:0 0 0 2px rgba(16,185,129,0.2); }
        .da-dot-closed { background:#DC2626; }
        .da-status-label { font-size:.76rem; color:#A16207; font-weight:600; }
        .da-user-row { display:flex; align-items:center; gap:.75rem; margin-bottom:1rem; padding:.85rem .9rem; background:linear-gradient(135deg,#FFFFFF 0%,#FFF8F0 100%); border:1px solid #FDE68A; border-radius:18px; box-shadow:0 10px 22px rgba(245,158,11,0.08); }
        .da-user-avatar { width:38px; height:38px; border-radius:14px; background:linear-gradient(135deg,#FDE68A 0%,#F59E0B 100%); color:#78350F; display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:800; flex-shrink:0; box-shadow:inset 0 1px 0 rgba(255,255,255,0.8); }
        .da-user-info { min-width:0; }
        .da-user-name { font-size:.84rem; font-weight:700; color:#78350F; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .da-user-role { font-size:.72rem; color:#A16207; margin-top:2px; }
        .da-signout-btn { display:flex; align-items:center; justify-content:center; gap:.5rem; width:100%; padding:.8rem .9rem; border-radius:16px; border:1px solid #FECACA; background:linear-gradient(135deg,#FFF5F5 0%,#FFFFFF 100%); color:#991B1B; font-size:.85rem; font-weight:700; cursor:pointer; transition:all .18s ease; font-family:'Inter',ui-sans-serif,system-ui,sans-serif; box-shadow:0 10px 22px rgba(239,68,68,0.08); }
        .da-signout-btn:hover { background:#FFFFFF; border-color:#FCA5A5; color:#7F1D1D; transform:translateY(-1px); }
        .da-signout-icon { width:16px; height:16px; flex-shrink:0; }
        .da-main { flex:1; margin-left:278px; display:flex; flex-direction:column; min-height:100vh; }
        .da-topbar { height:74px; margin:1rem 1rem 0; background:rgba(255,255,255,0.95); border:1px solid rgba(245,158,11,0.3); border-radius:22px; backdrop-filter:blur(14px); box-shadow:0 12px 30px rgba(245,158,11,0.12); display:flex; align-items:center; padding:0 1.25rem; gap:1rem; position:sticky; top:1rem; z-index:30; }
        .da-hamburger { display:none; width:40px; height:40px; align-items:center; justify-content:center; border:none; background:#FFF8F0; cursor:pointer; color:#78350F; border-radius:12px; box-shadow:inset 0 0 0 1px #FDE68A; }
        .da-hamburger svg { width:20px; height:20px; }
        .da-hamburger:hover { background:#FFFFFF; color:#78350F; }
        .da-breadcrumb { flex:1; font-size:.875rem; color:#A16207; }
        .da-topbar-right { display:flex; align-items:center; gap:.75rem; flex-shrink:0; }
        .da-flash { display:flex; align-items:center; gap:.375rem; font-size:.8125rem; font-weight:600; padding:.45rem .8rem; border-radius:9999px; }
        .da-flash-success { background:#DCFCE7; color:#065F46; }
        .da-flash-error { background:#FEE2E2; color:#991B1B; }
        .da-year-badge { display:flex; align-items:center; gap:.375rem; font-size:.75rem; font-weight:700; color:#78350F; background:#FEFCE8; border:1px solid #FDE68A; padding:.45rem .8rem; border-radius:9999px; box-shadow:0 8px 20px rgba(245,158,11,0.15); }
        .da-content { flex:1; padding:1.75rem 2rem 2rem; background:transparent; }
        .da-alert { display:flex; align-items:flex-start; gap:.625rem; padding:.85rem 1rem; border-radius:16px; font-size:.875rem; font-weight:600; box-shadow:0 10px 24px rgba(245,158,11,0.1); }
        .da-alert-warning { background:#FEF3C7; color:#78350F; border:1px solid #FDE68A; }
        .da-alert-info { background:#FFF8F0; color:#A16207; border:1px solid #FDE68A; }
        @media (max-width:768px) {
          .da-sidebar { transform:translateX(-100%); }
          .da-sidebar-open { transform:translateX(0); box-shadow:4px 0 24px rgba(15,23,42,0.15); }
          .da-main { margin-left:0; }
          .da-hamburger { display:flex; }
          .da-content { padding:1.25rem 1rem 1.5rem; }
          .da-topbar { height:68px; margin:.75rem .75rem 0; padding:0 1rem; top:.75rem; }
        }
      `}</style>
    </div>
  );
}
