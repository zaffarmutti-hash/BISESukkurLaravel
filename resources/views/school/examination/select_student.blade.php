@extends('layouts.school')

@section('title', 'Select Student for Examination Form')

@section('content')
<style>
    .sel-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .sel-card {
        background: #ffffff;
        border-radius: var(--radius-lg, 12px);
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    .sel-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
        text-align: left;
    }
    .sel-table th {
        background: #f8fafc;
        padding: 0.85rem 1rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e2e8f0;
    }
    .sel-table td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
    }
    .sel-table tr:hover td {
        background: #f8fafc;
    }
    .btn-select-student {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.85rem;
        background: #1e40af;
        color: #ffffff;
        font-size: 0.8rem;
        font-weight: 700;
        border-radius: 6px;
        text-decoration: none;
        transition: background 0.15s ease;
    }
    .btn-select-student:hover {
        background: #1e3a8a;
        color: #ffffff;
    }
</style>

<div class="sel-container">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0;">Select Candidate for Exam Form</h1>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">
                Select an enrolled candidate to proceed with their annual board examination form.
            </p>
        </div>
        <div>
            <a href="{{ route('school.examination.forms') }}" class="action-btn" style="padding: 0.5rem 1rem; font-weight: 600; text-decoration: none; border: 1px solid #cbd5e1; border-radius: 8px; color: #334155; background: #fff;">
                &larr; Back to Exam Forms
            </a>
        </div>
    </div>

    <div class="sel-card">
        <table class="sel-table">
            <thead>
                <tr>
                    <th>Candidate Name</th>
                    <th>Father's Name</th>
                    <th>Enrollment No</th>
                    <th>Class Level</th>
                    <th>Group</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $st)
                <tr>
                    <td>
                        <div style="font-weight: 700; color: #0f172a;">{{ $st->full_name }}</div>
                        <div style="font-size: 0.75rem; color: #64748b;">CNIC: {{ $st->cnic ?? $st->b_form ?? '—' }}</div>
                    </td>
                    <td>{{ $st->father_name }}</td>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #1e40af;">
                            {{ $st->enrollment_number ?? 'Pending' }}
                        </span>
                    </td>
                    <td>
                        <span style="font-weight: 600; text-transform: capitalize;">
                            {{ ucwords(str_replace('_', ' ', $st->currentAcademicRecord->class_level ?? ($st->academic_record->class_level ?? '—'))) }}
                        </span>
                    </td>
                    <td>
                        {{ $st->currentAcademicRecord->subject_group ?? ($st->academic_record->subject_group ?? 'General') }}
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('school.examination.form.create', ['student_id' => $st->id]) }}" class="btn-select-student">
                            + Fill Form
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                        <div style="font-weight: 600; font-size: 1rem; color: #475569;">No eligible candidates found</div>
                        <p style="font-size: 0.85rem; margin-top: 0.25rem;">Candidates must be officially enrolled to register for board examinations.</p>
                        <a href="{{ route('school.students.create') }}" style="display: inline-block; margin-top: 0.75rem; color: #1e40af; font-weight: 700; text-decoration: underline;">
                            Create New Candidate Enrollment
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
