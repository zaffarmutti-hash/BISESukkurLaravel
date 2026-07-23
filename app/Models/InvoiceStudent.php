<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceStudent extends Model
{
    protected $table = 'invoice_students';

    protected $fillable = [
        'invoice_id', 'student_id', 'student_academic_record_id',
        'fee_structure_id', 'amount_paisas', 'late_fee_surcharge_paisas',
    ];

    protected $casts = [
        'amount_paisas'             => 'integer',
        'late_fee_surcharge_paisas' => 'integer',
    ];

    // Alias for backward compatibility
    public function getChallanIdAttribute(): int
    {
        return $this->invoice_id;
    }

    public function setChallanIdAttribute(int $value): void
    {
        $this->invoice_id = $value;
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    // Alias for backward compatibility
    public function challan(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function studentAcademicRecord(): BelongsTo
    {
        return $this->belongsTo(StudentAcademicRecord::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }
}
