<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Invoice extends Model
{
    use SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'school_id', 'academic_year_id', 'invoice_number', 'invoice_type',
        'total_amount_paisas', 'late_fee_surcharge_paisas', 'fee_phase',
        'student_count', 'status',
        'submitted_at', 'bank_name', 'bank_branch', 'bank_reference',
        'payment_date', 'deposit_slip_path',
        'approved_by', 'approved_at', 'rejection_reason',
    ];

    protected $casts = [
        'submitted_at'                => 'datetime',
        'approved_at'                 => 'datetime',
        'payment_date'                => 'date',
        'total_amount_paisas'         => 'integer',
        'late_fee_surcharge_paisas'   => 'integer',
        'student_count'               => 'integer',
    ];

    protected $appends = ['challan_number', 'challan_type', 'amount'];

    // ─── Backward Compatibility Aliases ────────────────────────────────────────

    public function getChallanNumberAttribute(): ?string
    {
        return $this->invoice_number;
    }

    public function setChallanNumberAttribute(?string $value): void
    {
        $this->invoice_number = $value;
    }

    public function getChallanTypeAttribute(): ?string
    {
        return $this->invoice_type;
    }

    public function setChallanTypeAttribute(?string $value): void
    {
        $this->invoice_type = $value;
    }

    public function getAmountAttribute(): float
    {
        return (float) ($this->total_amount_paisas / 100);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function invoiceStudents(): HasMany
    {
        return $this->hasMany(InvoiceStudent::class, 'invoice_id');
    }

    // Alias for backward compatibility
    public function challanStudents(): HasMany
    {
        return $this->hasMany(InvoiceStudent::class, 'invoice_id');
    }

    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            InvoiceStudent::class,
            'invoice_id', // Foreign key on invoice_students table...
            'id',         // Foreign key on students table...
            'id',         // Local key on invoices table...
            'student_id'  // Local key on invoice_students table...
        );
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePaidNotVerified($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'confirmed'); // verified = confirmed in new model
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getTotalAmountRupeesAttribute(): string
    {
        return number_format($this->total_amount_paisas / 100, 2);
    }

    public function isPending(): bool
    {
        return $this->status === 'submitted';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
}
