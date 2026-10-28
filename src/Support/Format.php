<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Support;

/** Turns API numbers into short labels. */
final class Format
{
    public static function number(mixed $value): string
    {
        if (!is_numeric($value)) {
            return '0';
        }
        $value = (float) $value;
        if ($value >= 1_000_000) {
            return self::trim(round($value / 1_000_000, 1)) . 'M';
        }
        if ($value >= 10_000) {
            return self::trim(round($value / 1_000, 1)) . 'k';
        }

        return number_format($value, fmod($value, 1.0) === 0.0 ? 0 : 1);
    }

    public static function percent(mixed $value): string
    {
        return (is_numeric($value) ? self::trim(round((float) $value, 1)) : '0') . '%';
    }

    /** Milliseconds as 45s, 2m 05s or 1h 03m. */
    public static function duration(mixed $milliseconds): string
    {
        $seconds = is_numeric($milliseconds) ? (int) round(((float) $milliseconds) / 1000) : 0;
        if ($seconds < 60) {
            return $seconds . 's';
        }
        if ($seconds < 3600) {
            return sprintf('%dm %02ds', intdiv($seconds, 60), $seconds % 60);
        }

        return sprintf('%dh %02dm', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
    }

    private static function trim(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
    }
}
