<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Key/value settings, read on nearly every request that touches the
 * Google Sheets export, so reads go through the cache and writes clear
 * it.
 *
 * get() deliberately swallows database errors: a missing settings table
 * (fresh checkout before migrating, or a test database built without
 * it) must fall back to config/env rather than break the page.
 */
class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = ['key', 'value'];

    private const CACHE_PREFIX = 'setting:';

    public static function get(string $key, $default = null)
    {
        try {
            $value = Cache::rememberForever(self::CACHE_PREFIX . $key, function () use ($key) {
                // Wrapped in an array so a genuine null is cached as a
                // value rather than re-queried on every call.
                return ['v' => static::query()->where('key', $key)->value('value')];
            });
        } catch (Throwable $e) {
            return $default;
        }

        $value = $value['v'] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget(self::CACHE_PREFIX . $key);
    }

    public static function forgetKey(string $key): void
    {
        static::query()->where('key', $key)->delete();

        Cache::forget(self::CACHE_PREFIX . $key);
    }
}
