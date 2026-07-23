<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Enrollment Challan — {{ $challan->challan_number }}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; font-size: 12px; color: #111; background: #fff; }
  .page { width: 210mm; margin: 0 auto; padding: 15mm; }
  .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
  .header h1 { font-size: 18px; font-weight: bold; }
  .header h2 { font-size: 13px; font-weight: normal; }
  .challan-no { font-size: 16px; font-weight: bold; font-family: monospace; letter-spacing: 2px; }
  .section { margin-bottom: 12px; }
  .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #ccc; padding-bottom: 3px; margin-bottom: 6px; }
  table.info { width: 100%; border-collapse: collapse; }
  table.info td { padding: 3px 6px; }
  table.info td:first-child { font-weight: bold; width: 40%; }
  .amount-box { border: 2px solid #000; padding: 10px; text-align: center; margin: 10px 0; }
  .amount-box .amount { font-size: 24px; font-weight: bold; font-family: monospace; }
  .amount-box .amount-words { font-size: 11px; color: #555; margin-top: 4px; }
  .footer { margin-top: 20px; display: flex; justify-content: space-between; font-size: 10px; }
  .sign-box { border-top: 1px solid #000; width: 120px; text-align: center; padding-top: 3px; }
  .bank-strip { background: #f5f5f5; border: 1px dashed #999; padding: 8px; margin-top: 15px; font-size: 10px; }
  @media print { body { -webkit-print-color-adjust: exact; } }
</style>
</head>
<body>
<div class="page">
  <div class="header">
    <h1>BOARD OF INTERMEDIATE AND SECONDARY EDUCATION</h1>
    <h2>SUKKUR — Sindh, Pakistan</h2>
    <h2>ENROLLMENT FEE PAYMENT CHALLAN</h2>
    <div class="challan-no">{{ $challan->challan_number }}</div>
  </div>

  <div class="section">
    <div class="section-title">School Information</div>
    <table class="info">
      <tr><td>School Name:</td><td>{{ $challan->school->name }}</td></tr>
      <tr><td>School Code:</td><td>{{ $challan->school->code }}</td></tr>
      <tr><td>District:</td><td>{{ $challan->school->district->name }}</td></tr>
      <tr><td>Academic Year:</td><td>{{ $challan->academicYear->label }}</td></tr>
    </table>
  </div>

  <div class="section">
    <div class="section-title">Payment Details</div>
    <table class="info">
      <tr><td>Total Students:</td><td>{{ $challan->student_count }}</td></tr>
      <tr><td>Challan Date:</td><td>{{ now()->format('d-M-Y') }}</td></tr>
      <tr><td>Payment Type:</td><td>ENROLLMENT FEE</td></tr>
    </table>
  </div>

  <div class="amount-box">
    <div>TOTAL AMOUNT DUE</div>
    <div class="amount">PKR {{ number_format($challan->total_amount_paisas / 100, 2) }}</div>
    <div class="amount-words">{{-- TODO: Add number-to-words helper --}}</div>
  </div>

  <div class="bank-strip">
    <strong>BANK COPY</strong> — Pay this amount at any branch of the designated bank. Keep this challan after payment for your records.
    Challan No: {{ $challan->challan_number }}
  </div>

  <div class="footer">
    <div>
      <div class="sign-box">School Principal</div>
    </div>
    <div class="text-right">
      <div>Generated: {{ now()->format('d-M-Y H:i') }}</div>
      <div>BISE Sukkur — Official Document</div>
    </div>
  </div>
</div>
</body>
</html>
