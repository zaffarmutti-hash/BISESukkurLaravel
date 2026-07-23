<?php

namespace App\Models;

use App\Contracts\BoardLevelModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model implements BoardLevelModel
{
    protected $fillable = [
        'academic_year_id', 'class_level', 'student_type', 'fee_type',
        'amount_paisas', 'late_fee_surcharge_paisas', 'is_active',
    ];

    protected $casts = [
        'is_active'                 => 'boolean',
        'amount_paisas'             => 'integer',
        'late_fee_surcharge_paisas' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * Return the amount in full PKR for display purposes.
     * Never use this for calculations — use amount_paisas directly.
     */
    public function getAmountRupeesAttribute(): string
    {
        return number_format($this->amount_paisas / 100, 2);
    }

    /**
     * Total amount including late fee surcharge (for grace period invoices).
     */
    public function getGraceTotalPaisasAttribute(): int
    {
        return $this->amount_paisas + ($this->late_fee_surcharge_paisas ?? 0);
    }

    public function getLateFeeRupeesAttribute(): string
    {
        return number_format(($this->late_fee_surcharge_paisas ?? 0) / 100, 2);
    }
}
