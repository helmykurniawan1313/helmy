<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Settings saved in the admin. Each one overrides a config value at boot,
 * so anything left empty falls back to .env.
 */
class Settings
{
    /** setting key => config path; secrets are encrypted at rest */
    public const MAP = [
        'gemini_key' => 'services.gemini.key',
        'gemini_model' => 'services.gemini.model',
        'groq_key' => 'services.groq.key',
        'groq_model' => 'services.groq.model',
        'openrouter_key' => 'services.openrouter.key',
        'openrouter_model' => 'services.openrouter.model',
        'assistant_providers' => 'services.assistant.providers',
    ];

    public const SECRETS = ['gemini_key', 'groq_key', 'openrouter_key'];

    private const CACHE_KEY = 'app.settings';

    /** @var array<string, mixed> */
    private static array $fileDefaults = [];

    /** Push saved settings into config(). Called once per request. */
    public static function apply(): void
    {
        foreach (static::MAP as $key => $path) {
            static::$fileDefaults[$key] = config($path);
        }

        foreach (static::all() as $key => $value) {
            if (isset(static::MAP[$key]) && $value !== null && $value !== '') {
                config([static::MAP[$key] => $value]);
            }
        }
    }

    /** @return array<string, string|null> decrypted values */
    public static function all(): array
    {
        try {
            $rows = Cache::rememberForever(static::CACHE_KEY, fn () => DB::table('settings')->pluck('value', 'key')->all());
        } catch (Throwable) {
            return []; // not migrated yet
        }

        foreach (static::SECRETS as $key) {
            if (! empty($rows[$key])) {
                try {
                    $rows[$key] = Crypt::decryptString($rows[$key]);
                } catch (Throwable) {
                    $rows[$key] = null; // APP_KEY changed; key must be re-entered
                }
            }
        }

        return $rows;
    }

    public static function put(string $key, ?string $value): void
    {
        if ($value === null || $value === '') {
            DB::table('settings')->where('key', $key)->delete();
        } else {
            $stored = in_array($key, static::SECRETS, true) ? Crypt::encryptString($value) : $value;
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $stored, 'updated_at' => now(), 'created_at' => now()]);
        }

        Cache::forget(static::CACHE_KEY);
    }

    /** Where the effective value comes from: "admin", "env" or null (not set). */
    public static function source(string $key): ?string
    {
        if (! empty(static::all()[$key] ?? null)) {
            return 'admin';
        }

        return ! empty(static::$fileDefaults[$key] ?? null) ? 'env' : null;
    }

    /** The .env / config values as they were before admin overrides. */
    public static function fileDefault(string $key): ?string
    {
        return static::$fileDefaults[$key] ?? null;
    }

    /** "AIza…x9Qk" style hint so the admin can recognise a key without exposing it. */
    public static function mask(?string $secret): ?string
    {
        if (! $secret) {
            return null;
        }

        return strlen($secret) <= 10 ? str_repeat('•', 8) : substr($secret, 0, 4).str_repeat('•', 8).substr($secret, -4);
    }
}
