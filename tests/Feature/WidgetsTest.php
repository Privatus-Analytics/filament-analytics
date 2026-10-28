<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Tests\Feature;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Privatus\FilamentAnalytics\PrivatusAnalyticsPlugin;
use Privatus\FilamentAnalytics\Tests\Fixtures;
use Privatus\FilamentAnalytics\Tests\TestCase;
use Privatus\FilamentAnalytics\Widgets\TopPages;
use Privatus\FilamentAnalytics\Widgets\TopSources;
use Privatus\FilamentAnalytics\Widgets\VisitorsChart;
use Privatus\FilamentAnalytics\Widgets\VisitorsOverview;

final class WidgetsTest extends TestCase
{
    use Fixtures;

    public function testPluginRegistersTheWidgets(): void
    {
        $widgets = Filament::getPanel('admin')->getWidgets();

        foreach (PrivatusAnalyticsPlugin::widgetClasses() as $widget) {
            $this->assertContains($widget, $widgets);
        }
    }

    public function testOverviewShowsTheHeadlineNumbers(): void
    {
        $this->fakeApi();

        Livewire::test(VisitorsOverview::class)
            ->assertSee('Website traffic, Last 30 days')
            ->assertSee('12.3k')
            ->assertSee('40.2k')
            ->assertSee('41.3%')
            ->assertSee('1m 23s')
            ->assertSee('+12.5% on the period before')
            ->assertSee('Nothing to compare yet');
    }

    public function testOverviewExplainsMissingSettings(): void
    {
        Http::fake();
        config(['privatus-filament.api_token' => '']);

        Livewire::test(VisitorsOverview::class)
            ->assertSee('Add PRIVATUS_SITE_ID and PRIVATUS_API_TOKEN');
        Http::assertNothingSent();
    }

    public function testChartBuildsTwoLines(): void
    {
        $this->fakeApi();

        $component = Livewire::test(VisitorsChart::class)->assertSet('filter', '30d');
        $data = (fn () => $this->getData())->call($component->instance());

        $this->assertSame(['Oct 27', 'Oct 28'], $data['labels']);
        $this->assertSame([400, 450], $data['datasets'][0]['data']);
        $this->assertSame([1200, 1300], $data['datasets'][1]['data']);
    }

    public function testChartFilterChangesThePeriod(): void
    {
        $this->fakeApi();

        Livewire::test(VisitorsChart::class)->set('filter', '7d');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'timeseries') && $request['period'] === '7d');
    }

    public function testChartIgnoresAnUnknownPeriod(): void
    {
        $this->fakeApi();

        Livewire::test(VisitorsChart::class)->set('filter', 'all; drop');

        Http::assertNotSent(fn ($request) => $request['period'] === 'all; drop');
    }

    public function testTopPagesListsAndEscapesRows(): void
    {
        $this->fakeApi();

        Livewire::test(TopPages::class)
            ->assertSee('Top pages')
            ->assertSee('/pricing')
            ->assertSee('1,620')
            ->assertSee('width: 40.5%', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function testTopSourcesNamesEmptyReferrers(): void
    {
        $this->fakeApi();

        Livewire::test(TopSources::class)
            ->assertSee('Google')
            ->assertSee('6,800')
            ->assertSee('Direct or unknown');
    }

    public function testTopListShowsApiErrors(): void
    {
        Http::fake(['*' => Http::response([], 500)]);

        Livewire::test(TopPages::class)->assertSee('Privatus Analytics sent an error (500)');
    }

    public function testChartLabelsKeepTheApiOffset(): void
    {
        $widget = new VisitorsChart();
        $label = fn (string $date, string $interval) => (fn () => $this->label($date, $interval))->call($widget);

        $this->assertSame('Oct 22', $label('2026-10-22T00:00:00+08:00', 'day'));
        $this->assertSame('09:00', $label('2026-10-22T09:00:00+08:00', 'hour'));
        $this->assertSame('Oct 2026', $label('2026-10-01T00:00:00Z', 'month'));
        $this->assertSame('', $label('not a date', 'day'));
    }
}
