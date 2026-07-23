<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentSequence extends Model
{
    protected $fillable = [
        'academic_year_id',
        'school_id',
        'subject_group',
        'next_sequence',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
