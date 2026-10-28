<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Widgets;

/** The sites and searches that send the most visitors. */
class TopSources extends TopList
{
    protected static ?int $sort = -1;

    protected function dimension(): string
    {
        return 'referrer';
    }

    protected function metric(): string
    {
        return 'visitors';
    }

    protected function heading(): string
    {
        return (string) __('privatus-filament::widgets.top.sources');
    }

    protected function columnLabel(): string
    {
        return (string) __('privatus-filament::widgets.top.source');
    }
}
