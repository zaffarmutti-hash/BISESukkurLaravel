@extends('layouts.superadmin')

@php
    $title             = 'Bulk Import Institutions';
    $breadcrumbSection = 'Management';
    $breadcrumbCurrent = 'Import Schools';
@endphp

@section('content')
<div class="sa-animate-fade-up" style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

    <!-- Breadcrumb & Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <a href="{{ route('superadmin.schools') }}" style="font-size: 13px; color: #64748b; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                <span>Back to Schools Directory</span>
            </a>
            <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Bulk Register Schools & Colleges</h1>
            <p style="font-size: 13.5px; color: #64748b; margin: 4px 0 0 0;">Upload a CSV batch file to atomically register schools and generate their administrator credentials.</p>
        </div>
        <div>
            <a href="{{ route('superadmin.schools.import.template') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; padding: 10px 18px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border-radius: 8px; box-shadow: 0 4px 12px rgba(27,58,107,0.25);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Download Sample CSV Template</span>
            </a>
        </div>
    </div>

    <!-- Import Results Alert (if processed) -->
    @if(session('import_completed'))
        <div class="sa-panel" style="border: 2px solid {{ empty(session('errors')) ? '#10b981' : '#f59e0b' }}; background: #ffffff; padding: 24px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: {{ empty(session('errors')) ? '#ecfdf5' : '#fffbeb' }}; color: {{ empty(session('errors')) ? '#10b981' : '#d97706' }}; display: flex; align-items: center; justify-content: center;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Import Process Completed</h3>
                    <p style="font-size: 13px; color: #64748b; margin: 2px 0 0 0;">
                        Successfully registered <strong>{{ session('valid_count', 0) }}</strong> institutions.
                        @if(!empty(session('errors')))
                            Encountered {{ count(session('errors')) }} issue(s).
                        @endif
                    </p>
                </div>
            </div>

            @if(!empty(session('errors')))
                <div style="margin-top: 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; max-height: 240px; overflow-y: auto;">
                    <div style="font-size: 12px; font-weight: 800; color: #991b1b; margin-bottom: 8px; text-transform: uppercase;">Validation Error Report:</div>
                    <ul style="margin: 0; padding-left: 20px; font-size: 12.5px; color: #b91c1c; display: flex; flex-direction: column; gap: 4px;">
                        @foreach(session('errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div style="margin-top: 16px; display: flex; gap: 12px;">
                <a href="{{ route('superadmin.schools') }}" class="sa-header-btn" style="background: #1B3A6B; color: #fff; padding: 8px 18px; font-weight: 700; text-decoration: none; border-radius: 6px;">
                    View Schools Directory &rarr;
                </a>
            </div>
        </div>
    @endif

    <!-- Upload Card -->
    <div class="sa-panel">
        <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <span>Upload CSV File</span>
        </h3>

        <form method="POST" action="{{ route('superadmin.schools.import.process') }}" enctype="multipart/form-data">
            @csrf

            <div style="border: 2px dashed #cbd5e1; border-radius: 14px; padding: 36px 20px; text-align: center; background: #f8fafc; transition: all 0.2s;" id="dropZone">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: #e2e8f0; color: #475569; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                </div>
                <div style="font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">Choose a CSV file or drag & drop here</div>
                <div style="font-size: 12.5px; color: #64748b; margin-bottom: 18px;">Files must be in UTF-8 .csv format with maximum size 5MB.</div>

                <input type="file" name="csv_file" id="csvFileInput" accept=".csv,text/csv" required style="display: none;" onchange="updateFileName(this)">
                <button type="button" onclick="document.getElementById('csvFileInput').click()" class="sa-header-btn" style="border: 1px solid #cbd5e1; background: #fff; padding: 9px 22px; font-weight: 700; cursor: pointer; border-radius: 8px;">
                    Browse Computer
                </button>
                <div id="selectedFileName" style="font-size: 13px; font-weight: 700; color: #1B3A6B; margin-top: 12px; display: none;"></div>
            </div>

            @error('csv_file')
                <div style="color: #ef4444; font-size: 12.5px; font-weight: 600; margin-top: 8px;">{{ $message }}</div>
            @enderror

            <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                <button type="submit" class="sa-header-btn" style="background: linear-gradient(135deg, #1B3A6B, #2d5a9e); color: #fff; border: none; padding: 11px 28px; font-weight: 800; font-size: 14px; cursor: pointer; border-radius: 8px; box-shadow: 0 4px 14px rgba(27,58,107,0.3);">
                    Process Batch Import
                </button>
            </div>
        </form>
    </div>

    <!-- Required Columns Reference Panel -->
    <div class="sa-panel">
        <h3 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 14px 0;">CSV Column Specifications</h3>
        <div style="overflow-x: auto;">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Column Header</th>
                        <th>Format / Rule</th>
                        <th>Required</th>
                        <th>Sample Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>semis_code</code></td>
                        <td>Exactly 8 digits, unique</td>
                        <td><span style="color: #ef4444; font-weight: 700;">Yes</span></td>
                        <td>40102001</td>
                    </tr>
                    <tr>
                        <td><code>school_name</code></td>
                        <td>Full name of school/college</td>
                        <td><span style="color: #ef4444; font-weight: 700;">Yes</span></td>
                        <td>Govt Higher Secondary School Sukkur</td>
                    </tr>
                    <tr>
                        <td><code>district_code</code></td>
                        <td>One of: SUK, KHP, GHT, NSK, KSR</td>
                        <td><span style="color: #ef4444; font-weight: 700;">Yes</span></td>
                        <td>SUK</td>
                    </tr>
                    <tr>
                        <td><code>type</code></td>
                        <td><code>public</code> or <code>private</code></td>
                        <td><span style="color: #ef4444; font-weight: 700;">Yes</span></td>
                        <td>public</td>
                    </tr>
                    <tr>
                        <td><code>gender</code></td>
                        <td><code>boys</code>, <code>girls</code>, or <code>co_education</code></td>
                        <td><span style="color: #ef4444; font-weight: 700;">Yes</span></td>
                        <td>boys</td>
                    </tr>
                    <tr>
                        <td><code>head_name</code></td>
                        <td>Principal / Headmaster Name</td>
                        <td><span style="color: #ef4444; font-weight: 700;">Yes</span></td>
                        <td>Ghulam Hussain</td>
                    </tr>
                    <tr>
                        <td><code>head_mobile</code></td>
                        <td>Format 03XX-XXXXXXX</td>
                        <td><span style="color: #ef4444; font-weight: 700;">Yes</span></td>
                        <td>0300-1234567</td>
                    </tr>
                    <tr>
                        <td><code>head_email</code></td>
                        <td>Valid institutional email</td>
                        <td>Optional</td>
                        <td>ghss@bisesukkur.edu.pk</td>
                    </tr>
                    <tr>
                        <td><code>address</code></td>
                        <td>Campus physical location</td>
                        <td>Optional</td>
                        <td>Military Road, Sukkur</td>
                    </tr>
                    <tr>
                        <td><code>levels</code></td>
                        <td>Comma-separated: ssc_part1, ssc_part2, hsc_part1, hsc_part2</td>
                        <td><span style="color: #ef4444; font-weight: 700;">Yes</span></td>
                        <td>ssc_part1,ssc_part2</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateFileName(input) {
        const file = input.files[0];
        const display = document.getElementById('selectedFileName');
        if (file) {
            display.innerText = "Selected file: " + file.name + " (" + (file.size / 1024).toFixed(1) + " KB)";
            display.style.display = 'block';
        } else {
            display.style.display = 'none';
        }
    }
</script>
@endpush
@endsection
