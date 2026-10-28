<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Widgets\Concerns;

use Privatus\FilamentAnalytics\PrivatusClient;

trait UsesPrivatus
{
    protected function privatus(): PrivatusClient
    {
        return app(PrivatusClient::class);
    }
}
