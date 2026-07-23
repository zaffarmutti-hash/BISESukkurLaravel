<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentAcademicRecord extends Model
{
    protected $fillable = [
        'student_id', 'academic_year_id', 'class_level', 'subject_group',
        'student_type', 'status', 'previous_record_id', 'is_locked', 'locked_at',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    // ─── Status Constants ─────────────────────────────────────────────────────
    public const STATUS_DRAFT            = 'draft';
    public const STATUS_FINAL            = 'final';
    public const STATUS_PENDING_CHALLAN  = 'pending_challan';
    public const STATUS_CHALLAN_SUBMITTED = 'challan_submitted';
    public const STATUS_ENROLLED         = 'enrolled';

    // ─── Status Scopes ────────────────────────────────────────────────────────
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeFinal($query)
    {
        return $query->where('status', self::STATUS_FINAL);
    }

    public function scopeChallanEligible($query)
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

    public function isInChallanLifecycle(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_CHALLAN,
            self::STATUS_CHALLAN_SUBMITTED,
            self::STATUS_ENROLLED,
        ]);
    }

    public function markAsFinal(): void
    {
        $this->update(['status' => self::STATUS_FINAL]);
    }

    public function markAsDraft(): void
    {
        $this->update(['status' => self::STATUS_DRAFT]);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function previousRecord(): BelongsTo
    {
        return $this->belongsTo(StudentAcademicRecord::class, 'previous_record_id');
    }

    public function invoiceStudents(): HasMany
    {
        return $this->hasMany(InvoiceStudent::class, 'student_academic_record_id');
    }

    // Alias for backward compatibility
    public function challanStudents(): HasMany
    {
        return $this->hasMany(InvoiceStudent::class, 'student_academic_record_id');
    }

    public function examForms(): HasMany
    {
        return $this->hasMany(ExamForm::class);
    }

    public function lock(): void
    {
        $this->update(['is_locked' => true, 'locked_at' => now()]);
    }
}
