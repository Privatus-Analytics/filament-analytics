<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Support;

/** Checks site ids such as pa_7Q2K9XH3AB. */
final class SiteId
{
    public const PATTERN = '/^pa_[A-Za-z0-9]+\z/';

    public static function isValid(mixed $value): bool
    {
        return is_string($value) && preg_match(self::PATTERN, $value) === 1;
    }

    /** The trimmed site id, or null when it is missing or not valid. */
    public static function clean(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return self::isValid($value) ? $value : null;
    }
}
