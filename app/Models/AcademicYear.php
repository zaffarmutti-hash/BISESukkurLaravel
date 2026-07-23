<?php

namespace App\Models;

use App\Contracts\BoardLevelModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model implements BoardLevelModel
{
    protected $fillable = [
        'label', 'year_start', 'year_end', 'is_active',
        'enrollment_open_date', 'enrollment_close_date', 'enrollment_window_open',
        'enrollment_grace_end',
        'examination_open_date', 'examination_close_date', 'examination_window_open',
        'examination_grace_end',
        'exam_dates_announced',
    ];

    protected $casts = [
        'is_active'               => 'boolean',
        'enrollment_window_open'  => 'boolean',
        'examination_window_open' => 'boolean',
        'exam_dates_announced'    => 'boolean',
        'enrollment_open_date'    => 'date',
        'enrollment_close_date'   => 'date',
        'enrollment_grace_end'    => 'datetime',
        'examination_open_date'   => 'date',
        'examination_close_date'  => 'date',
        'examination_grace_end'   => 'datetime',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getIsEnrollmentOpenAttribute(): bool
    {
        return (bool) $this->enrollment_window_open;
    }

    public function getIsExaminationOpenAttribute(): bool
    {
        return (bool) $this->examination_window_open;
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function studentAcademicRecords(): HasMany
    {
        return $this->hasMany(StudentAcademicRecord::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function examTimetables(): HasMany
    {
        return $this->hasMany(ExamTimetable::class);
    }

    public function windowOverrides(): HasMany
    {
        return $this->hasMany(WindowOverride::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    // ─── Grace Period Helpers ──────────────────────────────────────────────────

    public function hasEnrollmentGracePeriod(): bool
    {
        return ! is_null($this->enrollment_grace_end);
    }

    public function hasExaminationGracePeriod(): bool
    {
        return ! is_null($this->examination_grace_end);
    }

    // ─── Static Helpers ───────────────────────────────────────────────────────

    /**
     * CRITICAL: Active year MUST always be determined server-side.
     * Never trust any value from a browser request.
     */
    public static function current(): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();
    }
}
