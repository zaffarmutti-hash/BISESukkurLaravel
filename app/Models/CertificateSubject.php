<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateSubject extends Model
{
    protected $fillable = [
        'certificate_id', 'result_id',
        'subject_name', 'subject_code',
        'total_marks', 'marks_obtained', 'grade',
    ];

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class);
    }
}
