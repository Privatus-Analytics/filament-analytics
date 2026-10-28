<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Privatus\Analytics\Config;
use Privatus\Analytics\Tag;
use Privatus\FilamentAnalytics\Support\SiteId;
use Privatus\FilamentAnalytics\Widgets\TopPages;
use Privatus\FilamentAnalytics\Widgets\TopSources;
use Privatus\FilamentAnalytics\Widgets\VisitorsChart;
use Privatus\FilamentAnalytics\Widgets\VisitorsOverview;

/**
 * Adds the Privatus Analytics widgets to a Filament panel.
 *
 *   $panel->plugin(PrivatusAnalyticsPlugin::make());
 */
class PrivatusAnalyticsPlugin implements Plugin
{
    /** @var list<string> */
    public const DEFAULT_MODULES = ['engage', 'auto', 'vitals', 'clicks', 'ask'];

    protected bool $widgets = true;

    protected bool $trackPanel = false;

    protected ?string $trackingSiteId = null;

    /** @var list<string> */
    protected array $modules = self::DEFAULT_MODULES;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static */
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'privatus-analytics';
    }

    /** Turn the dashboard widgets off, for example to place them yourself. */
    public function widgets(bool $condition = true): static
    {
        $this->widgets = $condition;

        return $this;
    }

    /**
     * Also count visits to the panel itself with the tracker. Off by default.
     * Pass a site id to count the panel as its own site.
     */
    public function trackPanel(bool $condition = true, ?string $siteId = null): static
    {
        $this->trackPanel = $condition;
        $this->trackingSiteId = $siteId;

        return $this;
    }

    /** @param list<string> $modules Tracker modules for trackPanel(). */
    public function modules(array $modules): static
    {
        $this->modules = array_values(array_intersect($modules, Config::MODULES));

        return $this;
    }

    /** @return list<class-string> */
    public static function widgetClasses(): array
    {
        return [VisitorsOverview::class, VisitorsChart::class, TopPages::class, TopSources::class];
    }

    public function register(Panel $panel): void
    {
        if ($this->widgets) {
            $panel->widgets(self::widgetClasses());
        }

        if ($this->trackPanel) {
            $panel->renderHook(PanelsRenderHook::HEAD_END, fn (): string => $this->trackerTag());
        }
    }

    public function boot(Panel $panel): void
    {
    }

    /** The tracker script tag for the panel, or an empty string when the site id is not valid. */
    public function trackerTag(): string
    {
        $siteId = SiteId::clean($this->trackingSiteId ?? (config('privatus-filament.site_id') ?: config('privatus.site_id')));
        if ($siteId === null) {
            return '';
        }

        return (new Tag(new Config(siteId: $siteId, modules: $this->modules, enabled: true)))->render();
    }
}
