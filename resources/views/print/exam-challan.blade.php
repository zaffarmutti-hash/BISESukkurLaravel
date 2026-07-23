{{-- Exam challan: same structure as enrollment-challan but labeled EXAMINATION FEE --}}
@php $type = 'EXAMINATION FEE'; @endphp
@include('print.enrollment-challan', ['type' => $type])
