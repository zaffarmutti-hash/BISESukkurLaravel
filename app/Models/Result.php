<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Result extends Model
{
    protected $fillable = [
        'student_id', 'academic_year_id', 'exam_form_id', 'exam_timetable_id',
        'subject_name', 'subject_code', 'total_marks', 'marks_obtained',
        'passing_percentage', 'is_pass', 'percentage', 'grade', 'status',
        'entered_by', 'entered_at', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'is_pass'            => 'boolean',
        'total_marks'        => 'integer',
        'marks_obtained'     => 'integer',
        'passing_percentage' => 'decimal:2',
        'percentage'         => 'decimal:2',
        'entered_at'         => 'datetime',
        'verified_at'        => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function examForm(): BelongsTo
    {
        return $this->belongsTo(ExamForm::class);
    }

    public function examTimetable(): BelongsTo
    {
        return $this->belongsTo(ExamTimetable::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function certificateSubjects(): HasMany
    {
        return $this->hasMany(CertificateSubject::class);
    }
}
