<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Support;

/** The date ranges the widgets offer. */
final class Periods
{
    /** @var list<string> */
    public const ALL = ['today', 'yesterday', '7d', '30d', 'mtd', 'last_month', '90d', '12mo'];

    /** @return array<string, string> period => label */
    public static function options(): array
    {
        $options = [];
        foreach (self::ALL as $period) {
            $options[$period] = (string) __('privatus-filament::widgets.periods.' . $period);
        }

        return $options;
    }

    public static function clean(mixed $value): string
    {
        if (is_string($value) && in_array($value, self::ALL, true)) {
            return $value;
        }
        $default = config('privatus-filament.period');

        return is_string($default) && in_array($default, self::ALL, true) ? $default : '30d';
    }
}
