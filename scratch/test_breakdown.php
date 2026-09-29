<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$school = \App\Models\School::first();
$schoolId = $school->id;

$records = \App\Models\StudentAcademicRecord::whereHas('student', fn($q) => $q->where('school_id', $schoolId))
    ->select('class_level', 'status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
    ->groupBy('class_level', 'status')
    ->get();

print_r($records->toArray());
