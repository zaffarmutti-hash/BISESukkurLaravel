<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Enrollment Form — {{ $student->full_name }}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; font-size: 11px; color: #111; background: #fff; }
  .page { width: 210mm; margin: 0 auto; padding: 12mm; position: relative; }
  .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 15px; }
  .header h1 { font-size: 15px; font-weight: bold; }
  .header h2 { font-size: 11px; margin-top: 2px; }
  .body-content { display: flex; gap: 15px; margin-bottom: 15px; }
  .photo-box { width: 100px; flex-shrink: 0; }
  .photo-box img { width: 100px; height: 120px; object-fit: cover; border: 1.5px solid #333; }
  .photo-placeholder { width: 100px; height: 120px; border: 1.5px dashed #999; display: flex; align-items: center; justify-content: center; font-size: 9px; color: #777; font-weight: bold; text-align: center; }
  .info-box { flex: 1; }
  table.info-table { width: 100%; border-collapse: collapse; font-size: 11px; }
  table.info-table td { padding: 4px 6px; border-bottom: 1px dotted #ccc; }
  table.info-table td:first-child { font-weight: bold; width: 35%; color: #333; }
  .section { margin: 15px 0; }
  .section-title { font-weight: bold; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #111; margin-bottom: 8px; padding-bottom: 2px; color: #1B3A6B; }
  
  /* Watermark styling for DomPDF */
  .watermark {
    position: absolute;
    top: 35%;
    left: 10%;
    width: 80%;
    text-align: center;
    font-size: 64px;
    font-weight: bold;
    color: rgba(239, 68, 68, 0.15);
    border: 8px solid rgba(239, 68, 68, 0.15);
    padding: 15px;
    transform: rotate(-25deg);
    font-family: Arial, sans-serif;
    text-transform: uppercase;
    pointer-events: none;
    z-index: 10;
  }
  
  .footer { margin-top: 40px; display: flex; justify-content: space-between; font-size: 10px; }
  .sign-box { border-top: 1.5px solid #111; width: 140px; text-align: center; padding-top: 4px; margin-top: 40px; }
  @media print { body { -webkit-print-color-adjust: exact; } }
</style>
</head>
<body>
<div class="page">
  
  @if($status === 'draft')
    <div class="watermark">Draft Copy</div>
  @endif

  <div class="header">
    <h1>BOARD OF INTERMEDIATE AND SECONDARY EDUCATION, SUKKUR</h1>
    <h2>STUDENT ENROLLMENT REGISTRATION FORM — {{ $academicYear->label }}</h2>
  </div>

  <div class="body-content">
    <div class="photo-box">
      @if($student->photo_path)
        <img src="{{ storage_path('app/' . $student->photo_path) }}" alt="Student Photo">
      @else
        <div class="photo-placeholder">STUDENT<br>PHOTO</div>
      @endif
    </div>
    
    <div class="info-box">
      <div class="section-title" style="margin-top: 0;">Personal Details</div>
      <table class="info-table">
        <tr><td>Full Name:</td><td><strong>{{ $student->full_name }}</strong></td></tr>
        <tr><td>Father's Name:</td><td>{{ $student->father_name }}</td></tr>
        <tr><td>Gender:</td><td style="text-transform: capitalize;">{{ $student->gender }}</td></tr>
        <tr><td>Date of Birth:</td><td>{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d-M-Y') : '—' }}</td></tr>
        <tr><td>CNIC Number:</td><td style="font-family: monospace;">{{ $student->cnic ?? '—' }}</td></tr>
        <tr><td>B-Form Number:</td><td style="font-family: monospace;">{{ $student->b_form ?? '—' }}</td></tr>
        <tr><td>Contact Phone:</td><td>{{ $student->phone ?? '—' }}</td></tr>
        <tr><td>Guardian Phone:</td><td>{{ $student->guardian_phone ?? '—' }}</td></tr>
        <tr><td>Nationality:</td><td>{{ $student->nationality }}</td></tr>
        <tr><td>Religion:</td><td>{{ $student->religion ?? '—' }}</td></tr>
      </table>
    </div>
  </div>

  <div class="section">
    <div class="section-title">Academic Details</div>
    <table class="info-table">
      <tr><td>Class Level:</td><td><strong>{{ strtoupper(str_replace('_', ' ', $academicRecord->class_level)) }}</strong></td></tr>
      <tr><td>Subject Group:</td><td style="text-transform: capitalize;">{{ $academicRecord->subject_group }}</td></tr>
      <tr><td>Student Type:</td><td style="text-transform: capitalize;">{{ $academicRecord->student_type }}</td></tr>
      <tr><td>School Name:</td><td>{{ $student->school->name }}</td></tr>
      <tr><td>School Code:</td><td>{{ $student->school->code }}</td></tr>
      <tr><td>District:</td><td>{{ $student->school->district->name ?? '—' }}</td></tr>
      <tr><td>Enrollment Number:</td><td><strong style="font-family: monospace; letter-spacing: 1px;">{{ $student->enrollment_number ?? 'Awaiting Board confirmation' }}</strong></td></tr>
    </table>
  </div>

  <div class="section">
    <div class="section-title">Residential Address</div>
    <p style="padding: 5px 6px; font-size: 11px; line-height: 1.4; border-bottom: 1px dotted #ccc;">
      {{ $student->address ?? '—' }}
    </p>
  </div>

  <div class="footer">
    <div>
      <div class="sign-box">Signature of Candidate</div>
    </div>
    <div>
      <div class="sign-box">Principal Signature &amp; Stamp</div>
    </div>
    <div style="text-align: right; padding-top: 30px;">
      <div>Generated: {{ now()->format('d-M-Y H:i') }}</div>
      <div>BISE Sukkur Portal — Computer Generated Form</div>
    </div>
  </div>

</div>
</body>
</html>
