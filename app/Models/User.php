<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes, HasRoles, LogsActivity;

    protected $fillable = [
        'username', 'name', 'email', 'password', 'role',
        'school_id', 'district_id', 'is_active',
        'last_login_at', 'failed_login_attempts', 'locked_until',
        'must_change_password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at'     => 'datetime',
        'password'              => 'hashed',
        'is_active'             => 'boolean',
        'last_login_at'         => 'datetime',
        'locked_until'          => 'datetime',
        'must_change_password'  => 'boolean',
        'failed_login_attempts' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['username', 'name', 'email', 'role', 'school_id', 'district_id', 'is_active'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "User {$eventName}");
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isDistrictAdmin(): bool
    {
        return $this->role === 'district_admin';
    }

    public function isSchoolAdmin(): bool
    {
        return $this->role === 'school_admin';
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function recordFailedLogin(): void
    {
        $attempts = $this->failed_login_attempts + 1;
        $data = ['failed_login_attempts' => $attempts];

        if ($attempts >= 5) {
            $data['locked_until'] = now()->addMinutes(30);
        }

        $this->update($data);
    }

    public function resetFailedLoginAttempts(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
        ]);
    }

    public function recordSuccessfulLogin(): void
    {
        $this->update(['last_login_at' => now()]);
        $this->resetFailedLoginAttempts();
    }

    /**
     * SECURITY: A school admin may only query records where school_id matches this value.
     */
    public function getSchoolScopeId(): ?int
    {
        return $this->school_id;
    }

    public function getDashboardRoute(): string
    {
        return match ($this->role) {
            'super_admin'   => 'superadmin.dashboard',
            'district_admin'=> 'district.dashboard',
            'school_admin'  => 'school.dashboard',
            'assistant_controller' => 'assistant.dashboard',
            default         => 'login',
        };
    }
}
