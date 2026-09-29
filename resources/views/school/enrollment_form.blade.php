@extends('layouts.school')
@section('title', 'Enrollment Form')

@push('styles')
<style>
        :root {
            --primary: #1e40af;
            --primary-dark: #172554;
            --primary-light: #3b82f6;
            --accent-gold: #d97706;
            --accent-green: #059669;
            --bg-page: #f1f5f9;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
            --focus-ring: rgba(37, 99, 235, 0.22);
            --radius-xl: 18px;
            --radius-lg: 12px;
            --radius-md: 8px;
            --error-color: #dc2626;
            --success-color: #16a34a;
            --shadow-subtle: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.03);
            --shadow-card: 0 10px 25px -5px rgba(15, 23, 42, 0.06), 0 8px 10px -6px rgba(15, 23, 42, 0.03);
            --shadow-hover: 0 20px 30px -10px rgba(15, 23, 42, 0.12);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: transparent;
            color: var(--text-main);
            line-height: 1.4;
        }

        .portal-wrapper {
            max-width: 100%;
            margin: 0;
        }

        /* ── Top Government Header Banner ── */
        .portal-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%);
            color: #ffffff;
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            box-shadow: 0 10px 25px -5px rgba(30, 64, 175, 0.3);
            position: relative;
            overflow: hidden;
        }

        .portal-header::after {
            content: '';
            position: absolute;
            right: -20px;
            top: -20px;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .portal-brand-wrap {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .portal-logo-img {
            width: 46px;
            height: 46px;
            object-fit: contain;
            background: #ffffff;
            border-radius: 10px;
            padding: 3px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.4);
            flex-shrink: 0;
        }

        .portal-title-block h1 {
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            margin-bottom: 0.1rem;
            line-height: 1.2;
        }

        .portal-title-block p {
            font-size: 0.8rem;
            color: #dbeafe;
            font-weight: 500;
        }

        .portal-meta-badges {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.45rem;
        }

        .badge-session {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            padding: 0.35rem 0.9rem;
            border-radius: 9999px;
            font-size: 0.82rem;
            font-weight: 700;
            border: 1px solid rgba(255, 255, 255, 0.28);
        }

        .badge-school {
            font-size: 0.76rem;
            color: #e0e7ff;
            background: rgba(15, 23, 42, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
        }

        /* ── Stepper Navigation Bar ── */
        .stepper-bar {
            background: #ffffff;
            border-left: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            padding: 0.45rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            overflow-x: auto;
            gap: 0.75rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-muted);
        }

        .stepper-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: var(--text-muted);
            transition: color 0.15s ease;
            white-space: nowrap;
        }

        .stepper-item:hover, .stepper-item.active {
            color: var(--primary);
        }

        .stepper-circle {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 800;
        }

        .stepper-item.active .stepper-circle {
            background: var(--primary);
            color: #ffffff;
        }

        .stepper-arrow {
            color: #cbd5e1;
            font-size: 0.75rem;
        }

        /* ── Form Body Container ── */
        .portal-card {
            background: var(--card-bg);
            border-radius: 0 0 var(--radius-xl) var(--radius-xl);
            border: 1px solid var(--border-color);
            border-top: none;
            box-shadow: var(--shadow-card);
            padding: 1.25rem 1.5rem;
        }

        /* ── Alert Error Box ── */
        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 0.6rem 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            font-size: 0.82rem;
        }

        .alert-error strong {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
        }

        .alert-error ul {
            margin-left: 1.5rem;
        }

        /* ── Section Dividers ── */
        .form-section {
            margin-bottom: 1.25rem;
            scroll-margin-top: 1rem;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 0.4rem;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 0.85rem;
        }

        .section-title {
            font-size: 0.92rem;
            font-weight: 800;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 0.45rem;
            letter-spacing: -0.01em;
        }

        .section-badge {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            background: #eff6ff;
            color: var(--primary);
            letter-spacing: 0.05em;
        }

        /* ── Grids & Inputs ── */
        .grid-layout {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.65rem;
        }

        .grid-3col {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.65rem;
        }

        .grid-2col {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.65rem;
        }

        @media (max-width: 820px) {
            .grid-3col, .grid-2col { grid-template-columns: 1fr !important; }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        label {
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #1e293b;
            display: flex;
            align-items: center;
        }

        .req {
            color: var(--error-color);
            margin-left: 3px;
            font-weight: 700;
        }

        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 0.42rem 0.65rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.84rem;
            transition: all 0.18s ease;
            background-color: #ffffff;
            color: var(--text-main);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        input:hover, select:hover, textarea:hover {
            border-color: #94a3b8;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px var(--focus-ring);
        }

        input.is-invalid, select.is-invalid, textarea.is-invalid {
            border-color: var(--error-color) !important;
            box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.15) !important;
        }

        .input-inline-btn {
            display: flex;
            gap: 0.5rem;
        }

        .btn-lookup {
            background-color: #0f172a;
            color: #ffffff;
            border: none;
            padding: 0.42rem 0.85rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.18s ease;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .btn-lookup:hover {
            background-color: #1e293b;
            transform: translateY(-1px);
        }

        /* ── Bio-Data Top Grid + Passport Photo Box (Right Side) ── */
        .biodata-hero-row {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .biodata-fields-col {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.65rem;
        }

        .passport-photo-card {
            width: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
        }

        .passport-photo-card label {
            width: 100%;
            justify-content: center;
            margin-bottom: 0.25rem;
        }

        .passport-photo-frame {
            width: 108px;
            height: 135px;
            border: 2px dashed #93c5fd;
            border-radius: 8px;
            background-color: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
            position: relative;
            text-align: center;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }

        .passport-photo-frame:hover {
            border-color: var(--primary);
            background-color: #eff6ff;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(37, 99, 235, 0.12);
        }

        .passport-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            color: var(--text-muted);
        }

        .passport-placeholder svg {
            color: var(--primary);
            margin-bottom: 0.4rem;
        }

        .passport-placeholder .photo-title {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .passport-placeholder .photo-note {
            font-size: 0.66rem;
            color: var(--text-muted);
            line-height: 1.25;
            margin-top: 4px;
        }

        .passport-photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-remove-link {
            margin-top: 0.45rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--error-color);
            background: none;
            border: none;
            cursor: pointer;
            text-decoration: underline;
        }

        /* ── Radio Pills for Medium ── */
        .medium-pill-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            height: 32px;
        }

        .medium-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            background: #f1f5f9;
            border: 1px solid var(--border-color);
            transition: all 0.15s ease;
        }

        .medium-pill:hover {
            background: #e2e8f0;
        }

        .medium-pill input[type="radio"] {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
            margin: 0;
        }

        /* ── Date of Birth & Words Container ── */
        .dob-age-badge {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary);
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            margin-top: 0.35rem;
            display: inline-block;
        }

        /* ── Footer Actions Toolbar ── */
        .form-footer-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 1rem;
            border-radius: var(--radius-md);
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-cancel:hover {
            background: #f8fafc;
            color: var(--text-main);
            border-color: #94a3b8;
        }

        .footer-action-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-submit-enrollment {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.75rem;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: #ffffff;
            font-size: 0.88rem;
            font-weight: 700;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(30, 64, 175, 0.35);
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .btn-submit-enrollment:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(30, 64, 175, 0.45);
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        }

        .btn-submit-enrollment:active {
            transform: translateY(0);
        }

        @media (max-width: 820px) {
            .biodata-hero-row {
                flex-direction: column-reverse;
                align-items: center;
            }
            .biodata-fields-col {
                width: 100%;
                grid-template-columns: 1fr;
            }
            .portal-card {
                padding: 1rem;
            }
            .portal-header {
                padding: 0.75rem 1rem;
            }
            .stepper-bar {
                padding: 0.35rem 0.75rem;
            }
            .form-footer-toolbar {
                flex-direction: column;
            }
            .form-footer-toolbar .btn-cancel,
            .footer-action-right,
            .btn-submit-enrollment {
                width: 100%;
                justify-content: center;
            }
        }
