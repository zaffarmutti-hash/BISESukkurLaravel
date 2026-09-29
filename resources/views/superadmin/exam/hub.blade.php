@extends('layouts.superadmin')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Examination Management Hub</h1>
            <p style="font-size: 13.5px; color: #64748b; margin: 4px 0 0 0;">Unified governance for secondary & higher secondary board examinations, centers, timetables, seat allotment, and certificates.</p>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('superadmin.exam-invoice-verification') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; font-weight: 600;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Verify Exam Payments</span>
            </a>
            <a href="{{ route('superadmin.examination.seat-allotment.index') }}" class="sa-header-btn" style="background: #C8960C; color: #fff; border: none; font-weight: 600;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                <span>Seat Allotment</span>
            </a>
        </div>
    </div>

    <!-- Quick Hub Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px;">
        <div class="sa-panel" style="padding: 18px 20px;">
            <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Pending Exam Verifications</div>
            <div style="font-size: 26px; font-weight: 800; color: {{ ($stats['pending_verification'] ?? 0) > 0 ? '#ef4444' : '#0f172a' }}; margin-top: 4px;">
                {{ number_format($stats['pending_verification'] ?? 0) }}
            </div>
        </div>

        <div class="sa-panel" style="padding: 18px 20px;">
            <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Missing Exam Forms (Gap)</div>
            <div style="font-size: 26px; font-weight: 800; color: {{ ($stats['missing_exam_forms'] ?? 0) > 0 ? '#f59e0b' : '#0f172a' }}; margin-top: 4px;">
                {{ number_format($stats['missing_exam_forms'] ?? 0) }}
            </div>
        </div>

        <div class="sa-panel" style="padding: 18px 20px;">
            <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Exam Centers Configured</div>
            <div style="font-size: 26px; font-weight: 800; color: #10b981; margin-top: 4px;">
                {{ number_format(count($centers)) }}
            </div>
        </div>

        <div class="sa-panel" style="padding: 18px 20px;">
            <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Scheduled Papers</div>
            <div style="font-size: 26px; font-weight: 800; color: #1B3A6B; margin-top: 4px;">
                {{ number_format(count($timetables)) }}
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 8px; border-bottom: 2px solid #e2e8f0; overflow-x: auto; padding-bottom: 2px;">
        <button type="button" onclick="switchTab('window')" id="btn-tab-window" class="sa-tab-btn {{ ($activeTab ?? 'window') === 'window' ? 'active' : '' }}">
            <span>Window Settings</span>
        </button>

        <button type="button" onclick="switchTab('fees')" id="btn-tab-fees" class="sa-tab-btn {{ ($activeTab ?? '') === 'fees' ? 'active' : '' }}">
            <span>Exam Fees</span>
        </button>

        <button type="button" onclick="switchTab('seats')" id="btn-tab-seats" class="sa-tab-btn {{ ($activeTab ?? '') === 'seats' ? 'active' : '' }}">
            <span>Seat Allotment</span>
        </button>

        <button type="button" onclick="switchTab('centers')" id="btn-tab-centers" class="sa-tab-btn {{ ($activeTab ?? '') === 'centers' ? 'active' : '' }}">
            <span>Exam Centers</span>
        </button>

        <button type="button" onclick="switchTab('timetable')" id="btn-tab-timetable" class="sa-tab-btn {{ ($activeTab ?? '') === 'timetable' ? 'active' : '' }}">
            <span>Timetable</span>
        </button>

        <button type="button" onclick="switchTab('results')" id="btn-tab-results" class="sa-tab-btn {{ ($activeTab ?? '') === 'results' ? 'active' : '' }}">
            <span>Result Entry</span>
        </button>

        <button type="button" onclick="switchTab('reports')" id="btn-tab-reports" class="sa-tab-btn {{ ($activeTab ?? '') === 'reports' ? 'active' : '' }}">
            <span>Exam Reports</span>
        </button>

        <button type="button" onclick="switchTab('certificates')" id="btn-tab-certificates" class="sa-tab-btn {{ ($activeTab ?? '') === 'certificates' ? 'active' : '' }}">
            <span>Certificates</span>
        </button>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 1: EXAM WINDOW SETTINGS
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="tab-window" class="sa-tab-pane {{ ($activeTab ?? 'window') === 'window' ? 'active' : '' }}">
        @php
            $phaseName = $globalPhase->phase ?? 'closed';
            $isGrace = ($phaseName === 'grace');
            $isOpen = ($phaseName === 'normal');
        @endphp
        <div style="border-radius: 16px; padding: 24px 28px; color: #fff; margin-bottom: 24px;
            background: {{ $isOpen ? 'linear-gradient(135deg, #059669, #10b981)' : ($isGrace ? 'linear-gradient(135deg, #d97706, #f59e0b)' : 'linear-gradient(135deg, #dc2626, #ef4444)') }}; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.9;">Current Global Examination Window Status</div>
            <div style="font-size: 30px; font-weight: 800; margin: 4px 0 8px 0;">
                {{ $isOpen ? 'Examination Window is OPEN' : ($isGrace ? 'Grace Period ACTIVE (Late Examination Surcharge)' : 'Examination Window is CLOSED') }}
            </div>
            <div style="font-size: 14px; opacity: 0.95;">
                {{ $globalPhase->nextTransitionLabel ?? 'No active schedule transition.' }}
            </div>
        </div>

        <div class="sa-panel" style="max-width: 600px;">
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                Global Examination Period
            </h3>
            <form method="POST" action="{{ route('superadmin.academic-years.window-dates', $activeYear->id ?? 1) }}">
                @csrf
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Exam Window Open Switch</label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; cursor: pointer;">
                            <input type="checkbox" name="examination_window_open" value="1" {{ ($activeYear->examination_window_open ?? false) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #10b981;">
                            <span>Examination Window Open</span>
                        </label>
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Exam Period Start</label>
                        <input type="datetime-local" name="examination_start" value="{{ $activeYear->examination_start ? $activeYear->examination_start->format('Y-m-d\TH:i') : '' }}" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Exam Normal End</label>
                        <input type="datetime-local" name="examination_normal_end" value="{{ $activeYear->examination_normal_end ? $activeYear->examination_normal_end->format('Y-m-d\TH:i') : '' }}" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Exam Grace End</label>
                        <input type="datetime-local" name="examination_grace_end" value="{{ $activeYear->examination_grace_end ? $activeYear->examination_grace_end->format('Y-m-d\TH:i') : '' }}" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    </div>

                    <button type="submit" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; justify-content: center; font-weight: 700; padding: 10px 0; margin-top: 8px;">
                        Save Exam Window Dates
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 2: EXAM FEES
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="tab-fees" class="sa-tab-pane {{ ($activeTab ?? '') === 'fees' ? 'active' : '' }}">
        <div class="sa-panel">
            <h3 class="sa-panel-title" style="margin-bottom: 16px;">
                <span>Examination Fee Schedule Matrix</span>
            </h3>

            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Class Level</th>
                        <th>Student Category</th>
                        <th style="text-align: right;">Standard Exam Fee (Rs)</th>
                        <th style="text-align: right;">Late Fee Surcharge (Rs)</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fees as $f)
                        <tr>
                            <td><strong>{{ strtoupper(str_replace('_', ' ', $f->class_level)) }}</strong></td>
                            <td><span class="sa-chip sa-chip-blue">{{ ucfirst($f->student_type) }}</span></td>
                            <td style="text-align: right; font-family: monospace; font-weight: 700; color: #0f172a;">Rs {{ number_format($f->amount_paisas / 100, 2) }}</td>
                            <td style="text-align: right; font-family: monospace; color: #f59e0b;">+ Rs {{ number_format(($f->late_fee_surcharge_paisas ?? 0) / 100, 2) }}</td>
                            <td style="text-align: center;">
                                <span class="sa-chip {{ $f->is_active ? 'sa-chip-green' : 'sa-chip-red' }}">{{ $f->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94a3b8; padding: 24px;">No exam fee rates configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 3: SEAT NUMBER ALLOTMENT
         ═════════════════════════════════════════════ -->
    <div id="tab-seats" class="sa-tab-pane {{ ($activeTab ?? '') === 'seats' ? 'active' : '' }}">
        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 24px;">
            <div class="sa-panel">
                <h3 class="sa-panel-title" style="margin-bottom: 16px;">Seat Allotment Engine</h3>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <div style="font-size: 12px; color: #64748b;">Total Verified Candidates:</div>
                        <div style="font-size: 24px; font-weight: 800; color: #0f172a;">{{ number_format($eligibleSeatCount) }}</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #64748b;">Assigned Seat Numbers:</div>
                        <div style="font-size: 24px; font-weight: 800; color: #10b981;">{{ number_format($assignedSeatCount) }}</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #64748b;">Pending Seat Assignment:</div>
                        <div style="font-size: 24px; font-weight: 800; color: #ef4444;">{{ number_format($pendingSeatCount) }}</div>
                    </div>
                    <a href="{{ route('superadmin.examination.seat-allotment.index') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; font-weight: 700; justify-content: center; padding: 12px 0;">
                        Open Seat Number Assignment Wizard &rarr;
                    </a>
                </div>
            </div>

            <div class="sa-panel">
                <h3 class="sa-panel-title" style="margin-bottom: 14px;">Allotment Strategies</h3>
                <div style="font-size: 13px; color: #334155; line-height: 1.6;">
                    <p><strong>1. By School:</strong> Each school receives a contiguous block of roll numbers sorted by GR/Name.</p>
                    <p><strong>2. Alphabetical:</strong> Candidates are sequenced alphabetically across the entire board district.</p>
                    <p><strong>3. By Center:</strong> Seat numbers are assigned contiguously per assigned examination center hall capacity.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 4: EXAM CENTERS
         ═════════════════════════════════════════════ -->
    <div id="tab-centers" class="sa-tab-pane {{ ($activeTab ?? '') === 'centers' ? 'active' : '' }}">
        <div class="sa-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 class="sa-panel-title">
                    <span>Configured Board Examination Centers</span>
                </h3>
                <a href="{{ route('superadmin.examination.centers.index') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; font-weight: 600; font-size: 12px;">
                    Manage Centers & School Assignments &rarr;
                </a>
            </div>

            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Center Code</th>
                        <th>Center Name</th>
                        <th>District</th>
                        <th>Invigilator / Focal</th>
                        <th style="text-align: center;">Capacity</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($centers as $c)
                        <tr>
                            <td><span style="font-family: monospace; font-weight: 700; color: #1B3A6B;">{{ $c->code }}</span></td>
                            <td><strong>{{ $c->name }}</strong></td>
                            <td>{{ $c->district->name ?? '—' }}</td>
                            <td>{{ $c->invigilator_name ?? 'Center Superintendent' }}</td>
                            <td style="text-align: center; font-weight: 700;">{{ number_format($c->capacity) }} seats</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94a3b8; padding: 24px;">No examination centers registered.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 5: TIMETABLE
         ═════════════════════════════════════════════ -->
    <div id="tab-timetable" class="sa-tab-pane {{ ($activeTab ?? '') === 'timetable' ? 'active' : '' }}">
        <div class="sa-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 class="sa-panel-title"><span>Date Sheet & Examination Timetable</span></h3>
                <a href="{{ route('superadmin.examination.timetable.index') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; font-weight: 600; font-size: 12px;">
                    Schedule New Paper &rarr;
                </a>
            </div>

            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Class Level</th>
                        <th>Subject Name</th>
                        <th>Subject Code</th>
                        <th>Exam Date</th>
                        <th>Session Time</th>
                        <th style="text-align: center;">Max Marks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timetables as $t)
                        <tr>
                            <td><span class="sa-chip sa-chip-blue">{{ strtoupper($t->class_level) }}</span></td>
                            <td><strong>{{ $t->subject_name }}</strong></td>
                            <td style="font-family: monospace;">{{ $t->subject_code }}</td>
                            <td>{{ $t->exam_date ? $t->exam_date->format('d-M-Y') : '—' }}</td>
                            <td>{{ $t->start_time }} – {{ $t->end_time }}</td>
                            <td style="text-align: center; font-weight: 700;">{{ $t->total_marks }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 24px;">No exam timetable papers scheduled for this session.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 6: RESULT ENTRY
         ═════════════════════════════════════════════ -->
    <div id="tab-results" class="sa-tab-pane {{ ($activeTab ?? '') === 'results' ? 'active' : '' }}">
        <div class="sa-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 class="sa-panel-title"><span>Examination Results Processing</span></h3>
                <a href="{{ route('superadmin.examination.results.index') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; font-weight: 600; font-size: 12px;">
                    Open Result Ledger Console &rarr;
                </a>
            </div>

            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Enrollment No.</th>
                        <th>Total Obtained Marks</th>
                        <th>Grade</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentResults as $r)
                        <tr>
                            <td><strong>{{ $r->student->full_name ?? 'Candidate' }}</strong></td>
                            <td style="font-family: monospace;">{{ $r->student->enrollment_number ?? '—' }}</td>
                            <td style="font-weight: 700;">{{ $r->marks_obtained ?? 0 }}</td>
                            <td><span class="sa-chip sa-chip-gold">{{ $r->grade ?? 'A' }}</span></td>
                            <td><span class="sa-chip sa-chip-green">{{ ucfirst($r->status ?? 'Passed') }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94a3b8; padding: 24px;">No marks sheets submitted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 7: EXAM REPORTS
         ═════════════════════════════════════════════ -->
    <div id="tab-reports" class="sa-tab-pane {{ ($activeTab ?? '') === 'reports' ? 'active' : '' }}">
        <div class="sa-panel">
            <h3 class="sa-panel-title" style="margin-bottom: 16px;">
                <span>Examination Metrics & District Gap Analysis</span>
            </h3>

            <table class="sa-table">
                <thead>
                    <tr>
                        <th>District</th>
                        <th style="text-align: center;">Schools</th>
                        <th style="text-align: right;">Total Candidates</th>
                        <th style="text-align: center;">Missing Exam Forms</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($districtStats as $d)
                        <tr>
                            <td><strong>{{ $d['name'] }}</strong></td>
                            <td style="text-align: center;">{{ number_format($d['school_count']) }}</td>
                            <td style="text-align: right; font-weight: 700;">{{ number_format($d['student_count']) }}</td>
                            <td style="text-align: center;">
                                <span class="sa-chip {{ $d['missing_exam_forms'] > 0 ? 'sa-chip-red' : 'sa-chip-green' }}">{{ $d['missing_exam_forms'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 8: CERTIFICATES
         ═════════════════════════════════════════════ -->
    <div id="tab-certificates" class="sa-tab-pane {{ ($activeTab ?? '') === 'certificates' ? 'active' : '' }}">
        <div class="sa-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 class="sa-panel-title"><span>Issued Secondary & Higher Secondary Certificates</span></h3>
                <a href="{{ route('superadmin.examination.certificates.index') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; border: none; font-weight: 600; font-size: 12px;">
                    Generate Certificates &rarr;
                </a>
            </div>

            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Certificate No.</th>
                        <th>Student Name</th>
                        <th>School / College</th>
                        <th>Passing Session</th>
                        <th>Verification Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($certificates as $cert)
                        <tr>
                            <td><span style="font-family: monospace; font-weight: 800; color: #C8960C;">{{ $cert->certificate_number }}</span></td>
                            <td><strong>{{ $cert->student->full_name ?? '—' }}</strong></td>
                            <td>{{ $cert->student->school->name ?? '—' }}</td>
                            <td>{{ $cert->passing_year ?? '2026' }}</td>
                            <td><span class="sa-chip sa-chip-green">Authentic & Verified</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94a3b8; padding: 24px;">No certificates issued yet for this session.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('styles')
<style>
    .sa-tab-btn {
        background: transparent;
        border: none;
        padding: 10px 18px;
        font-size: 13.5px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        border-radius: 8px 8px 0 0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        border-bottom: 3px solid transparent;
        white-space: nowrap;
    }
    .sa-tab-btn:hover {
        color: #1B3A6B;
        background: #f1f5f9;
    }
    .sa-tab-btn.active {
        color: #1B3A6B;
        border-bottom-color: #C8960C;
        background: #ffffff;
    }
    .sa-tab-pane {
        display: none;
    }
    .sa-tab-pane.active {
        display: block;
    }
</style>
@endpush

@push('scripts')
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.sa-tab-pane').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.sa-tab-btn').forEach(el => el.classList.remove('active'));

        const targetPane = document.getElementById('tab-' + tabId);
        const targetBtn = document.getElementById('btn-tab-' + tabId);

        if (targetPane) targetPane.classList.add('active');
        if (targetBtn) targetBtn.classList.add('active');

        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    }
</script>
@endpush
@endsection
