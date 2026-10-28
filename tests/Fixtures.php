<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Tests;

use Illuminate\Support\Facades\Http;

/** API responses in the shape https://privatusanalytics.com/openapi.json describes. */
trait Fixtures
{
    protected function fakeApi(): void
    {
        Http::fake([
            'api.test/sites/' . TestCase::SITE_ID . '/stats/aggregate.json*' => Http::response(['data' => [
                'period' => ['from' => '2026-09-29', 'to' => '2026-10-28'],
                'metrics' => ['visitors' => 12345, 'visits' => 15000, 'pageviews' => 40210, 'views_per_visit' => 2.7, 'bounce_rate' => 41.25, 'visit_duration' => 83000, 'revenue' => 0],
                'change' => ['visitors' => 12.5, 'pageviews' => -3, 'bounce_rate' => -2.1, 'visit_duration' => null],
            ], 'meta' => []]),
            'api.test/sites/' . TestCase::SITE_ID . '/stats/timeseries.json*' => Http::response(['data' => [
                'interval' => 'day',
                'series' => [
                    ['date' => '2026-10-27T00:00:00Z', 'visitors' => 400, 'pageviews' => 1200],
                    ['date' => '2026-10-28T00:00:00Z', 'visitors' => 450, 'pageviews' => 1300],
                ],
            ]]),
            'api.test/sites/' . TestCase::SITE_ID . '/breakdown/page.json*' => Http::response(['data' => [
                'dimension' => 'page', 'has_more' => false,
                'rows' => [
                    ['value' => '/pricing', 'share' => 40.5, 'pageviews' => 1620],
                    ['value' => '/<script>alert(1)</script>', 'share' => 2, 'pageviews' => 9],
                ],
            ]]),
            'api.test/sites/' . TestCase::SITE_ID . '/breakdown/referrer.json*' => Http::response(['data' => [
                'dimension' => 'referrer', 'has_more' => false,
                'rows' => [
                    ['value' => 'Google', 'share' => 55, 'visitors' => 6800],
                    ['value' => '', 'share' => 30, 'visitors' => 3700],
                ],
            ]]),
        ]);
    }
}
