@extends('layouts.school')

@section('title', 'Candidate Examination Form')

@section('content')
<style>
    .efc-wrap {
        max-width: 860px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .efc-card {
        background: #ffffff;
        border-radius: var(--radius-lg, 12px);
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 1.75rem;
    }
    .efc-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .efc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
    }
    .efc-field {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .efc-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .efc-val {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
    }
    .efc-input, .efc-select {
        padding: 0.65rem 0.85rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.88rem;
        color: #0f172a;
        background: #fff;
        outline: none;
        width: 100%;
    }
    .efc-input:focus, .efc-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .efc-btn-bar {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1.5rem;
        padding-top: 1.25rem;
        border-top: 1px solid #f1f5f9;
    }
</style>

<div class="efc-wrap">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0;">Annual Board Examination Form</h1>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Official candidacy registration for secondary & higher secondary examinations.</p>
        </div>
        <div>
            <a href="{{ route('school.examination.forms') }}" style="padding: 0.45rem 0.9rem; font-size: 0.85rem; font-weight: 600; color: #475569; text-decoration: none; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;">
                &larr; Back to Forms
            </a>
        </div>
    </div>

    <!-- Student Bio Details Card -->
    <div class="efc-card">
        <div class="efc-title-row">
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Candidate Information</h3>
                <span style="font-size: 0.8rem; color: #64748b;">Verified enrollment credentials from board records</span>
            </div>
            <div>
                <span style="font-size: 0.8rem; font-weight: 700; color: #1e40af; background: #eff6ff; padding: 4px 10px; border-radius: 9999px;">
                    ENROLLMENT # {{ $student->enrollment_number ?? 'PENDING' }}
                </span>
            </div>
        </div>

        <div class="efc-grid">
            <div class="efc-field">
                <span class="efc-label">Candidate Name</span>
                <span class="efc-val">{{ $student->full_name }}</span>
            </div>
            <div class="efc-field">
                <span class="efc-label">Father's Name</span>
                <span class="efc-val">{{ $student->father_name }}</span>
            </div>
            <div class="efc-field">
                <span class="efc-label">CNIC / B-Form</span>
                <span class="efc-val">{{ $student->cnic ?? $student->b_form ?? '—' }}</span>
            </div>
            <div class="efc-field">
                <span class="efc-label">Date of Birth</span>
                <span class="efc-val">{{ $student->date_of_birth ? date('d-M-Y', strtotime($student->date_of_birth)) : '—' }}</span>
            </div>
            <div class="efc-field">
                <span class="efc-label">Examination Class</span>
                <span class="efc-val">{{ ucwords(str_replace('_', ' ', $student->currentAcademicRecord->class_level ?? ($student->academic_record->class_level ?? '—'))) }}</span>
            </div>
            <div class="efc-field">
                <span class="efc-label">Subject Group</span>
                <span class="efc-val">{{ $student->currentAcademicRecord->subject_group ?? ($student->academic_record->subject_group ?? 'General') }}</span>
            </div>
        </div>
    </div>

    <!-- Examination Entry Form -->
    <div class="efc-card">
        <form method="POST" action="{{ isset($examForm) ? route('school.examination.form.update', $examForm->id) : route('school.examination.form.store') }}">
            @csrf
            @if(isset($examForm))
                @method('PUT')
            @endif

            <input type="hidden" name="student_id" value="{{ $student->id }}">
            <input type="hidden" name="student_academic_record_id" value="{{ $student->currentAcademicRecord->id ?? ($student->academic_record->id ?? '') }}">

            <div class="efc-title-row">
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Examination Preferences</h3>
                    <span style="font-size: 0.8rem; color: #64748b;">Specify center allocation preference and submission status</span>
                </div>
            </div>

            <div class="efc-grid">
                <div class="efc-field" style="grid-column: 1 / -1;">
                    <label class="efc-label">Examination Center Preference</label>
                    <select name="exam_center_id" class="efc-select">
                        <option value="">-- Board Allocated Default Center --</option>
                        @php
                            $centers = \App\Models\ExamCenter::orderBy('name')->get();
                        @endphp
                        @foreach($centers as $c)
                            <option value="{{ $c->id }}" {{ (old('exam_center_id', $examForm->exam_center_id ?? '') == $c->id) ? 'selected' : '' }}>
                                {{ $c->name }} (Code: {{ $c->code ?? $c->id }}) — Capacity: {{ $c->capacity ?? 'Standard' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="efc-btn-bar">
                <button type="submit" name="save_as" value="draft" style="padding: 0.6rem 1.25rem; font-size: 0.85rem; font-weight: 600; color: #475569; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer;">
                    Save as Draft
                </button>
                <button type="submit" name="save_as" value="final" style="padding: 0.6rem 1.5rem; font-size: 0.85rem; font-weight: 700; color: #ffffff; background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%); border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 4px 10px rgba(30, 64, 175, 0.25);">
                    Submit Final Examination Form &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
