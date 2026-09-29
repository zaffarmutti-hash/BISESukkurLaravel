<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Database-backed system setting.
 *
 * Replaces storage/settings.json so that:
 *  - Settings survive server re-images
 *  - Every change is audit-logged via Spatie Activity Log
 *  - No multi-server JSON sync issues
 */
class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = ['key', 'value', 'value_type', 'label', 'description'];

    // ─── Static Helpers ───────────────────────────────────────────────────────

    /**
     * Get a setting value, cast to the correct PHP type.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->value_type);
    }

    /**
     * Update or create a setting by key.
     * Always use this method so the calling code can rely on audit logging.
     */
    public static function set(string $key, mixed $value): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
    }

    /**
     * Return all settings as a flat key=>value array (typed).
     */
    public static function allAsArray(): array
    {
        return static::all()->mapWithKeys(function ($setting) {
            return [$setting->key => static::castValue($setting->value, $setting->value_type)];
        })->toArray();
    }

    // ─── Casting ──────────────────────────────────────────────────────────────

    private static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool) (int) $value,
            'integer' => (int) $value,
            'float'   => (float) $value,
            default   => (string) ($value ?? ''),
        };
    }
}
