@extends('layouts.superadmin')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Action & Breadcrumb -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <a href="{{ route('superadmin.schools') }}" style="font-size: 13px; color: #64748b; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                <span>Back to Schools Directory</span>
            </a>
            <div style="display: flex; align-items: center; gap: 12px;">
                <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">{{ $school->name }}</h1>
                <span style="font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: 700; background: #0f172a; color: #ffffff; padding: 4px 10px; border-radius: 6px;">
                    {{ $school->username }}
                </span>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('superadmin.schools') }}" class="sa-header-btn">Back</a>
        </div>
    </div>

    <!-- Quick Stats Cards for this School -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px;">
        <div class="sa-panel" style="padding: 18px 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Registered Candidates</div>
            <div style="font-size: 28px; font-weight: 800; color: #0f172a; margin-top: 6px;">{{ number_format($studentCount) }}</div>
        </div>

        <div class="sa-panel" style="padding: 18px 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">With Official Enrollment No.</div>
            <div style="font-size: 28px; font-weight: 800; color: #10b981; margin-top: 6px;">{{ number_format($enrolledCount) }}</div>
        </div>

        <div class="sa-panel" style="padding: 18px 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Enrollment Window Status</div>
            <div style="margin-top: 8px;">
                <span class="sa-chip {{ $enrPhase->isAccessAllowed ? 'sa-chip-green' : 'sa-chip-red' }}">
                    {{ strtoupper($enrPhase->phase) }}
                </span>
                <span style="font-size: 12px; color: #64748b; margin-left: 6px;">{{ $enrPhase->nextTransitionLabel }}</span>
            </div>
        </div>

        <div class="sa-panel" style="padding: 18px 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Examination Window Status</div>
            <div style="margin-top: 8px;">
                <span class="sa-chip {{ $examPhase->isAccessAllowed ? 'sa-chip-green' : 'sa-chip-red' }}">
                    {{ strtoupper($examPhase->phase) }}
                </span>
                <span style="font-size: 12px; color: #64748b; margin-left: 6px;">{{ $examPhase->nextTransitionLabel }}</span>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

        <!-- Main Info -->
        <div class="sa-panel">
            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 18px 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
                Institutional Information
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 13.5px;">
                <div>
                    <span style="color: #64748b; font-size: 12px; display: block;">SEMIS Code:</span>
                    <strong style="font-family: monospace; font-size: 14px;">{{ $school->semis_code ?? 'Not Configured' }}</strong>
                </div>

                <div>
                    <span style="color: #64748b; font-size: 12px; display: block;">District / Tehsil:</span>
                    <strong>{{ $school->district->name ?? '—' }} / {{ $school->tehsil->name ?? 'Main' }}</strong>
                </div>

                <div>
                    <span style="color: #64748b; font-size: 12px; display: block;">Institutional Type:</span>
                    <span class="sa-chip {{ $school->type === 'college' ? 'sa-chip-gold' : 'sa-chip-green' }}">
                        {{ ucfirst($school->type) }} ({{ ucfirst($school->gender) }})
                    </span>
                </div>

                <div>
                    <span style="color: #64748b; font-size: 12px; display: block;">Campus Address:</span>
                    <span>{{ $school->address ?? 'Main Campus, District ' . ($school->district->name ?? '') }}</span>
                </div>
            </div>

            <!-- Allowed Levels Section -->
            <h4 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 24px 0 12px 0;">Configured Allowed Class Levels:</h4>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                @php
                    $levels = $school->getAllowedLevelsList();
                @endphp
                @foreach($levels as $lvl)
                    <span class="sa-chip sa-chip-blue" style="font-size: 12px;">
                        &bull; {{ strtoupper(str_replace('_', ' ', $lvl)) }}
                    </span>
                @endforeach
            </div>
        </div>

        <!-- Head of School & Admin Account -->
        <div class="sa-panel">
            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 18px 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
                Administration & Focal Person
            </h3>

            <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13.5px;">
                <div>
                    <span style="color: #64748b; font-size: 12px; display: block;">Principal / Focal Person:</span>
                    <strong style="font-size: 15px; color: #0f172a;">{{ $school->principal_name ?? 'Principal Office' }}</strong>
                </div>

                <div>
                    <span style="color: #64748b; font-size: 12px; display: block;">Official Phone / Mobile:</span>
                    <span style="font-family: monospace;">{{ $school->phone ?? '—' }}</span>
                </div>

                <div>
                    <span style="color: #64748b; font-size: 12px; display: block;">Official Email:</span>
                    <span>{{ $school->email ?? '—' }}</span>
                </div>

                <div style="margin-top: 12px; padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 11px; font-weight: 700; color: #1B3A6B; text-transform: uppercase;">School Portal Login Account</div>
                    <div style="font-family: monospace; font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 4px;">{{ $school->adminUser->username ?? $school->username }}</div>
                    <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                        Status: <span style="color: {{ ($school->adminUser->is_active ?? true) ? '#10b981' : '#ef4444' }}; font-weight: 600;">{{ ($school->adminUser->is_active ?? true) ? 'Active' : 'Locked' }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
