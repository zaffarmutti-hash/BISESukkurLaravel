<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$school = \App\Models\School::first();
echo "School: {$school->name} (ID: {$school->id})\n";

$levels = \App\Models\StudentAcademicRecord::whereHas('student', fn($q) => $q->where('school_id', $school->id))
    ->select('class_level', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
    ->groupBy('class_level')
    ->get();

echo "Academic Records by Class Level:\n";
foreach ($levels as $l) {
    echo " - Level: '{$l->class_level}' => {$l->count}\n";
}

$invoices = \App\Models\Invoice::where('school_id', $school->id)->get();
echo "Invoices count: " . $invoices->count() . "\n";
foreach ($invoices as $inv) {
    echo " - Inv #{$inv->invoice_number}: Type={$inv->invoice_type}, Status={$inv->status}, Amount=" . ($inv->total_amount_paisas / 100) . "\n";
}

$examForms = \App\Models\ExamForm::whereHas('student', fn($q) => $q->where('school_id', $school->id))->get();
echo "Exam Forms count: " . $examForms->count() . "\n";
foreach ($examForms as $ef) {
    echo " - ExamForm #{$ef->id}: Status={$ef->status}, ClassLevel={$ef->class_level}\n";
}
