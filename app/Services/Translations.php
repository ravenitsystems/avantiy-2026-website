<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Translations
{
    public const CACHE_PREFIX = 'translations.';

    /**
     * Get all translation key/value pairs for a locale.
     *
     * @return array<string, string>
     */
    public static function getForLocale(string $locale): array
    {
        $cacheKey = self::CACHE_PREFIX . $locale;

        return Cache::rememberForever($cacheKey, static function () use ($locale): array {
            $localeRow = DB::table('locales')
                ->where('code', $locale)
                ->where('enabled', true)
                ->first();

            if (! $localeRow) {
                return [];
            }

            $rows = DB::table('translations')
                ->where('locale_id', $localeRow->id)
                ->orderBy('key')
                ->get(['key', 'value']);

            $result = [];
            foreach ($rows as $row) {
                $key = (string) $row->key;
                $value = (string) $row->value;
                $result[$key] = $value;
            }

            return $result;
        });
    }

    public static function forgetCache(?string $locale = null): void
    {
        if ($locale !== null) {
            Cache::forget(self::CACHE_PREFIX . $locale);

            return;
        }

        // Clear all cached locales (best-effort; relies on cache store supporting tags or a known list)
        // Fallback: clear the entire cache prefix by iterating known locales.
        $locales = DB::table('locales')->pluck('code');
        foreach ($locales as $code) {
            Cache::forget(self::CACHE_PREFIX . $code);
        }
    }

    public static function warmCache(?string $locale = null): void
    {
        if ($locale !== null) {
            self::forgetCache($locale);
            self::getForLocale($locale);

            return;
        }

        $locales = DB::table('locales')
            ->where('enabled', true)
            ->pluck('code');

        foreach ($locales as $code) {
            self::forgetCache($code);
            self::getForLocale($code);
        }
    }
}

