<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="BISE Sukkur Portal — Student Enrollment and Examination Management" />
    <meta name="author" content="BISE Sukkur" />
    <title>{{ $title ?? config('app.name', 'BISE Sukkur') }}</title>

    <link rel="icon" type="image/png" sizes="32x32" href="/images/bise-sukkur-logo.png?v={{ config('app.version', '1.0') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/bise-sukkur-logo.png?v={{ config('app.version', '1.0') }}">
    <link rel="shortcut icon" href="/images/bise-sukkur-logo.png?v={{ config('app.version', '1.0') }}">
    <link rel="apple-touch-icon" href="/images/bise-sukkur-logo.png?v={{ config('app.version', '1.0') }}">

    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="auth-shell">
        <div class="auth-visual" aria-hidden="true">
            <div class="auth-visual-glow-1"></div>
            <div class="auth-visual-glow-2"></div>
            <div class="auth-visual-grid-pattern"></div>
            <div class="auth-visual-content">
                <div class="auth-visual-header">
                    <span class="auth-visual-gov-tag">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Government of Sindh • Education Department
                    </span>
                </div>
                <div class="auth-visual-hero">
                    <h2 class="auth-visual-title">Board of Intermediate &amp; Secondary Education</h2>
                    <p class="auth-visual-subtitle">Sukkur, Sindh — Central Examination &amp; Candidate Enrollment Portal</p>
                </div>
                <div class="auth-visual-cards">
                    <div class="auth-glass-card">
                        <div class="auth-glass-icon bg-blue-500/20 text-blue-400">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="auth-glass-title">Online Candidate Enrollment</h4>
                            <p class="auth-glass-desc">Real-time SSC &amp; HSC student registration &amp; verification</p>
                        </div>
                    </div>
                    <div class="auth-glass-card">
                        <div class="auth-glass-icon bg-amber-500/20 text-amber-400">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                              <rect x="2" y="5" width="20" height="14" rx="2" />
                              <line x1="2" y1="10" x2="22" y2="10" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="auth-glass-title">Automated Challan Clearing</h4>
                            <p class="auth-glass-desc">Instant bank invoice verification &amp; payment audit trail</p>
                        </div>
                    </div>
                    <div class="auth-glass-card">
                        <div class="auth-glass-icon bg-emerald-500/20 text-emerald-400">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="auth-glass-title">Roll &amp; Seat Allotment Engine</h4>
                            <p class="auth-glass-desc">Secure examination center allocation and gap reporting</p>
                        </div>
                    </div>
                </div>
                <div class="auth-visual-footer">
                    <span>© 2026 BISE Sukkur • All Rights Reserved</span>
                </div>
            </div>
        </div>
        <div class="auth-panel">
            <div class="auth-panel-card">
                <div class="auth-brand">
                    <div class="auth-logo-badge">
                        <img src="/images/bise-sukkur-logo.png" alt="BISE Sukkur Logo" class="auth-logo-image" />
                    </div>
                    <h1 class="auth-title">BISE Sukkur Portal</h1>
                    <p class="auth-subtitle">Sign in to manage enrollment, exam forms &amp; verification</p>
                </div>

                @if(isset($heading) || isset($subheading))
                    <div class="auth-page-heading">
                        @if(isset($heading))
                            <h2 class="auth-page-title">{{ $heading }}</h2>
                        @endif
                        @if(isset($subheading))
                            <p class="auth-page-subtitle">{{ $subheading }}</p>
                        @endif
                    </div>
                @endif

                <div class="auth-form">
                    @yield('content')
                </div>

                <footer class="auth-footer">
                    <div class="auth-security-badge">
                        <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0110 0v4" />
                        </svg>
                        <span>256-Bit SSL Encrypted Connection</span>
                    </div>
                    <p class="auth-footer-contact">For technical assistance, contact BISE Sukkur IT Division</p>
                </footer>
            </div>
        </div>
    </div>
</body>
</html>
