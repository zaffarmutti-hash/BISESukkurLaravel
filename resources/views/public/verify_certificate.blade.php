<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isValid ? 'Verified: ' . ($certificate->student->full_name ?? 'Candidate') : 'Verification Result' }} — BISE Sukkur</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0f172a;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .cert-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 640px;
            overflow: hidden;
            position: relative;
        }
        .cert-header {
            background: linear-gradient(135deg, #1B3A6B 0%, #0f172a 100%);
            padding: 28px 24px;
            color: #ffffff;
            text-align: center;
            border-bottom: 4px solid #C8960C;
        }
        .gold-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(200, 150, 12, 0.2);
            border: 1px solid #C8960C;
            color: #f1c40f;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 4px 14px;
            border-radius: 9999px;
            margin-bottom: 12px;
        }
        .cert-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.3px;
        }
        .cert-subtitle {
            font-size: 12.5px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 4px;
        }
        .cert-body {
            padding: 32px 28px;
        }
        .verified-banner {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .verified-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #10b981;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }
        .notfound-banner {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .notfound-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #ef4444;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
        }
        .data-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px 20px;
            margin-bottom: 28px;
        }
        @media (max-width: 480px) {
            .data-grid { grid-template-columns: 1fr; }
        }
        .data-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .data-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
        }
        .data-value {
            font-size: 14.5px;
            font-weight: 700;
            color: #0f172a;
        }
        .token-pill {
            font-family: 'JetBrains Mono', monospace;
            background: #f1f5f9;
            color: #1e293b;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            display: inline-block;
            font-weight: 700;
            border: 1px solid #e2e8f0;
        }
        .cert-footer {
            border-top: 1px solid #f1f5f9;
            padding: 18px 28px;
            background: #f8fafc;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="cert-card">
        <!-- Brand Header -->
        <div class="cert-header">
            <div class="gold-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                OFFICIAL VERIFICATION PORTAL
            </div>
            <h1 class="cert-title">Board of Intermediate & Secondary Education, Sukkur</h1>
            <p class="cert-subtitle">Government of Sindh — Statutory Academic Certification Record</p>
        </div>

        <div class="cert-body">
            @if($isValid)
                <!-- Verified Banner -->
                <div class="verified-banner">
                    <div class="verified-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div>
                        <div style="font-size: 17px; font-weight: 800; color: #065f46;">Verified by BISE Sukkur</div>
                        <div style="font-size: 12.5px; color: #047857; margin-top: 2px;">This document has been confirmed authentic in the board official registry.</div>
                    </div>
                </div>

                <!-- Academic Record Grid -->
                <div class="data-grid">
                    <div class="data-item">
                        <span class="data-label">Candidate Full Name</span>
                        <span class="data-value">{{ $certificate->student->full_name ?? '—' }}</span>
                    </div>

                    <div class="data-item">
                        <span class="data-label">Father's Name</span>
                        <span class="data-value">{{ $certificate->student->father_name ?? '—' }}</span>
                    </div>

                    <div class="data-item">
                        <span class="data-label">Examination Class / Level</span>
                        <span class="data-value">{{ strtoupper($certificate->level ?? 'SSC-II (Matriculation)') }}</span>
                    </div>

                    <div class="data-item">
                        <span class="data-label">Academic Session</span>
                        <span class="data-value">{{ $certificate->academicYear->name ?? '2026-2027' }}</span>
                    </div>

                    <div class="data-item">
                        <span class="data-label">Result Status / Division</span>
                        <span class="data-value" style="color: #059669;">
                            PASS — {{ $certificate->overall_grade ?? 'A-1 Grade' }} ({{ $certificate->division ?? '1st Division' }})
                        </span>
                    </div>

                    <div class="data-item">
                        <span class="data-label">Candidate Enrollment No.</span>
                        <span class="data-value" style="font-family: 'JetBrains Mono', monospace;">{{ $certificate->student->enrollment_number ?? '—' }}</span>
                    </div>

                    <div class="data-item" style="grid-column: span 2;">
                        <span class="data-label">Affiliated Institution</span>
                        <span class="data-value">{{ $certificate->student->school->name ?? 'Private Candidate' }}</span>
                        @if($certificate->student->school?->district)
                            <span style="font-size: 12px; color: #64748b;">District: {{ $certificate->student->school->district->name }}</span>
                        @endif
                    </div>

                    <div class="data-item" style="grid-column: span 2;">
                        <span class="data-label">Certificate Serial Token</span>
                        <span class="token-pill">{{ $token }}</span>
                    </div>
                </div>

            @else
                <!-- Invalid / Not Found Banner -->
                <div class="notfound-banner">
                    <div class="notfound-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <div>
                        <div style="font-size: 17px; font-weight: 800; color: #991b1b;">Certificate Not Found</div>
                        <div style="font-size: 12.5px; color: #b91c1c; margin-top: 2px;">Verification Code: <strong style="font-family: monospace;">{{ $token }}</strong></div>
                    </div>
                </div>

                <div style="font-size: 13.5px; color: #475569; line-height: 1.6; margin-bottom: 24px;">
                    The verification code provided does not match any registered academic certificate or diploma record in the BISE Sukkur central archives.
                    <br><br>
                    Please ensure that you scanned the official QR code printed on the physical certificate, or check for typos in the verification URL. If you suspect fraud, contact the Controller of Examinations.
                </div>
            @endif

            <div style="text-align: center; margin-top: 12px;">
                <a href="{{ url('/') }}" style="font-size: 13px; font-weight: 700; color: #1B3A6B; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <span>&larr; Return to BISE Sukkur Home</span>
                </a>
            </div>
        </div>

        <div class="cert-footer">
            &copy; {{ date('Y') }} Board of Intermediate & Secondary Education, Sukkur. All rights reserved.
        </div>
    </div>
</body>
</html>
