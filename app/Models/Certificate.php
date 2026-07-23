<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Certificate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id', 'academic_year_id', 'level',
        'verification_token', 'verification_url',
        'total_percentage', 'overall_grade', 'division',
        'document_path', 'issued_by', 'issued_at', 'is_issued',
    ];

    protected $casts = [
        'is_issued'        => 'boolean',
        'issued_at'        => 'datetime',
        'total_percentage' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(CertificateSubject::class);
    }
}
