<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? config('app.name', 'BISE Sukkur') }} - School Portal</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        :root {
            --primary: #1e40af;
            --primary-dark: #1e3a8a;
            --primary-light: #3b82f6;
            --accent: #0284c7;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg-page: #f8fafc;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --sidebar-width: 275px;
            --radius-xl: 18px;
            --radius-lg: 14px;
            --radius-md: 10px;
            --shadow-subtle: 0 4px 14px rgba(15, 23, 42, 0.05);
            --shadow-card: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.03);
            --shadow-hover: 0 20px 30px -10px rgba(15, 23, 42, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* ── App Shell Layout ── */
        .sl-shell {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        }

        /* ── Sidebar ── */
        .sl-sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: #ffffff;
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 40;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 20px rgba(15, 23, 42, 0.03);
        }

        .sl-brand-box {
            padding: 1.25rem 1.25rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.875rem;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .sl-brand-logo {
            width: 44px;
            height: 44px;
            object-fit: contain;
            border-radius: var(--radius-md);
            padding: 2px;
            background: #ffffff;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.15);
            border: 1px solid #dbeafe;
        }

        .sl-brand-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .sl-brand-title {
            font-size: 0.9rem;
            font-weight: 800;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25;
        }

        .sl-brand-sub {
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 3px;
        }

        /* ── Nav Links ── */
        .sl-nav {
            flex: 1;
            padding: 1.25rem 0.875rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .sl-nav-heading {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.08em;
            padding: 0.6rem 0.75rem 0.3rem;
            margin-top: 0.5rem;
        }

        .sl-nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0.875rem;
            border-radius: var(--radius-md);
            text-decoration: none;
            color: #475569;
            font-size: 0.88rem;
            font-weight: 600;
            transition: all 0.18s ease;
            position: relative;
        }

        .sl-nav-item:hover {
            background: #f1f5f9;
            color: var(--text-main);
            transform: translateX(3px);
        }

        .sl-nav-item.active {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: var(--primary);
            font-weight: 700;
        }

        .sl-nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 15%;
            bottom: 15%;
            width: 4px;
            background: var(--primary);
            border-radius: 0 4px 4px 0;
        }

        .sl-nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: currentColor;
        }

        .sl-nav-icon svg {
            width: 100%;
            height: 100%;
        }

        .sl-badge-pill {
            margin-left: auto;
            font-size: 0.7rem;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            font-weight: 700;
        }

        .badge-green {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-blue {
            background: #e0f2fe;
            color: #0369a1;
        }

        /* ── Sidebar Footer ── */
        .sl-sidebar-footer {
            padding: 1rem 1rem 1.25rem;
            border-top: 1px solid var(--border);
            background: #fafafa;
        }

        .sl-user-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.75rem;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            margin-bottom: 0.75rem;
        }

        .sl-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .sl-user-meta {
            min-width: 0;
            flex: 1;
        }

        .sl-user-name {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sl-user-role {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .sl-btn-logout {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.65rem;
            border-radius: var(--radius-md);
            border: 1px solid #fecaca;
            background: #fff5f5;
            color: #dc2626;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: inherit;
        }

        .sl-btn-logout:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
        }

        /* ── Main Area ── */
        .sl-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Top Bar ── */
        .sl-topbar {
            height: 70px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .sl-topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .sl-hamburger {
            display: none;
            width: 38px;
            height: 38px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #ffffff;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-main);
        }

        .sl-breadcrumb {
            font-size: 0.85rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .sl-breadcrumb-active {
            color: var(--text-main);
            font-weight: 600;
        }

        .sl-topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .sl-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #ffffff;
            font-size: 0.84rem;
            font-weight: 700;
            border-radius: 9999px;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(30, 64, 175, 0.3);
            transition: all 0.18s ease;
            border: none;
            cursor: pointer;
        }

        .sl-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.4);
            color: #ffffff;
        }

        .sl-year-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 0.4rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        /* ── Content View ── */
        .sl-content {
            flex: 1;
            padding: 2rem;
        }

        /* ── Responsive Mobile ── */
        .sl-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            z-index: 39;
            backdrop-filter: blur(2px);
        }

        @media (max-width: 991px) {
            .sl-sidebar {
                transform: translateX(-100%);
            }
            .sl-sidebar.open {
                transform: translateX(0);
            }
            .sl-main {
                margin-left: 0;
            }
            .sl-hamburger {
                display: flex;
            }
            .sl-overlay.open {
                display: block;
            }
            .sl-topbar {
                padding: 0 1rem;
            }
            .sl-content {
                padding: 1.25rem 1rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="sl-shell">
        <!-- Mobile Overlay -->
        <div class="sl-overlay" id="slOverlay" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside class="sl-sidebar" id="slSidebar">
            <div class="sl-brand-box">
                <img src="/images/bise-sukkur-logo.png" alt="BISE Sukkur" class="sl-brand-logo" onerror="this.style.display='none'" />
                <div class="sl-brand-info">
                    <p class="sl-brand-title">{{ strtoupper(auth()->user()->school->name ?? 'School Portal') }}</p>
                    <p class="sl-brand-sub">BISE SUKKUR BOARD</p>
                </div>
            </div>

            <nav class="sl-nav">
                <div class="sl-nav-heading">Navigation</div>

                <!-- Home / Dashboard -->
                <a href="{{ route('school.dashboard') }}" class="sl-nav-item {{ request()->routeIs('school.dashboard') ? 'active' : '' }}">
                    <span class="sl-nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </span>
                    <span>Home</span>
                </a>

                <!-- Enrollment Form / List -->
                <a href="{{ route('school.students.index') }}" class="sl-nav-item {{ request()->routeIs('school.students.*') ? 'active' : '' }}">
                    <span class="sl-nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <line x1="20" y1="8" x2="20" y2="14"/>
                            <line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                    </span>
                    <span>Enrollment Form</span>
                </a>

                <!-- Examination Form -->
                <a href="{{ route('school.examination.forms') }}" class="sl-nav-item {{ request()->routeIs('school.examination.*') ? 'active' : '' }}">
                    <span class="sl-nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <line x1="10" y1="9" x2="8" y2="9"/>
                        </svg>
                    </span>
                    <span>Examination Form</span>
                </a>

                <!-- Fee Challans / Invoices -->
                <a href="{{ route('school.invoices') }}" class="sl-nav-item {{ request()->routeIs('school.invoices') || request()->routeIs('school.challan.*') ? 'active' : '' }}">
                    <span class="sl-nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                            <line x1="6" y1="9" x2="10" y2="9"/>
                            <line x1="6" y1="12" x2="14" y2="12"/>
                        </svg>
                    </span>
                    <span>Fee Challans</span>
                </a>
            </nav>

            <div class="sl-sidebar-footer">
                <div class="sl-user-card">
                    <div class="sl-user-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}
                    </div>
                    <div class="sl-user-meta">
                        <div class="sl-user-name">{{ auth()->user()->name ?? 'School Admin' }}</div>
                        <div class="sl-user-role">Code: {{ auth()->user()->school->code ?? 'BISE-SCH' }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sl-btn-logout">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                            <polyline points="16,17 21,12 16,7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="sl-main">
            <!-- Header Top Bar -->
            <header class="sl-topbar">
                <div class="sl-topbar-left">
                    <button class="sl-hamburger" type="button" onclick="toggleSidebar()" aria-label="Toggle Menu">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    @stack('topbar_back')
                    <div class="sl-topbar-school-title" style="display: flex; align-items: center; gap: 0.6rem;">
                        <span style="font-weight: 800; font-size: 1rem; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.04em;">
                            {{ strtoupper(auth()->user()->school->name ?? 'BISE SUKKUR SCHOOL PORTAL') }}
                        </span>
                        @if(auth()->user()->school?->code)
                            <span style="font-size: 0.72rem; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 0.15rem 0.55rem; border-radius: 6px; font-family: monospace;">
                                {{ auth()->user()->school->code }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="sl-topbar-right">
                    <div class="sl-year-chip">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <span>Year: {{ is_array($activeYear) ? ($activeYear['label'] ?? '2026') : ($activeYear->label ?? '2026') }}</span>
                    </div>
                    @stack('topbar_actions')
                </div>
            </header>

            <!-- Page Content -->
            <main class="sl-content">
                @if(session('success'))
                    <div style="background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 0.9rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem; font-weight: 600;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 0.9rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem; font-weight: 600;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById('slSidebar');
            var overlay = document.getElementById('slOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }
    </script>
    @stack('scripts')
</body>
</html>
