<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Tests;

use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Privatus\FilamentAnalytics\PrivatusAnalyticsPlugin;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->middleware([EncryptCookies::class, StartSession::class, ShareErrorsFromSession::class])
            ->plugin(PrivatusAnalyticsPlugin::make()->trackPanel());
    }
}
