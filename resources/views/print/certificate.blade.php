<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certificate — {{ $certificate->student->enrollment_number }}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Georgia', serif; color: #111; background: #fff; }
  .page { width: 210mm; margin: 0 auto; padding: 15mm; border: 8px double #8b6914; min-height: 290mm; position: relative; }
  .border-inner { border: 2px solid #c9a227; padding: 10mm; min-height: 260mm; }
  .header { text-align: center; margin-bottom: 15px; }
  .crest { font-size: 40px; margin-bottom: 8px; }
  .org-name { font-size: 18px; font-weight: bold; letter-spacing: 1px; color: #6b4f1a; }
  .sub-name { font-size: 12px; color: #555; margin-top: 2px; }
  .cert-title { font-size: 28px; font-weight: bold; color: #6b4f1a; margin: 15px 0 5px; font-variant: small-caps; }
  .cert-subtitle { font-size: 13px; color: #666; }
  .divider { border: none; border-top: 1px solid #c9a227; margin: 12px 0; }
  .student-section { text-align: center; margin: 15px 0; }
  .student-name { font-size: 22px; font-weight: bold; border-bottom: 1px dotted #8b6914; display: inline-block; padding-bottom: 3px; margin-bottom: 5px; }
  .student-meta { font-size: 12px; color: #444; }
  .result-box { border: 1px solid #c9a227; padding: 10px; margin: 12px 0; background: #fffdf5; }
  .result-summary { display: flex; justify-content: space-around; text-align: center; }
  .result-item .label { font-size: 10px; text-transform: uppercase; color: #888; }
  .result-item .value { font-size: 18px; font-weight: bold; color: #6b4f1a; }
  table.subjects { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
  table.subjects th { background: #6b4f1a; color: #fff; padding: 4px 8px; text-align: left; }
  table.subjects td { padding: 4px 8px; border-bottom: 1px solid #e8d5a0; }
  .qr-section { position: absolute; bottom: 20mm; right: 15mm; text-align: center; }
  .qr-label { font-size: 9px; color: #888; margin-top: 4px; }
  .footer-sigs { display: flex; justify-content: space-between; margin-top: 25px; }
  .sig-block { text-align: center; }
  .sig-line { border-top: 1px solid #333; width: 140px; padding-top: 3px; font-size: 10px; }
  .verify-url { font-size: 9px; color: #888; font-family: monospace; margin-top: 4px; }
  @media print { body { -webkit-print-color-adjust: exact; } @page { size: A4; margin: 0; } }
</style>
</head>
<body>
<div class="page">
<div class="border-inner">

  <div class="header">
    <div class="crest">🎓</div>
    <div class="org-name">BOARD OF INTERMEDIATE AND SECONDARY EDUCATION</div>
    <div class="sub-name">SUKKUR — Sindh, Pakistan</div>
    <div class="cert-title">{{ strtoupper($certificate->level) }} CERTIFICATE</div>
    <div class="cert-subtitle">Academic Year {{ $certificate->academicYear->label }}</div>
  </div>

  <hr class="divider">

  <div class="student-section">
    <p style="font-size:12px;color:#555;margin-bottom:8px">This is to certify that</p>
    <div class="student-name">{{ $certificate->student->full_name }}</div>
    <div class="student-meta">
      Son/Daughter of: {{ $certificate->student->father_name }}<br>
      Enrollment No: <strong style="font-family:monospace">{{ $certificate->student->enrollment_number }}</strong><br>
      School: {{ $certificate->student->school->name }}<br>
      District: {{ $certificate->student->school->district->name }}
    </div>
  </div>

  <div class="result-box">
    <div class="result-summary">
      <div class="result-item">
        <div class="label">Total Marks</div>
        <div class="value">{{ $certificate->subjects->sum('total_marks') }}</div>
      </div>
      <div class="result-item">
        <div class="label">Marks Obtained</div>
        <div class="value">{{ $certificate->subjects->sum('marks_obtained') }}</div>
      </div>
      <div class="result-item">
        <div class="label">Percentage</div>
        <div class="value">{{ number_format($certificate->total_percentage, 2) }}%</div>
      </div>
      <div class="result-item">
        <div class="label">Grade</div>
        <div class="value">{{ $certificate->overall_grade }}</div>
      </div>
      <div class="result-item">
        <div class="label">Division</div>
        <div class="value">{{ $certificate->division }}</div>
      </div>
    </div>
  </div>

  <table class="subjects">
    <thead>
      <tr><th>Subject</th><th>Code</th><th>Total</th><th>Obtained</th><th>Grade</th></tr>
    </thead>
    <tbody>
      @foreach($certificate->subjects as $sub)
      <tr>
        <td>{{ $sub->subject_name }}</td>
        <td style="font-family:monospace">{{ $sub->subject_code }}</td>
        <td>{{ $sub->total_marks }}</td>
        <td>{{ $sub->marks_obtained }}</td>
        <td><strong>{{ $sub->grade }}</strong></td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="qr-section">
    {!! QrCode::size(80)->generate($certificate->verification_url ?? url('/certificates/' . $certificate->verification_token . '/verify')) !!}
    <div class="qr-label">Scan to Verify</div>
    <div class="verify-url">{{ $certificate->verification_token }}</div>
  </div>

  <div class="footer-sigs">
    <div class="sig-block">
      <div class="sig-line">Controller of Examinations</div>
    </div>
    <div class="sig-block">
      <div class="sig-line">Chairman, BISE Sukkur</div>
    </div>
  </div>

  <p style="font-size:9px;color:#aaa;text-align:center;margin-top:15px">
    Issued: {{ $certificate->issued_at?->format('d F Y') ?? now()->format('d F Y') }} —
    This certificate is valid only with the official seal and signatures above.
    Verify authenticity at: {{ url('/certificates/' . $certificate->verification_token . '/verify') }}
  </p>

</div>
</div>
</body>
</html>
