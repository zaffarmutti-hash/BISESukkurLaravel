<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamForm extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id', 'academic_year_id', 'student_academic_record_id',
        'exam_center_id', 'seat_number', 'seat_number_assigned_at',
        'admission_slip_generated', 'admission_slip_generated_at',
        'status', 'submitted_at', 'confirmed_at', 'rejection_reason',
    ];

    protected $casts = [
        'seat_number_assigned_at'       => 'datetime',
        'admission_slip_generated'      => 'boolean',
        'admission_slip_generated_at'   => 'datetime',
        'submitted_at'                  => 'datetime',
        'confirmed_at'                  => 'datetime',
    ];

    // ─── Status Constants ─────────────────────────────────────────────────────
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_FINAL     = 'final';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_REJECTED  = 'rejected';

    // ─── Status Scopes ────────────────────────────────────────────────────────
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeFinal($query)
    {
        return $query->where('status', self::STATUS_FINAL);
    }

    // ─── Status Helpers ───────────────────────────────────────────────────────
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    public function markAsFinal(): void
    {
        $this->update(['status' => self::STATUS_FINAL]);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function studentAcademicRecord(): BelongsTo
    {
        return $this->belongsTo(StudentAcademicRecord::class);
    }

    public function examCenter(): BelongsTo
    {
        return $this->belongsTo(ExamCenter::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(ExamFormSubject::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
}
