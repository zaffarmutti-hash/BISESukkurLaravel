<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolException extends Model
{
    protected $fillable = [
        'school_id', 'granted_by', 'academic_year_id',
        'exception_type', 'reason', 'expires_at', 'is_active',
        'student_id', 'revoked_at', 'revoked_by',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function isCurrentlyActive(): bool
    {
        return $this->is_active
            && is_null($this->revoked_at)
            && (is_null($this->expires_at) || $this->expires_at->isFuture());
    }
}
