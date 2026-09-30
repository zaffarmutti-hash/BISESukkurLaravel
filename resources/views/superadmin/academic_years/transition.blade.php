@extends('layouts.superadmin')

@section('title', 'Academic Year Transition & Rollover')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Academic Year Transition & Rollover</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Promote regular candidates across levels, archive graduated cohorts, and safely advance the board's active session.</p>
        </div>
        <div>
            <a href="{{ route('superadmin.academic-years.index') }}" class="sa-btn sa-btn-outline">
                ← Academic Sessions
            </a>
        </div>
    </div>

    <!-- Active Year Status Banner -->
    <div class="sa-paper p-5" style="border-left: 4px solid #1B3A6B; background: linear-gradient(135deg, #f8fafc, #f1f5f9);">
        <div class="d-flex justify-between align-center flex-wrap gap-3">
            <div>
                <span class="sa-badge sa-badge-gold">CURRENT ACTIVE SESSION</span>
                <div style="font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 6px;">
                    {{ $activeYear->label ?? 'No Active Session Configured' }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    All new registrations, invoices, and examination seatings currently tie to this session.
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs font-bold uppercase text-slate-400">Total Enrolled Candidates</div>
                <div style="font-size: 24px; font-weight: 800; color: #1B3A6B;">
                    {{ number_format($preChecks['total_students'] ?? 0) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Pre-Check Diagnostic Cards -->
    <div class="sa-paper p-5">
        <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">
            1. Pre-Transition Integrity Checklist
        </h3>

        @php
            $hasBlockers = ($preChecks['pending_enrollment_verification'] > 0) ||
                           ($preChecks['pending_exam_verification'] > 0) ||
                           ($preChecks['pending_enrollment_numbers'] > 0);
        @endphp

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px;">
            <div class="p-3 rounded-lg border {{ $preChecks['pending_enrollment_verification'] > 0 ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50' }}">
                <div class="text-xs font-bold uppercase {{ $preChecks['pending_enrollment_verification'] > 0 ? 'text-amber-800' : 'text-emerald-800' }}">
                    Unverified Enrollment Challans
                </div>
                <div style="font-size: 22px; font-weight: 800; margin-top: 4px; color: {{ $preChecks['pending_enrollment_verification'] > 0 ? '#b45309' : '#047857' }};">
                    {{ number_format($preChecks['pending_enrollment_verification']) }}
                </div>
                <div class="text-xs text-slate-500 mt-1">Payments awaiting board reconciliation</div>
            </div>

            <div class="p-3 rounded-lg border {{ $preChecks['pending_exam_verification'] > 0 ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50' }}">
                <div class="text-xs font-bold uppercase {{ $preChecks['pending_exam_verification'] > 0 ? 'text-amber-800' : 'text-emerald-800' }}">
                    Unverified Exam Challans
                </div>
                <div style="font-size: 22px; font-weight: 800; margin-top: 4px; color: {{ $preChecks['pending_exam_verification'] > 0 ? '#b45309' : '#047857' }};">
                    {{ number_format($preChecks['pending_exam_verification']) }}
                </div>
                <div class="text-xs text-slate-500 mt-1">Exam fees awaiting confirmation</div>
            </div>

            <div class="p-3 rounded-lg border {{ $preChecks['pending_enrollment_numbers'] > 0 ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50' }}">
                <div class="text-xs font-bold uppercase {{ $preChecks['pending_enrollment_numbers'] > 0 ? 'text-red-800' : 'text-emerald-800' }}">
                    Pending Enrollment Numbers
                </div>
                <div style="font-size: 22px; font-weight: 800; margin-top: 4px; color: {{ $preChecks['pending_enrollment_numbers'] > 0 ? '#b91c1c' : '#047857' }};">
                    {{ number_format($preChecks['pending_enrollment_numbers']) }}
                </div>
                <div class="text-xs text-slate-500 mt-1">Verified candidates lacking roll #</div>
            </div>

            <div class="p-3 rounded-lg border border-slate-200 bg-slate-50">
                <div class="text-xs font-bold uppercase text-slate-700">
                    Missing Exam Forms (Gap)
                </div>
                <div style="font-size: 22px; font-weight: 800; margin-top: 4px; color: #334155;">
                    {{ number_format($preChecks['missing_exam_forms']) }}
                </div>
                <div class="text-xs text-slate-500 mt-1">Enrolled students without exam form</div>
            </div>
        </div>

        @if($hasBlockers)
            <div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-300 text-amber-900 text-xs leading-relaxed">
                <strong>Attention:</strong> Outstanding payment verifications or pending enrollment number allocations were detected. You may still proceed with the rollover, but any unverified candidates will remain associated with the {{ $activeYear->label }} session.
            </div>
        @endif
    </div>

    <!-- Transition Configuration & Action Form -->
    <div class="sa-paper p-5">
        <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">
            2. Target Academic Session & Execution
        </h3>

        <div style="max-width: 600px;">
            <div class="mb-4">
                <label class="sa-form-label">Select Next Academic Year to Activate *</label>
                <select id="next_year_select" class="sa-input">
                    <option value="">-- Choose Target Academic Session --</option>
                    @foreach($years as $y)
                        @if(!$y->is_active)
                            <option value="{{ $y->id }}">{{ $y->label }} ({{ date('Y', strtotime($y->year_start)) }} - {{ date('Y', strtotime($y->year_end)) }})</option>
                        @endif
                    @endforeach
                </select>
                <span class="text-xs text-slate-400 mt-1 block">The newly activated year will automatically receive promoted regular candidates.</span>
            </div>

            <!-- Steps Description -->
            <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 mb-5">
                <div class="font-bold text-slate-900 text-sm mb-2">Automated Rollover Actions:</div>
                <ul class="text-xs text-slate-600 space-y-2 mb-0" style="padding-left: 18px;">
                    <li><strong>Candidate Promotion:</strong> Part-I regular candidates (SSC-I and HSC-I) are promoted to Part-II in the new academic year.</li>
                    <li><strong>Record Expiration:</strong> Graduating cohorts (Part-II) and stale candidates inactive for &gt;2 sessions are archived.</li>
                    <li><strong>Session Switch:</strong> Current session windows are safely closed and the selected new year is made the active tenant anchor.</li>
                </ul>
            </div>

            <!-- Progress Box -->
            <div id="transition_progress_box" style="display: none;" class="mb-5">
                <div class="d-flex justify-between align-center mb-1">
                    <span class="font-bold text-xs text-slate-700" id="progress_step_label">Processing rollover...</span>
                    <span class="font-mono text-xs font-bold text-slate-900" id="progress_percent">0%</span>
                </div>
                <div style="height: 10px; border-radius: 5px; background: #e2e8f0; overflow: hidden;">
                    <div id="progress_bar_fill" style="width: 0%; height: 100%; background: #10b981; transition: width 0.3s ease;"></div>
                </div>
                <div id="progress_log" class="text-xs text-slate-500 mt-2 font-mono" style="max-height: 120px; overflow-y: auto; background: #fff; padding: 8px; border-radius: 6px; border: 1px solid #e2e8f0;"></div>
            </div>

            <div class="d-flex gap-3">
                <button type="button" id="btn_execute_transition" onclick="confirmAndExecute()" class="sa-btn sa-btn-gold">
                    Execute Academic Year Rollover
                </button>
            </div>
        </div>
    </div>
</div>

<script>
async function confirmAndExecute() {
    var select = document.getElementById('next_year_select');
    var nextYearId = select.value;

    if (!nextYearId) {
        alert('Please select the next target academic session first.');
        select.focus();
        return;
    }

    var selectedLabel = select.options[select.selectedIndex].text;
    if (!confirm('CRITICAL ACTION:\nAre you sure you want to transition the entire board system to ' + selectedLabel + '?\n\nThis will promote candidates and switch the global active year.')) {
        return;
    }

    var btn = document.getElementById('btn_execute_transition');
    btn.disabled = true;
    btn.innerText = 'Transition in progress...';

    var progressBox = document.getElementById('transition_progress_box');
    var progressLabel = document.getElementById('progress_step_label');
    var progressPercent = document.getElementById('progress_percent');
    var progressBar = document.getElementById('progress_bar_fill');
    var progressLog = document.getElementById('progress_log');

    progressBox.style.display = 'block';

    function log(msg) {
        var p = document.createElement('div');
        p.innerText = '[' + new Date().toLocaleTimeString() + '] ' + msg;
        progressLog.appendChild(p);
        progressLog.scrollTop = progressLog.scrollHeight;
    }

    var csrf = '{{ csrf_token() }}';
    var promotedCount = 0;
    var expiredCount = 0;

    try {
        // Step 1: Promote
        progressLabel.innerText = 'Step 1/3: Promoting Part-I regular candidates...';
        progressPercent.innerText = '30%';
        progressBar.style.width = '30%';
        log('Starting candidate promotion for next session...');

        var res1 = await fetch('{{ route("superadmin.academic-year-transition.promote") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ next_year_id: parseInt(nextYearId) })
        });
        var data1 = await res1.json();
        if (!res1.ok || data1.error) {
            throw new Error(data1.error || 'Promotion step failed.');
        }
        promotedCount = data1.count || 0;
        log('Promoted ' + promotedCount + ' candidates to Part-II.');

        // Step 2: Expire
        progressLabel.innerText = 'Step 2/3: Archiving completed and inactive records...';
        progressPercent.innerText = '65%';
        progressBar.style.width = '65%';
        log('Archiving graduated and inactive cohorts...');

        var res2 = await fetch('{{ route("superadmin.academic-year-transition.expire") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        });
        var data2 = await res2.json();
        if (!res2.ok || data2.error) {
            throw new Error(data2.error || 'Expiration step failed.');
        }
        expiredCount = data2.count || 0;
        log('Archived ' + expiredCount + ' candidate records.');

        // Step 3: Swap Active Year
        progressLabel.innerText = 'Step 3/3: Swapping system active session...';
        progressPercent.innerText = '90%';
        progressBar.style.width = '90%';
        log('Setting active year flag in database and recording audit log...');

        var res3 = await fetch('{{ route("superadmin.academic-year-transition.swap") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                next_year_id: parseInt(nextYearId),
                promoted_count: promotedCount,
                expired_count: expiredCount
            })
        });
        var data3 = await res3.json();
        if (!res3.ok || data3.error) {
            throw new Error(data3.error || 'Session swap step failed.');
        }

        progressBar.style.width = '100%';
        progressPercent.innerText = '100%';
        progressLabel.innerText = 'Transition Completed Successfully!';
        log('Academic session successfully transitioned!');

        setTimeout(function() {
            window.location.href = data3.redirect || '{{ route("superadmin.dashboard") }}';
        }, 1200);

    } catch (err) {
        log('ERROR: ' + err.message);
        progressBar.style.background = '#ef4444';
        progressLabel.innerText = 'Transition failed. Please check log.';
        btn.disabled = false;
        btn.innerText = 'Retry Transition';
        alert('Rollover failed: ' + err.message);
    }
}
</script>
@endsection
