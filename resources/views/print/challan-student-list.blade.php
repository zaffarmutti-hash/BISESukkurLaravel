<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
<title>Student List — Challan {{ $challan->challan_number }}</title>
<style>
  body { font-family: Arial, sans-serif; font-size: 11px; color: #111; }
  .page { padding: 12mm; }
  .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #333; color: #fff; padding: 5px 8px; text-align: left; font-size: 10px; }
  td { padding: 4px 8px; border-bottom: 1px solid #ddd; }
  tr:nth-child(even) td { background: #f9f9f9; }
  .total-row td { font-weight: bold; border-top: 2px solid #333; }
  @media print { body { -webkit-print-color-adjust: exact; } }
</style>
</head>
<body><div class="page">
  <div class="header">
    <strong>BISE SUKKUR — ENROLLMENT CHALLAN STUDENT LIST</strong><br>
    Challan No: {{ $challan->challan_number }} | School: {{ $challan->school->name }} | Year: {{ $challan->academicYear->label }}
  </div>
  <table>
    <thead>
      <tr><th>#</th><th>Student Name</th><th>Father's Name</th><th>Class</th><th>Group</th><th>Type</th><th>Fee (PKR)</th></tr>
    </thead>
    <tbody>
      @foreach($challan->challanStudents as $i => $cs)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $cs->student->full_name }}</td>
        <td>{{ $cs->student->father_name }}</td>
        <td>{{ strtoupper($cs->studentAcademicRecord->class_level) }}</td>
        <td>{{ ucfirst($cs->studentAcademicRecord->subject_group) }}</td>
        <td>{{ ucfirst($cs->studentAcademicRecord->student_type) }}</td>
        <td>{{ number_format($cs->amount_paisas / 100, 2) }}</td>
      </tr>
      @endforeach
      <tr class="total-row">
        <td colspan="6" style="text-align:right">TOTAL:</td>
        <td>PKR {{ number_format($challan->total_amount_paisas / 100, 2) }}</td>
      </tr>
    </tbody>
  </table>
  <p style="margin-top:10px;font-size:9px;color:#888">Generated: {{ now()->format('d-M-Y H:i') }} — BISE Sukkur Official Document</p>
</div></body></html>
