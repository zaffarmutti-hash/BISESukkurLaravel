<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamTimetable extends Model
{
    protected $fillable = [
        'academic_year_id', 'exam_center_id', 'class_level',
        'subject_name', 'subject_code', 'exam_date',
        'start_time', 'end_time', 'total_marks',
    ];

    protected $casts = [
        'exam_date'   => 'date',
        'total_marks' => 'integer',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function examCenter(): BelongsTo
    {
        return $this->belongsTo(ExamCenter::class);
    }

    public function examFormSubjects(): HasMany
    {
        return $this->hasMany(ExamFormSubject::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
}
