<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admission Slip — {{ $student->enrollment_number }}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; font-size: 11px; color: #111; background: #fff; }
  .page { width: 210mm; margin: 0 auto; padding: 12mm; }
  .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 12px; }
  .header h1 { font-size: 16px; font-weight: bold; }
  .header h2 { font-size: 11px; }
  .slip-body { display: flex; gap: 15px; }
  .photo-box { width: 90px; flex-shrink: 0; }
  .photo-box img { width: 90px; height: 110px; object-fit: cover; border: 1px solid #333; }
  .photo-placeholder { width: 90px; height: 110px; border: 1px solid #333; display: flex; align-items: center; justify-content: center; font-size: 9px; color: #999; }
  .student-info { flex: 1; }
  table.info { width: 100%; border-collapse: collapse; font-size: 11px; }
  table.info td { padding: 3px 5px; border-bottom: 1px dotted #ddd; }
  table.info td:first-child { font-weight: bold; width: 35%; }
  .section { margin: 12px 0; }
  .section-title { font-weight: bold; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #000; margin-bottom: 6px; padding-bottom: 2px; }
  table.subjects { width: 100%; border-collapse: collapse; font-size: 10px; }
  table.subjects th, table.subjects td { border: 1px solid #ccc; padding: 3px 6px; }
  table.subjects th { background: #f0f0f0; text-align: left; }

  /* ═══ BUBBLE GRID — Seat Number ═══════════════════════════════════════ */
  .bubble-section { margin-top: 15px; border: 2px solid #000; padding: 10px; }
  .bubble-section h3 { font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; text-align: center; }
  .bubble-grid { display: flex; gap: 8px; justify-content: center; }
  .bubble-col { display: flex; flex-direction: column; align-items: center; gap: 3px; }
  .bubble-col-header { font-size: 13px; font-weight: bold; font-family: monospace; width: 22px; text-align: center; margin-bottom: 3px; }
  .bubble { width: 22px; height: 22px; border: 1.5px solid #333; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 9px; font-family: monospace; font-weight: bold; }
  .bubble.filled { background: #111; color: #fff; border-color: #111; }
  .bubble-instruction { font-size: 9px; color: #555; text-align: center; margin-top: 6px; }
  .font-urdu { font-family: 'Noto Nastaliq Urdu', 'Traditional Arabic', serif; direction: rtl; font-size: 10px; }

  .footer-strip { margin-top: 15px; display: flex; justify-content: space-between; font-size: 10px; }
  .sign-box { border-top: 1px solid #000; width: 120px; text-align: center; padding-top: 3px; margin-top: 30px; }
  @media print { body { -webkit-print-color-adjust: exact; } }
</style>
</head>
<body>
<div class="page">
  <div class="header">
    <h1>BOARD OF INTERMEDIATE AND SECONDARY EDUCATION, SUKKUR</h1>
    <h2>OFFICIAL EXAMINATION ADMISSION SLIP — {{ $examForm->academicYear->label }}</h2>
  </div>

  <div class="slip-body">
    <div class="photo-box">
      @if($student->photo_path)
        <img src="{{ storage_path('app/' . $student->photo_path) }}" alt="Student Photo">
      @else
        <div class="photo-placeholder">PHOTO</div>
      @endif
    </div>
    <div class="student-info">
      <table class="info">
        <tr><td>Student Name:</td><td><strong>{{ $student->full_name }}</strong></td></tr>
        <tr><td>Father's Name:</td><td>{{ $student->father_name }}</td></tr>
        <tr><td>Enrollment No.:</td><td><strong style="font-family:monospace;letter-spacing:2px">{{ $student->enrollment_number }}</strong></td></tr>
        <tr><td>Seat Number:</td><td><strong style="font-family:monospace;letter-spacing:2px;font-size:14px">{{ $examForm->seat_number }}</strong></td></tr>
        <tr><td>Class:</td><td>{{ strtoupper($examForm->studentAcademicRecord->class_level) }}</td></tr>
        <tr><td>Group:</td><td>{{ ucfirst($examForm->studentAcademicRecord->subject_group) }}</td></tr>
        <tr><td>School:</td><td>{{ $student->school->name }}</td></tr>
        <tr><td>Exam Center:</td><td>{{ $examForm->examCenter?->name ?? 'TBA' }}</td></tr>
      </table>
    </div>
  </div>

  <!-- Subjects -->
  <div class="section">
    <div class="section-title">Examination Schedule</div>
    <table class="subjects">
      <thead>
        <tr><th>#</th><th>Subject</th><th>Code</th><th>Date</th><th>Time</th><th>Marks</th></tr>
      </thead>
      <tbody>
        @foreach($examForm->subjects as $i => $subject)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td>{{ $subject->subject_name }}</td>
          <td style="font-family:monospace">{{ $subject->subject_code }}</td>
          <td>{{ $subject->examTimetable?->exam_date?->format('d-M-Y') ?? '—' }}</td>
          <td>{{ $subject->examTimetable?->start_time ?? '—' }}</td>
          <td>{{ $subject->examTimetable?->total_marks ?? '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- Bubble Grid: Seat Number -->
  <div class="bubble-section">
    <h3>Seat Number — Fill bubbles on Answer Sheet exactly as shown below</h3>
    <div class="bubble-grid">
      @php
        $seatDigits = str_split(str_pad($examForm->seat_number ?? '0', 6, '0', STR_PAD_LEFT));
      @endphp
      @foreach($seatDigits as $colIndex => $digit)
      <div class="bubble-col">
        <div class="bubble-col-header">{{ $digit }}</div>
        @for($d = 0; $d <= 9; $d++)
        <div class="bubble {{ (string)$d === $digit ? 'filled' : '' }}">{{ $d }}</div>
        @endfor
      </div>
      @endforeach
    </div>
    <div class="bubble-instruction">
      Fill the bubble corresponding to each digit of your seat number ({{ $examForm->seat_number }}) on your answer sheet.
    </div>
    <div class="bubble-instruction font-urdu">
      اپنے جوابی پرچے پر اپنا نشست نمبر ({{ $examForm->seat_number }}) کے ہر ہندسے کے مطابق بلبلے کو بھریں۔
    </div>
  </div>

  <div class="footer-strip">
    <div>
      <div>Generated: {{ now()->format('d-M-Y H:i') }}</div>
      <div class="sign-box">Controller of Examinations<br>BISE Sukkur</div>
    </div>
    <div style="text-align:right; font-size:9px; color:#666;">
      This is a computer-generated document. No signature required.<br>
      Verify at: {{ url('/certificates') }}<br>
      BISE Sukkur — Official Document
    </div>
  </div>
</div>
</body>
</html>
