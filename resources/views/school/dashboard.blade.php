@extends('layouts.school')

@section('content')
<style>
    .db-grid {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .db-header {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        align-items: center;
        gap: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border);
    }

    .db-school-name {
        grid-column: 1;
        margin: 0;
        color: var(--text-main);
        font-size: 1.2rem;
        font-weight: 800;
        line-height: 1.3;
        text-transform: uppercase;
    }

    .db-year {
        grid-column: 2;
        grid-row: 1;
        color: var(--text-muted);
        font-size: 0.9rem;
        font-weight: 700;
        text-align: center;
        white-space: nowrap;
    }

    .db-stats-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        min-width: 0;
    }

    .db-overview {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
        align-items: start;
        gap: 1rem;
    }

    .db-stat-box {
        background: linear-gradient(135deg, #f0f7ff 0%, #f8faff 100%);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-card);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .db-stat-box:nth-child(2) { background: linear-gradient(135deg, #effcf7 0%, #f8fdfb 100%); }
    .db-stat-box:nth-child(3) { background: linear-gradient(135deg, #fff4f1 0%, #fffaf8 100%); }
    .db-stat-box:nth-child(4) { background: linear-gradient(135deg, #fff8e9 0%, #fffdf6 100%); }

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
    .icon-green { background: #ecfdf5; color: #059669; }
    .icon-teal { background: #f0fdfa; color: #0f766e; }
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

    .db-stat-value-money {
        font-size: 1.65rem;
    }

    .db-breakdown {
        width: 100%;
        overflow-x: auto;
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        background: linear-gradient(135deg, #f7faff 0%, #ffffff 100%);
        box-shadow: var(--shadow-card);
    }

    .db-enrollment-chart {
        display: flex;
        align-items: center;
        gap: 2rem;
        width: 100%;
        min-width: 0;
        padding: 1.25rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        background: linear-gradient(135deg, #f3fbf8 0%, #fbfefd 100%);
        box-shadow: var(--shadow-card);
    }

    .db-donut-svg {
        width: 190px;
        height: 190px;
        flex: 0 0 190px;
        overflow: visible;
    }

    .db-donut-track {
        fill: none;
        stroke: #e5e7eb;
        stroke-width: 24;
    }

    .db-donut-segment {
        fill: none;
        stroke-width: 24;
        stroke-linecap: butt;
    }

    .db-donut-center-value {
        fill: var(--text-main);
        font-size: 27px;
        font-weight: 800;
        text-anchor: middle;
    }

    .db-donut-center-label {
        fill: var(--text-muted);
        font-size: 11px;
        font-weight: 700;
        text-anchor: middle;
    }

    .db-chart-legend {
        display: grid;
        gap: 0.75rem;
        min-width: 0;
        flex: 1;
    }

    .db-chart-title {
        margin: 0 0 0.15rem;
        color: var(--text-main);
        font-size: 1rem;
        font-weight: 800;
    }

    .db-chart-legend-item {
        display: grid;
        grid-template-columns: 10px minmax(0, 1fr) auto;
        align-items: center;
        gap: 0.6rem;
        color: var(--text-main);
        font-size: 0.85rem;
    }

    .db-chart-swatch {
        width: 10px;
        height: 10px;
        border-radius: 2px;
    }

    .db-chart-count {
        color: var(--text-muted);
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .db-breakdown table {
        width: 100%;
        min-width: 680px;
        border-collapse: collapse;
        text-align: left;
        table-layout: fixed;
    }

    .db-breakdown th:first-child,
    .db-breakdown td:first-child {
        width: 112px;
    }

    .db-breakdown caption {
        padding: 1rem 1.25rem;
        color: var(--text-main);
        font-size: 1rem;
        font-weight: 800;
        text-align: left;
    }

    .db-breakdown th,
    .db-breakdown td {
        padding: 0.8rem 1.25rem;
        border-top: 1px solid var(--border);
        white-space: nowrap;
    }

    .db-breakdown th {
        color: var(--text-muted);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .db-breakdown td {
        color: var(--text-main);
        font-size: 0.88rem;
    }

    .db-breakdown tfoot td {
        font-weight: 800;
        background: #f8fafc;
    }

    @media (max-width: 600px) {
        .db-overview {
            grid-template-columns: minmax(0, 1fr);
        }

        .db-header {
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        }

        .db-school-name {
            font-size: 1rem;
        }

        .db-year {
            grid-column: 2;
            font-size: 0.8rem;
        }

        .db-stat-box {
            padding: 1rem;
        }

        .db-stat-label {
            font-size: 0.67rem;
        }

        .db-stat-value {
            font-size: 1.65rem;
        }

        .db-stat-value-money {
            font-size: 1.25rem;
        }

        .db-enrollment-chart {
            gap: 1rem;
            padding: 1rem;
        }

        .db-donut-svg {
            width: 140px;
            height: 140px;
            flex-basis: 140px;
        }
    }

    @media (max-width: 420px) {
        .db-enrollment-chart {
            flex-direction: column;
            align-items: flex-start;
        }

        .db-donut-svg {
            align-self: center;
        }
    }
</style>

<div class="db-grid">
    <header class="db-header">
        <h1 class="db-school-name">{{ strtoupper(auth()->user()->school->name ?? 'Government School / College') }}</h1>
        <div class="db-year">{{ $activeYear['label'] ?? 'No Active Year' }}</div>
    </header>

    <div class="db-overview">
    <div class="db-stats-grid">
        <div class="db-stat-box">
            <div class="db-stat-header">
                <span class="db-stat-label">Total Enrollment</span>
                <div class="db-stat-icon-wrap icon-blue">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
            </div>
            <div class="db-stat-value">{{ number_format($stats['total_students'] ?? 0) }}</div>
            <div class="db-stat-sub">Academic records this year</div>
        </div>

        <div class="db-stat-box">
            <div class="db-stat-header">
                <span class="db-stat-label">Total Examination</span>
                <div class="db-stat-icon-wrap icon-teal">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </div>
            </div>
            <div class="db-stat-value">{{ number_format($stats['total_examinations'] ?? 0) }}</div>
            <div class="db-stat-sub">Exam forms this year</div>
        </div>

        <div class="db-stat-box">
            <div class="db-stat-header">
                <span class="db-stat-label">Fees Paid</span>
                <div class="db-stat-icon-wrap icon-green">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
            </div>
            <div class="db-stat-value db-stat-value-money">Rs {{ number_format($stats['fees_paid'] ?? 0, 2) }}</div>
            <div class="db-stat-sub">Confirmed and verified invoices</div>
        </div>

        <div class="db-stat-box">
            <div class="db-stat-header">
                <span class="db-stat-label">Fees Payable</span>
                <div class="db-stat-icon-wrap icon-amber">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                    </svg>
                </div>
            </div>
            <div class="db-stat-value db-stat-value-money">Rs {{ number_format($stats['fees_payable'] ?? 0, 2) }}</div>
            <div class="db-stat-sub">Outstanding invoice balance</div>
        </div>
    </div>

    @php
        $enrollmentTotal = (int) $classBreakdown->sum('enrollment');
        $chartColors = ['#2563eb', '#0f766e', '#d97706', '#db2777'];
        $chartCircumference = 2 * pi() * 78;
        $chartOffset = 0;
    @endphp
    <section class="db-enrollment-chart" aria-labelledby="enrollment-chart-title">
        <svg class="db-donut-svg" viewBox="0 0 200 200" role="img" aria-label="Enrollment distribution by class">
            <circle class="db-donut-track" cx="100" cy="100" r="78" />
            @foreach ($classBreakdown as $index => $row)
                @php
                    $segmentLength = $enrollmentTotal > 0 ? ($row['enrollment'] / $enrollmentTotal) * $chartCircumference : 0;
                    $visibleSegmentLength = max(0, $segmentLength - ($row['enrollment'] > 0 ? 3 : 0));
                    $segmentOffset = $chartOffset;
                    $chartOffset += $segmentLength;
                @endphp
                @if ($visibleSegmentLength > 0)
                    <circle
                        class="db-donut-segment"
                        cx="100"
                        cy="100"
                        r="78"
                        stroke="{{ $chartColors[$index % count($chartColors)] }}"
                        stroke-dasharray="{{ $visibleSegmentLength }} {{ $chartCircumference }}"
                        stroke-dashoffset="{{ -$segmentOffset }}"
                        transform="rotate(-90 100 100)"
                    >
                        <title>{{ $row['class'] }}: {{ number_format($row['enrollment']) }}</title>
                    </circle>
                @endif
            @endforeach
            <text class="db-donut-center-value" x="100" y="97">{{ number_format($enrollmentTotal) }}</text>
            <text class="db-donut-center-label" x="100" y="117">ENROLLED</text>
        </svg>
        <div class="db-chart-legend">
            <h2 class="db-chart-title" id="enrollment-chart-title">Enrollment by Class</h2>
            @foreach ($classBreakdown as $index => $row)
                <div class="db-chart-legend-item">
                    <span class="db-chart-swatch" style="background: {{ $chartColors[$index % count($chartColors)] }}" aria-hidden="true"></span>
                    <span>{{ $row['class'] }}</span>
                    <span class="db-chart-count">{{ number_format($row['enrollment']) }}</span>
                </div>
            @endforeach
        </div>
    </section>
    </div>

    <div class="db-breakdown">
        <table>
            <caption>Class-wise Summary</caption>
            <thead>
                <tr>
                    <th scope="col">Class</th>
                    <th scope="col">Total Enrollment</th>
                    <th scope="col">Total Examination</th>
                    <th scope="col">Fees Paid</th>
                    <th scope="col">Fees Payable</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($classBreakdown as $row)
                    <tr>
                        <td>{{ $row['class'] }}</td>
                        <td>{{ number_format($row['enrollment']) }}</td>
                        <td>{{ number_format($row['examinations']) }}</td>
                        <td>Rs {{ number_format($row['fees_paid'], 2) }}</td>
                        <td>Rs {{ number_format($row['fees_payable'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td>{{ number_format($stats['total_students'] ?? 0) }}</td>
                    <td>{{ number_format($stats['total_examinations'] ?? 0) }}</td>
                    <td>Rs {{ number_format($stats['fees_paid'] ?? 0, 2) }}</td>
                    <td>Rs {{ number_format($stats['fees_payable'] ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