</style>
@endpush

@push('topbar_back')
<a href="{{ route('school.students.index') }}" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.35rem 0.85rem;border-radius:8px;border:1px solid #cbd5e1;background:#fff;color:#475569;font-size:0.8rem;font-weight:600;text-decoration:none;transition:all 0.15s ease;white-space:nowrap;" onmouseover="this.style.background='#f1f5f9';this.style.color='#0f172a'" onmouseout="this.style.background='#fff';this.style.color='#475569'">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Back to List
</a>
@endpush

@push('topbar_actions')
<a href="{{ route('school.students.create') }}" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1.1rem;background:linear-gradient(135deg,#1e40af,#1e3a8a);color:#fff;font-size:0.82rem;font-weight:700;border-radius:9999px;text-decoration:none;box-shadow:0 4px 12px rgba(30,64,175,0.3);">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    New Enrollment
</a>
@endpush

@section('content')
<div class="portal-wrapper">
    <!-- Top Header -->
    <header class="portal-header">
        <div class="portal-brand-wrap">
            <img src="/images/bise-sukkur-logo.png" alt="BISE Sukkur" class="portal-logo-img" onerror="this.style.display='none'">
            <div class="portal-title-block">
                <h1>STUDENT ENROLLMENT FORM</h1>
                <p>Board of Intermediate &amp; Secondary Education, Sukkur &bull; Sindh</p>
            </div>
        </div>

        <div class="portal-meta-badges">
            <span class="badge-session">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Session: {{ $activeYear->label ?? '2025-26' }}
            </span>
            <span class="badge-school">{{ auth()->user()->school->name ?? 'Government / Private Institution' }}</span>
        </div>
    </header>

    <!-- Stepper Indicator -->
    <nav class="stepper-bar" aria-label="Enrollment Steps">
        <a href="#sec-academic" class="stepper-item active">
            <span class="stepper-circle">1</span>
            <span>Academic Details</span>
        </a>
        <span class="stepper-arrow">&rarr;</span>
        <a href="#sec-other-board" class="stepper-item">
            <span class="stepper-circle">2</span>
            <span>Transfer / Other Board</span>
        </a>
        <span class="stepper-arrow">&rarr;</span>
        <a href="#sec-biodata" class="stepper-item">
            <span class="stepper-circle">3</span>
            <span>Bio Data &amp; Photo</span>
        </a>
        <span class="stepper-arrow">&rarr;</span>
        <a href="#sec-contact" class="stepper-item">
            <span class="stepper-circle">4</span>
            <span>Contact &amp; Address</span>
        </a>
    </nav>

    <!-- Main Card Body -->
    <div class="portal-card">
        <!-- Error Alerts -->
        @if ($errors->any())
            <div class="alert-error" role="alert">
                <strong>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Please fix the following validation errors before submitting:
                </strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="enrollmentForm" action="{{ route('school.students.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="save_as" id="saveAsInput" value="final">

            <!-- ── STEP 1: ACADEMIC INFORMATION ── -->
            <section class="form-section" id="sec-academic">
                <div class="section-header">
                    <h2 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        Academic Information
                    </h2>
                    <span class="section-badge">Step 1 of 4</span>
                </div>

                <div class="grid-3col">
                    <div class="form-group">
                        <label for="academic_session">Academic Session <span class="req">*</span></label>
                        <select id="academic_session" name="academic_session" required>
                            <option value="2025-26" {{ old('academic_session', $activeYear->label ?? '2025-26') == '2025-26' ? 'selected' : '' }}>2025-26</option>
                            <option value="2026-27" {{ old('academic_session') == '2026-27' ? 'selected' : '' }}>2026-27</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="class_level">Enrollment for <span class="req">*</span></label>
                        <select id="class_level" name="class_level" class="@error('class_level') is-invalid @enderror" required onchange="handleClassLevelChange()">
                            <option value="">Select Class...</option>
                            <optgroup label="Matric / SSC">
                                <option value="ssc_part1" {{ old('class_level') == 'ssc_part1' ? 'selected' : '' }}>SSC Part-I (Class 9th)</option>
                                <option value="ssc_part2" {{ old('class_level') == 'ssc_part2' ? 'selected' : '' }}>SSC Part-II (Class 10th)</option>
                            </optgroup>
                            <optgroup label="Intermediate / HSC">
                                <option value="hsc_part1" {{ old('class_level') == 'hsc_part1' ? 'selected' : '' }}>HSC Part-I (Class 11th)</option>
                                <option value="hsc_part2" {{ old('class_level') == 'hsc_part2' ? 'selected' : '' }}>HSC Part-II (Class 12th)</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject_group">Group <span class="req">*</span></label>
                        <select id="subject_group" name="subject_group" class="@error('subject_group') is-invalid @enderror" required>
                            <option value="">Select Class first...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="student_type">Student Type <span class="req">*</span></label>
                        <select id="student_type" name="student_type" required>
                            <option value="fresh" {{ old('student_type', 'fresh') == 'fresh' ? 'selected' : '' }}>Fresh</option>
                            <option value="repeater" {{ old('student_type') == 'repeater' ? 'selected' : '' }}>Repeater</option>
                            <option value="private" {{ old('student_type') == 'private' ? 'selected' : '' }}>Private</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="gr_number">G.R / Roll No <span class="req">*</span></label>
                        <input type="text" id="gr_number" name="gr_number" value="{{ old('gr_number') }}" placeholder="Enter School G.R Number" class="@error('gr_number') is-invalid @enderror" required>
                    </div>

                    <div class="form-group">
                        <label for="admission_date">Date of Admission <span class="req">*</span></label>
                        <input type="date" id="admission_date" name="admission_date" value="{{ old('admission_date', date('Y-m-d')) }}" class="@error('admission_date') is-invalid @enderror" required>
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="enrollment_number">Enrollment Registration Number</label>
                        <div class="input-inline-btn">
                            <input type="text" id="enrollment_number" name="enrollment_number" value="{{ old('enrollment_number') }}" placeholder="e.g. 2024-SUK-12345">
                            <button type="button" class="btn-lookup" id="btnGetEnrollment" onclick="lookupStudentData()">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <span>Get Data</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="eligibility_no">Eligibility No.</label>
                        <input type="text" id="eligibility_no" name="eligibility_no" value="{{ old('eligibility_no') }}" placeholder="e.g. ELIG-2025-XXXX">
                    </div>
                </div>
            </section>

            <!-- ── STEP 2: OTHER BOARD / TRANSFER INFORMATION ── -->
            <section class="form-section" id="sec-other-board">
                <div class="section-header">
                    <h2 class="section-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                        Other Board / Transfer Information
                    </h2>
                    <span class="section-badge">Step 2 of 4</span>
                </div>

                <div class="grid-3col">
                    <div class="form-group">
                        <label for="other_board">Board</label>
                        <select id="other_board" name="other_board" onchange="toggleOtherBoard(this)">
                            <option value="">Select Board (If transferring)...</option>
                            <option value="bise_hyderabad">BISE Hyderabad</option>
                            <option value="bise_larkana">BISE Larkana</option>
                            <option value="bise_mirpurkhas">BISE Mirpurkhas</option>
                            <option value="bise_karachi">BIEK / BSEK Karachi</option>
                            <option value="bise_shaheed_benazirabad">BISE Shaheed Benazirabad</option>
                            <option value="federal_board">Federal Board (FBISE)</option>
                            <option value="other">Other Board</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="other_board_name">Name of Other Board</label>
                        <input type="text" id="other_board_name" name="other_board_name" value="{{ old('other_board_name') }}" placeholder="Enter board title">
                    </div>

                    <div class="form-group">
                        <label for="other_board_date">Date of Passing / Migration</label>
                        <input type="date" id="other_board_date" name="other_board_date" value="{{ old('other_board_date') }}">
                    </div>

                    <div class="form-group">
                        <label for="previous_seat_no">Previous Seat No</label>
                        <input type="text" id="previous_seat_no" name="previous_seat_no" value="{{ old('previous_seat_no') }}" placeholder="e.g. 142058">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="previous_year">Passing Year</label>
                        <div class="input-inline-btn">
                            <select id="previous_year" name="previous_year">
                                <option value="">Select Year...</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                                <option value="2022">2022</option>
                                <option value="2021">2021</option>
                            </select>
                            <button type="button" class="btn-lookup" onclick="lookupPreviousBoard()">Get Data</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── STEP 3: CANDIDATE BIO-DATA & PASSPORT PHOTO ── -->
            <section class="form-section" id="sec-biodata" style="margin-top:0;">
                <div class="section-header">
                    <h2 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Bio Data &amp; Candidate Photo
                    </h2>
                    <span class="section-badge">Step 3 of 4</span>

                </div>

                <!-- Bio Data Top: Names & CNICs with Passport Photo on Right -->
                <div class="biodata-hero-row">
                    <div class="biodata-fields-col">
                        <!-- Candidate Name (Letters Only, Auto Uppercase) -->
                        <div class="form-group">
                            <label for="full_name">Name of Candidate <span class="req">*</span></label>
                            <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}"
                                   placeholder="CAPITAL LETTERS"
                                   style="text-transform: uppercase;"
                                   data-type="text-only"
                                   class="@error('full_name') is-invalid @enderror"
                                   required>
                        </div>

                        <!-- CNIC / B-Form (Numbers Only, Auto-Hyphenated) -->
                        <div class="form-group">
                            <label for="cnic">CNIC or B-Form <span class="req">*</span></label>
                            <input type="text" id="cnic" name="cnic" value="{{ old('cnic') }}"
                                   placeholder="xxxxx-xxxxxxx-x"
                                   maxlength="15"
                                   data-type="cnic"
                                   class="@error('cnic') is-invalid @enderror"
                                   required>
                        </div>

                        <!-- Father's Name (Letters Only, Auto Uppercase) -->
                        <div class="form-group">
                            <label for="father_name">Father's Name <span class="req">*</span></label>
                            <input type="text" id="father_name" name="father_name" value="{{ old('father_name') }}"
                                   placeholder="CAPITAL LETTERS"
                                   style="text-transform: uppercase;"
                                   data-type="text-only"
                                   class="@error('father_name') is-invalid @enderror"
                                   required>
                        </div>

                        <!-- Father's CNIC (Numbers Only, Auto-Hyphenated) -->
                        <div class="form-group">
                            <label for="father_cnic">Father's CNIC</label>
                            <input type="text" id="father_cnic" name="father_cnic" value="{{ old('father_cnic') }}"
                                   placeholder="xxxxx-xxxxxxx-x"
                                   maxlength="15"
                                   data-type="cnic"
                                   class="@error('father_cnic') is-invalid @enderror">
                        </div>
                    </div>

                    <!-- Passport Photo Small Box (Right Side) -->
                    <div class="passport-photo-card">
                        <label>Candidate Photo <span class="req">*</span></label>
                        <div class="passport-photo-frame" id="passportPhotoFrame" onclick="document.getElementById('photo').click()" title="Click to upload passport size photo">
                            <div id="photoPlaceholder" class="passport-placeholder">
                                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                    <circle cx="12" cy="13" r="4"/>
                                </svg>
                                <span class="photo-title">Passport Photo</span>
                                <span class="photo-note">Blue background<br>Max 500 KB</span>
                            </div>
                            <img id="photoPreviewImg" class="passport-photo-img" src="" alt="Candidate Photo Preview" style="display:none;">
                            <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="handlePhotoSelect(this)">
                        </div>
                        <button type="button" class="photo-remove-link" id="photoRemoveBtn" style="display:none;" onclick="clearPhoto(event)">Remove / Change</button>
                    </div>
                </div>

                <!-- Lower Bio-Data Grid -->
                <div class="grid-3col" style="margin-top: 0.65rem;">
                    <!-- Guardian Name (Letters Only) -->
                    <div class="form-group">
                        <label for="guardian_name">Guardian Name</label>
                        <input type="text" id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}"
                               placeholder="CAPITAL LETTERS"
                               style="text-transform: uppercase;"
                               data-type="text-only">
                    </div>

                    <!-- Guardian CNIC (Numbers Only) -->
                    <div class="form-group">
                        <label for="guardian_cnic">Guardian CNIC</label>
                        <input type="text" id="guardian_cnic" name="guardian_cnic" value="{{ old('guardian_cnic') }}"
                               placeholder="xxxxx-xxxxxxx-x"
                               maxlength="15"
                               data-type="cnic">
                    </div>

                    <!-- Surname / Cast (Letters Only) -->
                    <div class="form-group">
                        <label for="surname">Surname / Cast <span class="req">*</span></label>
                        <input type="text" id="surname" name="surname" value="{{ old('surname') }}"
                               placeholder="CAPITAL LETTERS"
                               style="text-transform: uppercase;"
                               data-type="text-only"
                               class="@error('surname') is-invalid @enderror"
                               required>
                    </div>

                    <!-- Gender -->
                    <div class="form-group">
                        <label for="gender">Gender <span class="req">*</span></label>
                        <select id="gender" name="gender" class="@error('gender') is-invalid @enderror" required>
                            <option value="">Select Gender...</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <!-- Date Of Birth -->
                    <div class="form-group">
                        <label for="date_of_birth">Date Of Birth <span class="req">*</span></label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                               onchange="handleDateOfBirthChange(this.value)"
                               class="@error('date_of_birth') is-invalid @enderror"
                               required>
                        <div id="dob_age_badge" class="dob-age-badge" style="display:none;"></div>
                    </div>

                    <!-- Date Of Birth (In Words) - Automatically Generated -->
                    <div class="form-group">
                        <label for="dob_in_words">Date Of Birth (In Words) <span class="req">*</span></label>
                        <input type="text" id="dob_in_words" name="dob_in_words" value="{{ old('dob_in_words') }}"
                               placeholder="e.g. First January Two Thousand"
                               class="@error('dob_in_words') is-invalid @enderror"
                               required>
                    </div>

                    <!-- Marks of Identification -->
                    <div class="form-group">
                        <label for="marks_of_identification">Marks of Identification <span class="req">*</span></label>
                        <input type="text" id="marks_of_identification" name="marks_of_identification" value="{{ old('marks_of_identification', 'NIL') }}" class="@error('marks_of_identification') is-invalid @enderror" required>
                    </div>

                    <!-- Religion -->
                    <div class="form-group">
                        <label for="religion">Religion <span class="req">*</span></label>
                        <select id="religion" name="religion" required>
                            <option value="Islam" {{ old('religion', 'Islam') == 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Christianity" {{ old('religion') == 'Christianity' ? 'selected' : '' }}>Christianity</option>
                            <option value="Hinduism" {{ old('religion') == 'Hinduism' ? 'selected' : '' }}>Hinduism</option>
                            <option value="Other" {{ old('religion') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <!-- Medium of Instruction (Pill radios) -->
                    <div class="form-group" style="grid-column: span 1;">
                        <label>Medium of Instruction <span class="req">*</span></label>
                        <div class="medium-pill-group">
                            <label class="medium-pill">
                                <input type="radio" name="medium_of_instruction" value="Urdu" {{ old('medium_of_instruction', 'Urdu') == 'Urdu' ? 'checked' : '' }}>
                                <span>Urdu</span>
                            </label>
                            <label class="medium-pill">
                                <input type="radio" name="medium_of_instruction" value="Sindhi" {{ old('medium_of_instruction') == 'Sindhi' ? 'checked' : '' }}>
                                <span>Sindhi</span>
                            </label>
                            <label class="medium-pill">
                                <input type="radio" name="medium_of_instruction" value="English" {{ old('medium_of_instruction') == 'English' ? 'checked' : '' }}>
                                <span>English</span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>
            <!-- ── STEP 4: CONTACT & RESIDENTIAL DETAILS ── -->
            <section class="form-section" id="sec-contact">
                <div class="section-header">
                    <h2 class="section-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Contact &amp; Residential Information
                    </h2>
                    <span class="section-badge">Step 4 of 4</span>
                </div>

                <div class="grid-2col">
                    <!-- Mobile Number (Numbers Only, 03xx-xxxxxxx) -->
                    <div class="form-group">
                        <label for="phone">Mobile Number <span class="req">*</span></label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                               placeholder="03xx-xxxxxxx"
                               maxlength="12"
                               data-type="mobile"
                               class="@error('phone') is-invalid @enderror"
                               required>
                    </div>

                    <!-- Postal Code -->
                    <div class="form-group">
                        <label for="postal_code">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" placeholder="e.g. 65200">
                    </div>

                    <!-- Residential Address -->
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="address">Student Residential Address <span class="req">*</span></label>
                        <textarea id="address" name="address" rows="1" placeholder="Enter complete home / residential address" class="@error('address') is-invalid @enderror" required>{{ old('address') }}</textarea>
                    </div>

                    <!-- Remarks -->
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="remarks">Special Remarks / Notes</label>
                        <textarea id="remarks" name="remarks" rows="1" placeholder="Any disability, medical considerations, or administrative remarks">{{ old('remarks') }}</textarea>
                    </div>
                </div>
            </section>

            <!-- ── FOOTER ACTIONS TOOLBAR ── -->
            <footer class="form-footer-toolbar">
                <a href="{{ route('school.students.index') }}" class="btn-cancel">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Back to Candidate List</span>
                </a>

                <div class="footer-action-right">
                    <button type="submit" class="btn-submit-enrollment" id="btnSubmitForm">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Submit &amp; Enroll Candidate</span>
                    </button>
                </div>
            </footer>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // ─────────────────────────────────────────────────────────────
    // 1. Dynamic Groups by Class Level
    // ─────────────────────────────────────────────────────────────
    const groupsByLevel = {
        'ssc_part1': [
            { value: 'science', label: 'Science (Biology)' },
            { value: 'science', label: 'Science (Computer Science)' },
            { value: 'general', label: 'General / Arts Group' }
        ],
        'ssc_part2': [
            { value: 'science', label: 'Science (Biology)' },
            { value: 'science', label: 'Science (Computer Science)' },
            { value: 'general', label: 'General / Arts Group' }
        ],
        'hsc_part1': [
            { value: 'pre_medical', label: 'Pre-Medical' },
            { value: 'pre_engineering', label: 'Pre-Engineering' },
            { value: 'science', label: 'General Science / Computer Science' },
            { value: 'commerce', label: 'Commerce' },
            { value: 'arts', label: 'Humanities / Arts' }
        ],
        'hsc_part2': [
            { value: 'pre_medical', label: 'Pre-Medical' },
            { value: 'pre_engineering', label: 'Pre-Engineering' },
            { value: 'science', label: 'General Science / Computer Science' },
            { value: 'commerce', label: 'Commerce' },
            { value: 'arts', label: 'Humanities / Arts' }
        ]
    };

    function handleClassLevelChange() {
        const level = document.getElementById('class_level').value;
        const groupSelect = document.getElementById('subject_group');
        const selectedValue = "{{ old('subject_group') }}";
        groupSelect.innerHTML = '<option value="">Select Group...</option>';

        if (level && groupsByLevel[level]) {
            groupsByLevel[level].forEach(grp => {
                const opt = document.createElement('option');
                opt.value = grp.value;
                opt.textContent = grp.label;
                if (grp.value === selectedValue) {
                    opt.selected = true;
                }
                groupSelect.appendChild(opt);
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const currentLevel = document.getElementById('class_level').value;
        if (currentLevel) {
            handleClassLevelChange();
        }
    });

    // ─────────────────────────────────────────────────────────────
    // 2. Silent & Strict Input Validations
    // ─────────────────────────────────────────────────────────────

    // (A) Text-Only Inputs: Only Letters & Spaces (Digits/Symbols strictly blocked)
    document.querySelectorAll('[data-type="text-only"]').forEach(input => {
        input.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which || e.keyCode);
            if (!/^[a-zA-Z\s]$/.test(char) && e.keyCode !== 8 && e.keyCode !== 9 && e.keyCode !== 46) {
                e.preventDefault();
            }
        });

        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Z\s]/g, '').toUpperCase();
        });

        input.addEventListener('blur', function() {
            this.value = this.value.trim().replace(/\s+/g, ' ');
        });
    });

    // (B) CNIC Masking: xxxxx-xxxxxxx-x (Numbers only)
    document.querySelectorAll('[data-type="cnic"]').forEach(input => {
        input.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which || e.keyCode);
            if (!/^\d$/.test(char) && e.keyCode !== 8 && e.keyCode !== 9) {
                e.preventDefault();
            }
        });

        input.addEventListener('input', function() {
            let digits = this.value.replace(/\D/g, '');
            if (digits.length > 13) {
                digits = digits.substring(0, 13);
            }

            let masked = '';
            if (digits.length <= 5) {
                masked = digits;
            } else if (digits.length <= 12) {
                masked = digits.substring(0, 5) + '-' + digits.substring(5);
            } else {
                masked = digits.substring(0, 5) + '-' + digits.substring(5, 12) + '-' + digits.substring(12, 13);
            }
            this.value = masked;

            if (digits.length === 13) {
                this.classList.remove('is-invalid');
                this.style.borderColor = '#16a34a';
            } else {
                this.style.borderColor = '';
            }
        });
    });

    // (C) Mobile Number Masking: 03xx-xxxxxxx (Numbers only)
    document.querySelectorAll('[data-type="mobile"]').forEach(input => {
        input.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which || e.keyCode);
            if (!/^\d$/.test(char) && e.keyCode !== 8 && e.keyCode !== 9) {
                e.preventDefault();
            }
        });

        input.addEventListener('input', function() {
            let digits = this.value.replace(/\D/g, '');
            if (digits.length > 11) {
                digits = digits.substring(0, 11);
            }

            let masked = '';
            if (digits.length <= 4) {
                masked = digits;
            } else {
                masked = digits.substring(0, 4) + '-' + digits.substring(4);
            }
            this.value = masked;

            if (digits.length === 11 && digits.startsWith('03')) {
                this.classList.remove('is-invalid');
                this.style.borderColor = '#16a34a';
            } else {
                this.style.borderColor = '';
            }
        });
    });

    // ─────────────────────────────────────────────────────────────
    // 3. Date of Birth -> Words & Age Calculator
    // ─────────────────────────────────────────────────────────────
    const ones = ['', 'First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth', 'Seventh', 'Eighth', 'Ninth', 'Tenth',
                  'Eleventh', 'Twelfth', 'Thirteenth', 'Fourteenth', 'Fifteenth', 'Sixteenth', 'Seventeenth',
                  'Eighteenth', 'Nineteenth', 'Twentieth', 'Twenty-First', 'Twenty-Second', 'Twenty-Third',
                  'Twenty-Fourth', 'Twenty-Fifth', 'Twenty-Sixth', 'Twenty-Seventh', 'Twenty-Eighth', 'Twenty-Ninth',
                  'Thirtieth', 'Thirty-First'];

    const monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July',
                        'August', 'September', 'October', 'November', 'December'];

    const numWordsBelow20 = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    const tensWords = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    function numberToWords(n) {
        if (n === 0) return 'Zero';
        if (n < 20) return numWordsBelow20[n];
        if (n < 100) {
            const ten = Math.floor(n / 10);
            const rem = n % 10;
            return tensWords[ten] + (rem > 0 ? ' ' + numWordsBelow20[rem] : '');
        }
        if (n < 1000) {
            const hundred = Math.floor(n / 100);
            const rem = n % 100;
            return numWordsBelow20[hundred] + ' Hundred' + (rem > 0 ? ' ' + numberToWords(rem) : '');
        }
        if (n < 1000000) {
            const thousand = Math.floor(n / 1000);
            const rem = n % 1000;
            return numberToWords(thousand) + ' Thousand' + (rem > 0 ? ' ' + numberToWords(rem) : '');
        }
        return n.toString();
    }

    function handleDateOfBirthChange(dateString) {
        if (!dateString) return;
        const parts = dateString.split('-');
        if (parts.length !== 3) return;

        const year = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10);
        const day = parseInt(parts[2], 10);

        if (day >= 1 && day <= 31 && month >= 1 && month <= 12 && year > 1900) {
            const dayWord = ones[day] || day.toString();
            const monthWord = monthNames[month] || '';
            const yearWord = numberToWords(year);

            const inWords = `${dayWord} ${monthWord} ${yearWord}`;
            document.getElementById('dob_in_words').value = inWords;

            // Calculate exact age
            const birthDate = new Date(year, month - 1, day);
            const today = new Date();
            let ageYears = today.getFullYear() - birthDate.getFullYear();
            let ageMonths = today.getMonth() - birthDate.getMonth();
            if (today.getDate() < birthDate.getDate()) {
                ageMonths--;
            }
            if (ageMonths < 0) {
                ageYears--;
                ageMonths += 12;
            }

            const ageBadge = document.getElementById('dob_age_badge');
            if (ageBadge) {
                ageBadge.innerHTML = `Calculated Age: <strong>${ageYears} Years, ${ageMonths} Months</strong>`;
                ageBadge.style.display = 'inline-block';
            }
        }
    }

    // Trigger calculation if date_of_birth already has an old value
    window.addEventListener('load', function() {
        const existingDob = document.getElementById('date_of_birth').value;
        if (existingDob) {
            handleDateOfBirthChange(existingDob);
        }
    });

    // ─────────────────────────────────────────────────────────────
    // 4. Passport Photo Upload & Live Preview
    // ─────────────────────────────────────────────────────────────
    function handlePhotoSelect(input) {
        const file = input.files && input.files[0];
        if (!file) return;

        // Check file size (max 500 KB)
        if (file.size > 500 * 1024) {
            alert('The selected photo is larger than 500KB. Please choose a passport size photo under 500KB.');
            input.value = '';
            clearPhoto();
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('photoPreviewImg');
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
            document.getElementById('photoPlaceholder').style.display = 'none';
            document.getElementById('photoRemoveBtn').style.display = 'inline-block';
        };
        reader.readAsDataURL(file);
    }

    function clearPhoto(e) {
        if (e) e.stopPropagation();
        const input = document.getElementById('photo');
        input.value = '';
        const previewImg = document.getElementById('photoPreviewImg');
        previewImg.src = '';
        previewImg.style.display = 'none';
        document.getElementById('photoPlaceholder').style.display = 'flex';
        document.getElementById('photoRemoveBtn').style.display = 'none';
    }

    // ─────────────────────────────────────────────────────────────
    // 5. Real AJAX "Get Data" Student Lookup
    // ─────────────────────────────────────────────────────────────
    function lookupStudentData() {
        const enrNo = document.getElementById('enrollment_number').value.trim();
        const cnic = document.getElementById('cnic').value.trim();

        if (!enrNo && !cnic) {
            alert('Please enter an Enrollment Number or CNIC to lookup existing candidate records.');
            return;
        }

        const btn = document.getElementById('btnGetEnrollment');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span>Searching...</span>';
        btn.disabled = true;

        const params = new URLSearchParams();
        if (enrNo) params.append('enrollment_number', enrNo);
        if (cnic) params.append('cnic', cnic);

        fetch("{{ route('school.students.lookup') }}?" + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(res => {
            btn.innerHTML = originalText;
            btn.disabled = false;

            if (res.success && res.data) {
                const d = res.data;
                if (d.full_name) document.getElementById('full_name').value = d.full_name;
                if (d.father_name) document.getElementById('father_name').value = d.father_name;
                if (d.father_cnic) document.getElementById('father_cnic').value = d.father_cnic;
                if (d.cnic) document.getElementById('cnic').value = d.cnic;
                if (d.surname) document.getElementById('surname').value = d.surname;
                if (d.gender) document.getElementById('gender').value = d.gender;
                if (d.date_of_birth) {
                    document.getElementById('date_of_birth').value = d.date_of_birth;
                    handleDateOfBirthChange(d.date_of_birth);
                }
                if (d.religion) document.getElementById('religion').value = d.religion;
                if (d.phone) document.getElementById('phone').value = d.phone;
                if (d.address) document.getElementById('address').value = d.address;
                if (d.postal_code) document.getElementById('postal_code').value = d.postal_code;
                if (d.guardian_name) document.getElementById('guardian_name').value = d.guardian_name;
                if (d.guardian_cnic) document.getElementById('guardian_cnic').value = d.guardian_cnic;
                if (d.marks_of_identification) document.getElementById('marks_of_identification').value = d.marks_of_identification;

                alert('Candidate records successfully retrieved and loaded into the form!');
            } else {
                alert(res.message || 'No matching record found in BISE database.');
            }
        })
        .catch(err => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Lookup failed or server is unreachable. Please verify network connection.');
        });
    }

    function lookupPreviousBoard() {
        const seatNo = document.getElementById('previous_seat_no').value.trim();
        const year = document.getElementById('previous_year').value;

        if (!seatNo || !year) {
            alert('Please select both Passing Year and Previous Seat Number.');
            return;
        }

        alert('Querying BISE archives for Seat No: ' + seatNo + ' (' + year + ')... No online record found. Please enter details manually.');
    }

    function toggleOtherBoard(select) {
        const nameInput = document.getElementById('other_board_name');
        if (select.value === 'other') {
            nameInput.required = true;
            nameInput.focus();
        } else {
            nameInput.required = false;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 6. Pre-Submission Form Validation
    // ─────────────────────────────────────────────────────────────
    document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
        const cnic = document.getElementById('cnic').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const fullName = document.getElementById('full_name').value.trim();
        const fatherName = document.getElementById('father_name').value.trim();

        // Validate CNIC length
        if (cnic.replace(/\D/g, '').length !== 13) {
            e.preventDefault();
            alert('Please provide a complete 13-digit CNIC or B-Form number (xxxxx-xxxxxxx-x).');
            document.getElementById('cnic').focus();
            return false;
        }

        // Validate Phone length
        if (phone.replace(/\D/g, '').length !== 11) {
            e.preventDefault();
            alert('Please provide a complete 11-digit mobile number starting with 03 (03xx-xxxxxxx).');
            document.getElementById('phone').focus();
            return false;
        }

        // Validate Candidate Name
        if (/\d/.test(fullName)) {
            e.preventDefault();
            alert('Candidate Name cannot contain numbers. Only letters are allowed.');
            document.getElementById('full_name').focus();
            return false;
        }

        // Validate Father Name
        if (/\d/.test(fatherName)) {
            e.preventDefault();
            alert("Father's Name cannot contain numbers. Only letters are allowed.");
            document.getElementById('father_name').focus();
            return false;
        }

        // Show spinner on submit button
        const submitBtn = document.getElementById('btnSubmitForm');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>Enrolling Candidate...</span>';
    });
</script>
@endpush

@endsection
