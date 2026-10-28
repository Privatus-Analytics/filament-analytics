<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Widgets;

/** The most viewed pages. */
class TopPages extends TopList
{
    protected static ?int $sort = -1;

    protected function dimension(): string
    {
        return 'page';
    }

    protected function metric(): string
    {
        return 'pageviews';
    }

    protected function heading(): string
    {
        return (string) __('privatus-filament::widgets.top.pages');
    }

    protected function columnLabel(): string
    {
        return (string) __('privatus-filament::widgets.top.page');
    }
}
