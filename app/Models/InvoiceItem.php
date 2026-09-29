<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $table = 'invoice_students';

    protected $fillable = [
        'invoice_id',
        'student_id',
        'student_academic_record_id',
        'fee_structure_id',
        'amount_paisas',
        'late_fee_surcharge_paisas',
        'is_included',
    ];

    protected $casts = [
        'amount_paisas'             => 'integer',
        'late_fee_surcharge_paisas' => 'integer',
        'is_included'               => 'boolean',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function studentAcademicRecord(): BelongsTo
    {
        return $this->belongsTo(StudentAcademicRecord::class, 'student_academic_record_id');
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }
}
