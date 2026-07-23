<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id', 'gr_number', 'admission_date', 'full_name', 'surname', 'father_name',
        'father_cnic', 'cnic', 'b_form', 'date_of_birth', 'marks_of_identification',
        'gender', 'nationality', 'religion', 'medium_of_instruction', 'phone',
        'guardian_phone', 'guardian_name', 'guardian_cnic', 'address', 'postal_code',
        'remarks', 'photo_path', 'enrollment_number', 'enrollment_number_issued_at',
        'is_active', 'is_expired', 'expired_at',
    ];

    protected $casts = [
        'admission_date'              => 'date',
        'date_of_birth'               => 'date',
        'enrollment_number_issued_at' => 'datetime',
        'is_active'                   => 'boolean',
        'is_expired'                  => 'boolean',
        'expired_at'                  => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicRecords(): HasMany
    {
        return $this->hasMany(StudentAcademicRecord::class);
    }

    public function currentAcademicRecord(): HasOne
    {
        return $this->hasOne(StudentAcademicRecord::class)
            ->whereHas('academicYear', fn ($q) => $q
                ->where('is_active', true));
    }

    public function invoiceStudents(): HasMany
    {
        return $this->hasMany(InvoiceStudent::class, 'student_id');
    }

    // Alias for backward compatibility
    public function challanStudents(): HasMany
    {
        return $this->hasMany(InvoiceStudent::class, 'student_id');
    }

    public function examForms(): HasMany
    {
        return $this->hasMany(ExamForm::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeWithEnrollmentNumber($query)
    {
        return $query->whereNotNull('enrollment_number');
    }

    public function scopeWithoutEnrollmentNumber($query)
    {
        return $query->whereNull('enrollment_number');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_expired', false);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function hasEnrollmentNumber(): bool
    {
        return ! is_null($this->enrollment_number);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path
            ? asset('storage/' . $this->photo_path)
            : null;
    }
}
