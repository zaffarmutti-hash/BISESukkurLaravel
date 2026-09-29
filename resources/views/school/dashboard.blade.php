@extends('layouts.school')

@section('content')
<style>
    /* ── Dashboard Styles ── */
    .db-grid {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }

    .db-hero-section {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .db-hero-card {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%);
        color: #ffffff;
        border-radius: var(--radius-xl);
        padding: 2.25rem 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 14px 30px rgba(30, 58, 138, 0.25);
    }

    .db-hero-card::after {
        content: '';
        position: absolute;
        right: -30px;
        bottom: -30px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .db-badge-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(8px);
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    .db-hero-title {
        font-size: 1.85rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
    }

    .db-hero-desc {
        color: #dbeafe;
        font-size: 0.95rem;
        max-width: 600px;
        margin-bottom: 1.5rem;
    }

    .db-phases-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .db-phase-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-size: 0.82rem;
        font-weight: 700;
        background: rgba(15, 23, 42, 0.25);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .db-phase-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .dot-open { background: #34d399; box-shadow: 0 0 10px #34d399; }
    .dot-amber { background: #fbbf24; box-shadow: 0 0 10px #fbbf24; }
    .dot-closed { background: #f87171; box-shadow: 0 0 10px #f87171; }

    /* ── Metric Cards Grid ── */
    .db-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
    }

    .db-stat-box {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-card);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .db-stat-box:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
    }

    .db-stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .db-stat-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-muted);
    }

    .db-stat-icon-wrap {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .icon-blue { background: #eff6ff; color: #2563eb; }
    .icon-purple { background: #faf5ff; color: #9333ea; }
    .icon-cyan { background: #ecfeff; color: #0891b2; }
    .icon-amber { background: #fffbeb; color: #d97706; }

    .db-stat-value {
        font-size: 2.15rem;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.02em;
        line-height: 1.1;
        margin-bottom: 0.4rem;
    }

    .db-stat-sub {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    @media (max-width: 1200px) {
        .db-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .db-stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="db-grid">
    <!-- Top Hero -->
    <div class="db-hero-section">
        <div class="db-hero-card">
            <span class="db-badge-kicker">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
                BISE Sukkur School Workspace
            </span>
            <h1 class="db-hero-title">{{ strtoupper(auth()->user()->school->name ?? 'Government School / College') }}</h1>
            <p class="db-hero-desc">
                School Center Code: <strong>{{ auth()->user()->school->code ?? 'N/A' }}</strong> &bull;
                Academic Session: <strong>{{ is_array($activeYear) ? ($activeYear['label'] ?? '2026') : ($activeYear->label ?? '2026') }}</strong>
            </p>

            <div class="db-phases-bar">
                @php
                    $enrPhase = is_array($activeYear) ? ($activeYear['enrollment_phase']['phase'] ?? 'normal') : ($activeYear->enrollment_phase['phase'] ?? 'normal');
                    $examPhase = is_array($activeYear) ? ($activeYear['examination_phase']['phase'] ?? 'normal') : ($activeYear->examination_phase['phase'] ?? 'normal');
                @endphp
                <div class="db-phase-tag">
                    <span class="db-phase-dot {{ $enrPhase === 'normal' ? 'dot-open' : ($enrPhase === 'grace' ? 'dot-amber' : 'dot-closed') }}"></span>
                    <span>Enrollment: {{ $enrPhase === 'normal' ? 'Open (Normal Fee)' : ($enrPhase === 'grace' ? 'Late Fee Window' : 'Closed') }}</span>
                </div>

                <div class="db-phase-tag">
                    <span class="db-phase-dot {{ $examPhase === 'normal' ? 'dot-open' : ($examPhase === 'grace' ? 'dot-amber' : 'dot-closed') }}"></span>
                    <span>Exam: {{ $examPhase === 'normal' ? 'Open' : ($examPhase === 'grace' ? 'Late Fee Window' : 'Closed') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="db-stats-grid">
        <div class="db-stat-box">
            <div class="db-stat-header">
                <span class="db-stat-label">Total Candidates</span>
                <div class="db-stat-icon-wrap icon-blue">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
            </div>
            <div class="db-stat-value">{{ number_format($stats['total_students'] ?? 0) }}</div>
            <div class="db-stat-sub">Enrolled in current session</div>
        </div>

        <div class="db-stat-box">
            <div class="db-stat-header">
                <span class="db-stat-label">SSC / Matric Candidates</span>
                <div class="db-stat-icon-wrap icon-purple">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </div>
            </div>
            <div class="db-stat-value">{{ number_format($stats['ssc_students'] ?? 0) }}</div>
            <div class="db-stat-sub">9th & 10th Class students</div>
        </div>

        <div class="db-stat-box">
            <div class="db-stat-header">
                <span class="db-stat-label">HSC / Inter Candidates</span>
                <div class="db-stat-icon-wrap icon-cyan">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
            </div>
            <div class="db-stat-value">{{ number_format($stats['hsc_students'] ?? 0) }}</div>
            <div class="db-stat-sub">11th & 12th Class students</div>
        </div>

        <div class="db-stat-box">
            <div class="db-stat-header">
                <span class="db-stat-label">Fee Challans</span>
                <div class="db-stat-icon-wrap icon-amber">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                    </svg>
                </div>
            </div>
            <div class="db-stat-value">{{ number_format($stats['total_challans'] ?? 0) }}</div>
            <div class="db-stat-sub">{{ number_format($stats['pending_enrollment_challans'] ?? 0) }} pending verification</div>
        </div>
    </div>
</div>
@endsection
