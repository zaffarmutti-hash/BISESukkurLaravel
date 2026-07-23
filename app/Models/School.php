<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class School extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'district_id', 'tehsil_id', 'name', 'username', 'type', 'gender',
        'principal_name', 'address', 'phone', 'email', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['code'];

    // ─── Accessors & Mutators ──────────────────────────────────────────────────

    public function getCodeAttribute(): ?string
    {
        return $this->username;
    }

    public function setCodeAttribute(?string $value): void
    {
        $this->username = $value;
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function tehsil(): BelongsTo
    {
        return $this->belongsTo(Tehsil::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function adminUser(): HasOne
    {
        return $this->hasOne(User::class)->where('role', 'school_admin');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(SchoolException::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function hasActiveException(string $type): bool
    {
        return $this->exceptions()
            ->where('exception_type', $type)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }
}
