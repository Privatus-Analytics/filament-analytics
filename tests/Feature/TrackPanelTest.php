<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Tests\Feature;

use Privatus\FilamentAnalytics\PrivatusAnalyticsPlugin;
use Privatus\FilamentAnalytics\Tests\TestCase;

final class TrackPanelTest extends TestCase
{
    public function testLoginPageGetsTheTracker(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('<script defer src="https://privatusstats.com/js/pa.js" data-site="pa_TEST123456" data-modules="engage,auto,vitals,clicks,ask"></script>', false);
    }

    public function testBadSiteIdPrintsNothing(): void
    {
        config(['privatus-filament.site_id' => 'pa_x" onload="alert(1)']);

        $this->assertSame('', PrivatusAnalyticsPlugin::make()->trackerTag());
    }

    public function testOwnSiteIdAndModules(): void
    {
        $tag = PrivatusAnalyticsPlugin::make()->trackPanel(siteId: 'pa_PANEL1')->modules(['engage', 'nope'])->trackerTag();

        $this->assertSame('<script defer src="https://privatusstats.com/js/pa.js" data-site="pa_PANEL1" data-modules="engage"></script>', $tag);
    }
}
