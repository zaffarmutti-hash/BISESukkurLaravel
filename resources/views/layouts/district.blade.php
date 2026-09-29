<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? config('app.name', 'BISE Sukkur') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-slate-50">
    <div class="district-shell">
        <aside class="district-sidebar">
            <div class="district-brand">
                <img src="/images/bise-sukkur-logo.png" alt="BISE Sukkur" class="district-brand-logo" />
                <div>
                    <p class="district-brand-title">{{ auth()->user()->district->name ?? 'District Admin' }}</p>
                    <p class="district-brand-sub">BISE Sukkur Portal</p>
                </div>
            </div>

            <nav class="district-nav">
                <a href="{{ route('district.dashboard') }}" class="district-nav-item{{ request()->routeIs('district.dashboard') ? ' active' : '' }}">Dashboard</a>
                <a href="{{ route('district.schools') }}" class="district-nav-item{{ request()->routeIs('district.schools*') ? ' active' : '' }}">Schools</a>
                <a href="{{ route('district.reports') }}" class="district-nav-item{{ request()->routeIs('district.reports') ? ' active' : '' }}">Reports</a>
                <a href="{{ route('district.announcements') }}" class="district-nav-item{{ request()->routeIs('district.announcements') ? ' active' : '' }}">Announcements</a>
            </nav>

            <div class="district-sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="district-logout">Sign Out</button>
                </form>
            </div>
        </aside>

        <main class="district-main">
            <header class="district-header">
                <div>
                    <p class="district-page-tag">{{ $breadcrumb ?? 'Dashboard' }}</p>
                    <h1 class="district-page-title">{{ $title ?? 'District Dashboard' }}</h1>
                </div>
                @if(session('success'))
                    <div class="district-flash district-flash-success">{{ session('success') }}</div>
                @endif
            </header>

            <div class="district-content">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
