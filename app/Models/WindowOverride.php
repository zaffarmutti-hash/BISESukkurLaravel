<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * District-level or school-level window phase override.
 *
 * When active, fully replaces the global AcademicYear dates for one
 * scope (a specific district or school) and one window type.
 * No partial merging — the override is self-contained.
 */
class WindowOverride extends Model
{
    protected $fillable = [
        'academic_year_id', 'scope_type', 'scope_id', 'window_type',
        'normal_start', 'normal_end', 'grace_end', 'is_active',
    ];

    protected $casts = [
        'normal_start' => 'datetime',
        'normal_end'   => 'datetime',
        'grace_end'    => 'datetime',
        'is_active'    => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // ─── Polymorphic Scope Resolution ─────────────────────────────────────────

    /**
     * Resolve the related district or school model.
     */
    public function scopeEntity()
    {
        return $this->scope_type === 'district'
            ? District::find($this->scope_id)
            : School::find($this->scope_id);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSchool($query, int $schoolId, string $windowType)
    {
        return $query->where('scope_type', 'school')
            ->where('scope_id', $schoolId)
            ->where('window_type', $windowType)
            ->where('is_active', true);
    }

    public function scopeForDistrict($query, int $districtId, string $windowType)
    {
        return $query->where('scope_type', 'district')
            ->where('scope_id', $districtId)
            ->where('window_type', $windowType)
            ->where('is_active', true);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function hasGracePeriod(): bool
    {
        return ! is_null($this->grace_end);
    }
}
