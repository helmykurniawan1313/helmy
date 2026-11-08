<?php

namespace App\Support;

use App\Models\Content;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * The portfolio data for one locale: the defaults from config/portfolio.php
 * and lang/{locale}/portfolio.php, with anything saved in the admin on top.
 */
class PortfolioContent
{
    public const LOCALES = ['en', 'id'];

    public static function for(string $locale): array
    {
        app()->setLocale($locale);
        $data = static::defaults($locale);

        foreach (static::overrides($locale) as $values) {
            foreach ($values as $path => $value) {
                Arr::set($data, $path, $value);
            }
        }

        return $data;
    }

    /** The file-based data, ignoring admin edits. */
    public static function defaults(string $locale): array
    {
        return config('portfolio') + trans('portfolio', [], $locale);
    }

    public static function flush(): void
    {
        foreach (static::LOCALES as $locale) {
            Cache::forget(static::cacheKey($locale));
        }
    }

    /** @return array<int, array<string, mixed>> neutral rows first, then the locale's own */
    private static function overrides(string $locale): array
    {
        try {
            return Cache::rememberForever(static::cacheKey($locale), fn () => Content::query()
                ->whereIn('locale', ['all', $locale])
                ->orderByRaw("locale = 'all' desc")
                ->pluck('value')
                ->all());
        } catch (Throwable) {
            // Table not migrated yet: the site still works from the files
            return [];
        }
    }

    private static function cacheKey(string $locale): string
    {
        return "portfolio.overrides.{$locale}";
    }
}
