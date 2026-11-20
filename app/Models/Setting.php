<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Platform-wide key/value settings, edited by Super Admin only.
 * Values are stored as JSON so booleans, arrays and scalars all round-trip.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    /** Read a setting, falling back to a default. */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        return $setting?->value['data'] ?? $default;
    }

    /** Write (create or update) a setting. */
    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => ['data' => $value]]);
    }
}
