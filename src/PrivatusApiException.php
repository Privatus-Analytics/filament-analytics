<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics;

use RuntimeException;

/** The API could not be reached or said no. The message is safe to show in the panel. */
final class PrivatusApiException extends RuntimeException
{
}
