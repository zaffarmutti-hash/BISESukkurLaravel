<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Super Admin Portal' }} — BISE Sukkur</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="/css/superadmin.css?v={{ time() }}">
    @stack('styles')
</head>
<body class="superadmin-body">
    <!-- Dark Backdrop Overlay for Mobile -->
    <div id="saSidebarOverlay" class="sa-overlay" onclick="toggleSidebar(false)"></div>

    <!-- ═════════════════════════════════════════════════════════════════════
         SIDEBAR (260px fixed width, #0f172a very dark navy)
         ═════════════════════════════════════════════════════════════════════ -->
    <aside id="saSidebar" class="sa-sidebar">
        <!-- Logo Area (72px, #1e293b) -->
        <div class="sa-sidebar-brand">
            <div class="sa-brand-icon">B</div>
            <div class="sa-brand-info">
                <span class="sa-brand-title">BISE SUKKUR</span>
                <span class="sa-brand-sub">SUPER ADMIN</span>
            </div>
        </div>

        <!-- Navigation structure with ALL working routes -->
        <nav class="sa-nav">
            <!-- OVERVIEW -->
            <div class="sa-nav-section-label">OVERVIEW</div>
            <a href="{{ route('superadmin.dashboard') }}" class="sa-nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('superadmin.health') }}" class="sa-nav-link {{ request()->routeIs('superadmin.health*') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <span>System Health</span>
            </a>

            <!-- MANAGEMENT -->
            <div class="sa-nav-section-label">MANAGEMENT</div>
            <a href="{{ route('superadmin.schools') }}" class="sa-nav-link {{ request()->routeIs('superadmin.schools*') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 21h18M3 7v14M21 7v14M6 11h2M6 15h2M11 11h2M11 15h2M16 11h2M16 15h2M3 7l9-4 9 4"/></svg>
                <span>Schools</span>
            </a>
            <a href="{{ route('superadmin.districts') }}" class="sa-nav-link {{ request()->routeIs('superadmin.districts*') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                <span>Districts</span>
            </a>
            <a href="{{ route('superadmin.users.index') }}" class="sa-nav-link {{ request()->routeIs('superadmin.users*') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Users</span>
            </a>

            <!-- ENROLLMENT -->
            <div class="sa-nav-section-label">ENROLLMENT</div>
            <a href="{{ route('superadmin.enrollment') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.enrollment') || request()->routeIs('superadmin.enrollment-hub')) && !request()->has('tab') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><circle cx="19" cy="5" r="2"/><circle cx="5" cy="5" r="2"/><circle cx="19" cy="19" r="2"/><circle cx="5" cy="19" r="2"/><line x1="12" y1="9" x2="12" y2="6"/><line x1="14.5" y1="13.5" x2="17.5" y2="17.5"/><line x1="9.5" y1="13.5" x2="6.5" y2="17.5"/></svg>
                <span>Enrollment Hub</span>
            </a>
            <a href="{{ route('superadmin.enrollment.window') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.enrollment.window') || (request()->is('*enrollment*') && request()->get('tab') === 'window')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Window Settings</span>
            </a>
            <a href="{{ route('superadmin.enrollment.fees') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.enrollment.fees') || (request()->is('*enrollment*') && request()->get('tab') === 'fees')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>Fee Configuration</span>
            </a>
            <a href="{{ route('superadmin.enrollment.verify') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.enrollment.verify') || (request()->is('*enrollment*') && request()->get('tab') === 'verify')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                <span>Pending Verifications</span>
            </a>
            <a href="{{ route('superadmin.enrollment.allotment') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.enrollment.allotment*') || (request()->is('*enrollment*') && request()->get('tab') === 'allotment')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
                <span>Allotment History</span>
            </a>
            <a href="{{ route('superadmin.enrollment.permissions') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.enrollment.permissions*') || (request()->is('*enrollment*') && request()->get('tab') === 'permissions')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 2l-2 2m-1.5 6.1L12.9 4.7a6.5 6.5 0 1 0-8.2 8.2l5.4 4.6 2-2 2 2 2-2 2 2 2.5-2.5"/></svg>
                <span>Special Permissions</span>
            </a>
            <a href="{{ route('superadmin.enrollment.reports') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.enrollment.reports*') || request()->routeIs('superadmin.reports.enrollment*') || (request()->is('*enrollment*') && request()->get('tab') === 'reports')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <span>Enrollment Reports</span>
            </a>

            <!-- EXAMINATION -->
            <div class="sa-nav-section-label">EXAMINATION</div>
            <a href="{{ route('superadmin.exam') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.exam') || request()->routeIs('superadmin.examination-hub')) && !request()->has('tab') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                <span>Examination Hub</span>
            </a>
            <a href="{{ route('superadmin.exam.window') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.exam.window') || (request()->is('*exam*') && request()->get('tab') === 'window')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Exam Window</span>
            </a>
            <a href="{{ route('superadmin.exam.fees') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.exam.fees') || (request()->is('*exam*') && request()->get('tab') === 'fees')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><line x1="12" y1="6" x2="12" y2="8"/><line x1="12" y1="16" x2="12" y2="18"/></svg>
                <span>Exam Fees</span>
            </a>
            <a href="{{ route('superadmin.exam.seats') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.exam.seats*') || request()->routeIs('superadmin.examination.seat-allotment*') || (request()->is('*exam*') && request()->get('tab') === 'seats')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="4" y="4" width="16" height="16" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                <span>Seat Allotment</span>
            </a>
            <a href="{{ route('superadmin.exam.centers') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.exam.centers*') || request()->routeIs('superadmin.examination.centers*') || (request()->is('*exam*') && request()->get('tab') === 'centers')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>Exam Centers</span>
            </a>
            <a href="{{ route('superadmin.exam.timetable') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.exam.timetable*') || request()->routeIs('superadmin.examination.timetable*') || (request()->is('*exam*') && request()->get('tab') === 'timetable')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Timetable</span>
            </a>
            <a href="{{ route('superadmin.exam.results') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.exam.results*') || request()->routeIs('superadmin.examination.results*') || (request()->is('*exam*') && request()->get('tab') === 'results')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <span>Result Entry</span>
            </a>
            <a href="{{ route('superadmin.exam.reports') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.exam.reports*') || request()->routeIs('superadmin.reports.gap*') || (request()->is('*exam*') && request()->get('tab') === 'reports')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <span>Exam Reports</span>
            </a>
            <a href="{{ route('superadmin.exam.certificates') }}" class="sa-nav-link {{ (request()->routeIs('superadmin.exam.certificates*') || request()->routeIs('superadmin.examination.certificates*') || (request()->is('*exam*') && request()->get('tab') === 'certificates')) ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                <span>Certificates</span>
            </a>

            <!-- FINANCE -->
            <div class="sa-nav-section-label">FINANCE</div>
            <a href="{{ route('superadmin.invoices.verify') }}" class="sa-nav-link {{ request()->routeIs('superadmin.invoices.verify*') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Invoice Verification</span>
            </a>
            <a href="{{ route('superadmin.invoices.transactions') }}" class="sa-nav-link {{ request()->routeIs('superadmin.invoices.transactions*') || request()->routeIs('superadmin.payment-transactions*') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                <span>Payment Transactions</span>
            </a>
            <a href="{{ route('superadmin.reports.fees') }}" class="sa-nav-link {{ request()->routeIs('superadmin.reports.fees*') || request()->routeIs('superadmin.reports.fee-collection*') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1z"/><line x1="8" y1="7" x2="16" y2="7"/><line x1="8" y1="11" x2="16" y2="11"/><line x1="8" y1="15" x2="12" y2="15"/></svg>
                <span>Fee Collection</span>
            </a>

            <!-- SYSTEM -->
            <div class="sa-nav-section-label">SYSTEM</div>
            <a href="{{ route('superadmin.activity-log') }}" class="sa-nav-link {{ request()->routeIs('superadmin.activity-log*') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                <span>Activity Log</span>
            </a>
            <a href="{{ route('superadmin.security') }}" class="sa-nav-link {{ request()->routeIs('superadmin.security*') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span>Security Dashboard</span>
            </a>
            @php
                $pendingApprovalsBadge = \App\Models\Invoice::where('status', 'submitted')->count();
            @endphp
            <a href="{{ route('superadmin.approvals') }}" class="sa-nav-link {{ request()->routeIs('superadmin.approvals*') ? 'active' : '' }}" onclick="toggleSidebar(false)" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                    <span>Pending Approvals</span>
                </div>
                @if($pendingApprovalsBadge > 0)
                    <span style="background: #ef4444; color: #fff; font-size: 10px; font-weight: 800; border-radius: 9999px; padding: 2px 7px; margin-right: 8px;">
                        {{ $pendingApprovalsBadge }}
                    </span>
                @endif
            </a>
            <a href="{{ route('superadmin.announcements') }}" class="sa-nav-link {{ request()->routeIs('superadmin.announcements*') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span>Announcements</span>
            </a>
            <a href="{{ route('superadmin.settings.system') }}" class="sa-nav-link {{ request()->routeIs('superadmin.settings*') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span>System Settings</span>
            </a>
            <a href="{{ route('superadmin.academic-years.index') }}" class="sa-nav-link {{ request()->routeIs('superadmin.academic-years*') ? 'active' : '' }}" onclick="toggleSidebar(false)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Academic Years</span>
            </a>
        </nav>

        <!-- Sidebar footer -->
        <div class="sa-sidebar-footer">
            <div class="sa-user-meta">
                <span class="sa-user-name">{{ auth()->user()->name ?? 'Administrator' }}</span>
                <span class="sa-badge-gold">SUPER ADMIN</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="sa-logout-btn" title="Sign Out">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- ═════════════════════════════════════════════════════════════════════
         TOP HEADER BAR (64px, frosted glass, sticky)
         ═════════════════════════════════════════════════════════════════════ -->
    <header class="sa-header">
        <div class="sa-header-left">
            <button class="sa-hamburger" onclick="toggleSidebar(true)" aria-label="Open Navigation">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="sa-breadcrumb">
                <span class="sa-breadcrumb-section">{{ $breadcrumbSection ?? 'Super Admin' }}</span>
                <span class="sa-breadcrumb-sep">/</span>
                <span class="sa-breadcrumb-current">{{ $breadcrumbCurrent ?? ($title ?? 'Dashboard') }}</span>
            </div>
        </div>

        <div class="sa-header-center">
            @php
                $activeYear = \App\Models\AcademicYear::current();
            @endphp
            <div class="sa-session-badge" title="Active Academic Session">
                <span class="sa-session-dot"></span>
                <span>Session: {{ $activeYear->name ?? '2026-2027' }}</span>
            </div>
        </div>

        <div class="sa-header-right">
            <!-- Global Search Trigger (Ctrl+K) -->
            <button class="sa-search-trigger" onclick="openSearchModal()" title="Global Search (Ctrl+K)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <span>Search everything...</span>
                <span class="sa-kbd">Ctrl K</span>
            </button>

            <!-- Refresh Button -->
            <button class="sa-header-btn" onclick="window.location.reload()" title="Refresh Page">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            </button>

            <!-- Notifications Bell -->
            <button class="sa-header-btn" title="Notifications">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span class="sa-badge-count">3</span>
            </button>

            <!-- Avatar -->
            <div class="sa-avatar" title="{{ auth()->user()->name ?? 'Super Admin' }}">
                {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
            </div>
        </div>
    </header>

    <!-- ═════════════════════════════════════════════════════════════════════
         GLOBAL SEARCH OVERLAY (Ctrl+K)
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="saSearchModal" class="sa-search-modal" onclick="if(event.target === this) closeSearchModal()">
        <div class="sa-search-card">
            <div class="sa-search-input-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="saSearchInput" class="sa-search-input" placeholder="Type a command, school, student, or invoice..." oninput="handleSearch(this.value)">
                <span class="sa-kbd" onclick="closeSearchModal()" style="cursor: pointer;">ESC</span>
            </div>
            <div id="saSearchResults" class="sa-search-results">
                <!-- Navigation Items -->
                <div class="sa-search-group-title">Portal Navigation & Modules</div>
                <a href="{{ route('superadmin.dashboard') }}" class="sa-search-item">
                    <span><strong>Command Center</strong> — Overview & KPIs</span>
                    <span class="sa-kbd">/dashboard</span>
                </a>
                <a href="{{ route('superadmin.health') }}" class="sa-search-item">
                    <span><strong>System Health</strong> — DB & server diagnostics</span>
                    <span class="sa-kbd">/health</span>
                </a>
                <a href="{{ route('superadmin.schools') }}" class="sa-search-item">
                    <span><strong>Schools Directory</strong> — Manage all registered institutions</span>
                    <span class="sa-kbd">/schools</span>
                </a>
                <a href="{{ route('superadmin.districts') }}" class="sa-search-item">
                    <span><strong>Districts Overview</strong> — 5 Sindh districts jurisdiction</span>
                    <span class="sa-kbd">/districts</span>
                </a>
                <a href="{{ route('superadmin.users.index') }}" class="sa-search-item">
                    <span><strong>Users & Roles</strong> — Admin accounts & credentials</span>
                    <span class="sa-kbd">/users</span>
                </a>
                <a href="{{ route('superadmin.enrollment-hub') }}" class="sa-search-item">
                    <span><strong>Enrollment Hub</strong> — Master enrollment management</span>
                    <span class="sa-kbd">/enrollment</span>
                </a>
                <a href="{{ route('superadmin.enrollment.window') }}" class="sa-search-item">
                    <span><strong>Window Settings</strong> — Global, district & school phase dates</span>
                    <span class="sa-kbd">/enrollment/window</span>
                </a>
                <a href="{{ route('superadmin.enrollment.fees') }}" class="sa-search-item">
                    <span><strong>Fee Configuration</strong> — Rate matrix & slab definitions</span>
                    <span class="sa-kbd">/enrollment/fees</span>
                </a>
                <a href="{{ route('superadmin.invoices.verify') }}" class="sa-search-item">
                    <span><strong>Invoice Verification</strong> — Bank challans desk & allotment trigger</span>
                    <span class="sa-kbd">/invoices/verify</span>
                </a>
                <a href="{{ route('superadmin.enrollment.allotment') }}" class="sa-search-item">
                    <span><strong>Allotment Registry</strong> — Search and manual allotment</span>
                    <span class="sa-kbd">/enrollment/allotment</span>
                </a>
                <a href="{{ route('superadmin.enrollment.permissions') }}" class="sa-search-item">
                    <span><strong>Special Permissions</strong> — Deadline extensions & waivers</span>
                    <span class="sa-kbd">/enrollment/permissions</span>
                </a>
                <a href="{{ route('superadmin.reports.enrollment') }}" class="sa-search-item">
                    <span><strong>Enrollment Reports</strong> — Candidate census & breakdowns</span>
                    <span class="sa-kbd">/reports/enrollment</span>
                </a>
                <a href="{{ route('superadmin.examination-hub') }}" class="sa-search-item">
                    <span><strong>Examination Hub</strong> — Master exam operations</span>
                    <span class="sa-kbd">/exam</span>
                </a>
                <a href="{{ route('superadmin.exam.seats') }}" class="sa-search-item">
                    <span><strong>Seat Allotment</strong> — Candidate roll numbers generation</span>
                    <span class="sa-kbd">/exam/seats</span>
                </a>
                <a href="{{ route('superadmin.exam.centers') }}" class="sa-search-item">
                    <span><strong>Exam Centers</strong> — Center capacity & school zoning</span>
                    <span class="sa-kbd">/exam/centers</span>
                </a>
                <a href="{{ route('superadmin.exam.timetable') }}" class="sa-search-item">
                    <span><strong>Timetable Scheduling</strong> — Date sheets & announcements</span>
                    <span class="sa-kbd">/exam/timetable</span>
                </a>
                <a href="{{ route('superadmin.exam.results') }}" class="sa-search-item">
                    <span><strong>Result Entry</strong> — Mark entry & statutory verification</span>
                    <span class="sa-kbd">/exam/results</span>
                </a>
                <a href="{{ route('superadmin.exam.reports') }}" class="sa-search-item">
                    <span><strong>Registration Gap Report</strong> — Enrolled vs exam disparity</span>
                    <span class="sa-kbd">/reports/gap</span>
                </a>
                <a href="{{ route('superadmin.exam.certificates') }}" class="sa-search-item">
                    <span><strong>Certificates Registry</strong> — QR verification & issuance</span>
                    <span class="sa-kbd">/exam/certificates</span>
                </a>
                <a href="{{ route('superadmin.invoices.transactions') }}" class="sa-search-item">
                    <span><strong>Payment Transactions</strong> — Financial challan ledger</span>
                    <span class="sa-kbd">/invoices/transactions</span>
                </a>
                <a href="{{ route('superadmin.reports.fees') }}" class="sa-search-item">
                    <span><strong>Fee Collection Analytics</strong> — Revenue reconciliation</span>
                    <span class="sa-kbd">/reports/fees</span>
                </a>
                <a href="{{ route('superadmin.activity-log') }}" class="sa-search-item">
                    <span><strong>Activity Log</strong> — Complete board audit trail</span>
                    <span class="sa-kbd">/activity-log</span>
                </a>
                <a href="{{ route('superadmin.announcements') }}" class="sa-search-item">
                    <span><strong>Board Announcements</strong> — Official circulars broadcast</span>
                    <span class="sa-kbd">/announcements</span>
                </a>
                <a href="{{ route('superadmin.settings.system') }}" class="sa-search-item">
                    <span><strong>System Settings</strong> — Board parameters & late fee multipliers</span>
                    <span class="sa-kbd">/settings</span>
                </a>
                <a href="{{ route('superadmin.academic-years.index') }}" class="sa-search-item">
                    <span><strong>Academic Years</strong> — Session transition & rollover checklist</span>
                    <span class="sa-kbd">/academic-years</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         MAIN CONTENT AREA
         ═════════════════════════════════════════════════════════════════════ -->
    <main class="sa-main">
        <!-- Flash messages -->
        @if(session('success'))
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                <span><strong>Success:</strong> {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;color:#065f46;cursor:pointer;font-weight:bold;">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                <span><strong>Error:</strong> {{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;color:#991b1b;cursor:pointer;font-weight:bold;">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Global Scripts -->
    <script>
        // Mobile Sidebar Controls
        function toggleSidebar(open) {
            const sidebar = document.getElementById('saSidebar');
            const overlay = document.getElementById('saSidebarOverlay');
            if (open) {
                sidebar.classList.add('open');
                overlay.classList.add('open');
            } else {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
            }
        }

        // Global Search Modal (Ctrl+K)
        function openSearchModal() {
            const modal = document.getElementById('saSearchModal');
            modal.classList.add('active');
            setTimeout(() => document.getElementById('saSearchInput').focus(), 50);
        }

        function closeSearchModal() {
            document.getElementById('saSearchModal').classList.remove('active');
        }

        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                openSearchModal();
            } else if (e.key === 'Escape') {
                closeSearchModal();
                toggleSidebar(false);
            }
        });

        // Debounced Global Search (300ms) with categories: Schools, Students, Invoices, Users, Certificates, Navigation
        let searchDebounceTimer = null;
        const defaultNavHtml = document.getElementById('saSearchResults')?.innerHTML || '';

        function handleSearch(q) {
            const results = document.getElementById('saSearchResults');
            const query = q.trim();

            if (query.length < 2) {
                // Restore default navigation items and filter locally
                results.innerHTML = defaultNavHtml;
                const items = results.querySelectorAll('.sa-search-item');
                const qLower = query.toLowerCase();
                items.forEach(item => {
                    const text = item.innerText.toLowerCase();
                    item.style.display = (query === '' || text.includes(qLower)) ? 'flex' : 'none';
                });
                return;
            }

            // Filter static items immediately for instant responsiveness
            const items = results.querySelectorAll('.sa-search-item');
            const qLower = query.toLowerCase();
            items.forEach(item => {
                const text = item.innerText.toLowerCase();
                item.style.display = text.includes(qLower) ? 'flex' : 'none';
            });

            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                fetch(`{{ route('superadmin.search') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '';

                        // Helper for sections
                        function renderSection(title, list, iconColor = '#1B3A6B') {
                            if (!list || list.length === 0) return '';
                            let sec = `<div class="sa-search-group-title">${title} (${list.length})</div>`;
                            list.forEach(item => {
                                sec += `
                                    <a href="${item.url}" class="sa-search-item">
                                        <div style="display:flex; flex-direction:column; gap:2px;">
                                            <span style="font-weight:700; color:#0f172a;">${item.title}</span>
                                            <span style="font-size:11.5px; color:#64748b;">${item.subtitle}</span>
                                        </div>
                                        <span class="sa-kbd" style="background:#f1f5f9; color:#475569;">${item.badge}</span>
                                    </a>
                                `;
                            });
                            return sec;
                        }

                        html += renderSection('Institutions & Colleges', data.schools);
                        html += renderSection('Students & Candidates', data.students);
                        html += renderSection('Bank Challans & Invoices', data.invoices);
                        html += renderSection('Users & Administrative Staff', data.users);
                        html += renderSection('Certificates Registry', data.certificates);
                        html += renderSection('Portal Modules & Pages', data.navigation);

                        if (!html) {
                            html = `
                                <div style="text-align:center; padding: 36px 20px; color:#94a3b8;">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 8px; display:block;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    <div style="font-size:14px; font-weight:700; color:#475569;">No results found for "${query}"</div>
                                    <div style="font-size:12px; color:#94a3b8; margin-top:4px;">Try searching by SEMIS code, student name, CNIC, or invoice number.</div>
                                </div>
                            `;
                        }

                        results.innerHTML = html;
                    })
                    .catch(err => console.error('Search error:', err));
            }, 300);
        }

        // Counter Animation Utility (easeOutCubic over duration ms)
        function animateCounter(elem, targetVal, duration = 1500) {
            const start = 0;
            const startTime = performance.now();
            const isCurrency = elem.dataset.currency === 'true';

            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // easeOutCubic
                const easeProgress = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(start + (targetVal - start) * easeProgress);

                if (isCurrency) {
                    elem.innerText = 'Rs ' + current.toLocaleString('en-US');
                } else {
                    elem.innerText = current.toLocaleString('en-US');
                }

                if (progress < 1) {
                    requestAnimationFrame(update);
                } else {
                    if (isCurrency) {
                        elem.innerText = 'Rs ' + targetVal.toLocaleString('en-US');
                    } else {
                        elem.innerText = targetVal.toLocaleString('en-US');
                    }
                }
            }

            requestAnimationFrame(update);
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-counter-target]').forEach(elem => {
                const target = parseInt(elem.dataset.counterTarget, 10);
                if (!isNaN(target)) {
                    animateCounter(elem, target);
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
