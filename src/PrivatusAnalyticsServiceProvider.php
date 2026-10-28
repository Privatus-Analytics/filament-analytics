<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics;

use Illuminate\Support\ServiceProvider;

class PrivatusAnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/privatus-filament.php', 'privatus-filament');
        $this->app->bind(PrivatusClient::class, fn (): PrivatusClient => PrivatusClient::fromConfig());
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'privatus-filament');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'privatus-filament');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/privatus-filament.php' => config_path('privatus-filament.php'),
            ], 'privatus-filament-config');
            $this->publishes([
                __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/privatus-filament'),
            ], 'privatus-filament-translations');
        }
    }
}
