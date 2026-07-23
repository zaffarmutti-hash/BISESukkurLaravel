<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamFormSubject extends Model
{
    protected $fillable = [
        'exam_form_id', 'exam_timetable_id',
        'subject_name', 'subject_code', 'is_elective',
    ];

    protected $casts = [
        'is_elective' => 'boolean',
    ];

    public function examForm(): BelongsTo
    {
        return $this->belongsTo(ExamForm::class);
    }

    public function examTimetable(): BelongsTo
    {
        return $this->belongsTo(ExamTimetable::class);
    }
}
