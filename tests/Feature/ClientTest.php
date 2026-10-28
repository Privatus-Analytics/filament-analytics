<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Privatus\FilamentAnalytics\PrivatusApiException;
use Privatus\FilamentAnalytics\PrivatusClient;
use Privatus\FilamentAnalytics\Tests\Fixtures;
use Privatus\FilamentAnalytics\Tests\TestCase;

final class ClientTest extends TestCase
{
    use Fixtures;

    public function testAggregateSendsTokenAndPeriod(): void
    {
        $this->fakeApi();

        $data = app(PrivatusClient::class)->aggregate('7d');

        $this->assertSame(12345, $data['metrics']['visitors']);
        Http::assertSent(function (Request $request): bool {
            return $request->hasHeader('Authorization', 'Bearer ' . self::TOKEN)
                && str_starts_with($request->url(), 'https://api.test/sites/pa_TEST123456/stats/aggregate.json?')
                && $request['period'] === '7d'
                && $request['compare'] === 'previous_period';
        });
    }

    public function testResultsAreCached(): void
    {
        $this->fakeApi();
        $client = app(PrivatusClient::class);

        $client->breakdown('page', '30d', 5);
        $client->breakdown('page', '30d', 5);

        Http::assertSentCount(1);
    }

    public function testMissingSettingsNeverCallTheApi(): void
    {
        Http::fake();
        config(['privatus-filament.api_token' => null]);

        try {
            app(PrivatusClient::class)->aggregate('7d');
            $this->fail('Expected an exception');
        } catch (PrivatusApiException $e) {
            $this->assertStringContainsString('PRIVATUS_API_TOKEN', $e->getMessage());
        }
        Http::assertNothingSent();
    }

    public function testBadSiteIdNeverCallsTheApi(): void
    {
        Http::fake();
        config(['privatus-filament.site_id' => 'pa_x/../../admin']);

        $this->expectException(PrivatusApiException::class);
        try {
            app(PrivatusClient::class)->timeseries('7d');
        } finally {
            Http::assertNothingSent();
        }
    }

    public function testRejectedTokenGivesAClearMessage(): void
    {
        Http::fake(['*' => Http::response(['error' => 'unauthorized'], 401)]);

        $this->expectExceptionMessage('did not accept the API token');
        app(PrivatusClient::class)->aggregate('7d');
    }

    public function testUnknownSiteGivesAClearMessage(): void
    {
        Http::fake(['*' => Http::response([], 404)]);

        $this->expectExceptionMessage('could not find this site');
        app(PrivatusClient::class)->aggregate('7d');
    }

    public function testOddDimensionIsRefused(): void
    {
        Http::fake();

        $this->expectException(PrivatusApiException::class);
        app(PrivatusClient::class)->breakdown('../x', '7d');
    }

    public function testDashboardUrl(): void
    {
        $this->assertSame('https://api.test/sites/pa_TEST123456', app(PrivatusClient::class)->dashboardUrl());
    }
}
