<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$school = \App\Models\School::first();
$schoolId = $school->id;

$recordsByClass = \App\Models\StudentAcademicRecord::whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
    ->select('class_level', 'status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
    ->groupBy('class_level', 'status')
    ->get();

$classConfigs = [
    [
        'key' => 'ssc_part1',
        'match_keys' => ['ssc_part1', '9th', 'SSC-I', 'ssc1', 'ssc-1'],
        'name' => 'Class 9th (SSC-I)',
        'section' => 'Matric',
    ],
    [
        'key' => 'ssc_part2',
        'match_keys' => ['ssc_part2', '10th', 'SSC-II', 'ssc2', 'ssc-2'],
        'name' => 'Class 10th (SSC-II)',
        'section' => 'Matric',
    ],
    [
        'key' => 'hsc_part1',
        'match_keys' => ['hsc_part1', '11th', 'HSC-I', 'hsc1', 'hsc-1'],
        'name' => 'Class 11th (HSC-I)',
        'section' => 'Intermediate',
    ],
    [
        'key' => 'hsc_part2',
        'match_keys' => ['hsc_part2', '12th', 'HSC-II', 'hsc2', 'hsc-2'],
        'name' => 'Class 12th (HSC-II)',
        'section' => 'Intermediate',
    ],
];

$breakdown = [];
foreach ($classConfigs as $cfg) {
    $matching = $recordsByClass->filter(fn($r) => in_array($r->class_level, $cfg['match_keys']));
    $total = $matching->sum('count');
    $enrolled = $matching->where('status', 'enrolled')->sum('count');
    $examCount = \App\Models\ExamForm::whereHas('studentAcademicRecord', fn($q) => $q->whereIn('class_level', $cfg['match_keys'])->whereHas('student', fn($sq) => $sq->where('school_id', $schoolId)))->count();

    $breakdown[] = [
        'name' => $cfg['name'],
        'section' => $cfg['section'],
        'total' => $total,
        'enrolled' => $enrolled,
        'exam' => $examCount,
    ];
}

print_r($breakdown);
