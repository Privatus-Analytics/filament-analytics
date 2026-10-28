<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Privatus\FilamentAnalytics\Support\SiteId;

/**
 * Reads stats from the Privatus Analytics JSON API (https://privatusanalytics.com/docs/api).
 * It only reads. Events always come from the tracker in the browser.
 */
class PrivatusClient
{
    public function __construct(
        private readonly ?string $siteId,
        private readonly ?string $token,
        private readonly string $baseUrl = 'https://privatusanalytics.com',
        private readonly int $cacheSeconds = 300,
        private readonly int $timeout = 10,
    ) {
    }

    public static function fromConfig(): self
    {
        $siteId = config('privatus-filament.site_id') ?: config('privatus.site_id');
        $token = config('privatus-filament.api_token');
        $url = config('privatus-filament.api_url');

        return new self(
            siteId: is_string($siteId) ? $siteId : null,
            token: is_string($token) ? trim($token) : null,
            baseUrl: is_string($url) && $url !== '' ? $url : 'https://privatusanalytics.com',
            cacheSeconds: (int) config('privatus-filament.cache_seconds', 300),
            timeout: (int) config('privatus-filament.timeout', 10),
        );
    }

    public function siteId(): ?string
    {
        return SiteId::clean($this->siteId);
    }

    /** Whether there is a valid site id and a token. */
    public function isConfigured(): bool
    {
        return $this->siteId() !== null && $this->token !== null && $this->token !== '';
    }

    /**
     * Headline numbers with the change against the period before.
     *
     * @return array<string, mixed>
     */
    public function aggregate(string $period): array
    {
        return $this->get('stats/aggregate', ['period' => $period, 'compare' => 'previous_period']);
    }

    /**
     * Visitors and pageviews per day (or hour, week or month).
     *
     * @return array<string, mixed>
     */
    public function timeseries(string $period): array
    {
        return $this->get('stats/timeseries', ['period' => $period]);
    }

    /**
     * Top values of one dimension, such as page or referrer.
     *
     * @return array<string, mixed>
     */
    public function breakdown(string $dimension, string $period, int $limit = 10): array
    {
        if (preg_match('/^[a-z_]+$/', $dimension) !== 1) {
            throw new PrivatusApiException('That breakdown is not supported.');
        }

        return $this->get('breakdown/' . $dimension, ['period' => $period, 'limit' => max(1, min($limit, 100))]);
    }

    /** A link to this site's dashboard on privatusanalytics.com. */
    public function dashboardUrl(): string
    {
        $siteId = $this->siteId();

        return rtrim($this->baseUrl, '/') . ($siteId === null ? '' : '/sites/' . rawurlencode($siteId));
    }

    /**
     * @param array<string, scalar> $query
     *
     * @return array<string, mixed>
     */
    protected function get(string $path, array $query): array
    {
        $siteId = $this->siteId();
        if ($siteId === null || !$this->isConfigured()) {
            throw new PrivatusApiException((string) __('privatus-filament::widgets.errors.not_configured'));
        }

        $url = rtrim($this->baseUrl, '/') . '/sites/' . rawurlencode($siteId) . '/' . $path . '.json';
        $key = 'privatus-filament:' . sha1($url . '?' . http_build_query($query) . '|' . sha1((string) $this->token));

        if ($this->cacheSeconds <= 0) {
            return $this->fetch($url, $query);
        }

        /** @var array<string, mixed> */
        return Cache::remember($key, $this->cacheSeconds, fn (): array => $this->fetch($url, $query));
    }

    /**
     * @param array<string, scalar> $query
     *
     * @return array<string, mixed>
     */
    private function fetch(string $url, array $query): array
    {
        try {
            $response = Http::withToken((string) $this->token)
                ->acceptJson()
                ->withUserAgent('privatus-filament-analytics')
                ->timeout($this->timeout)
                ->get($url, $query);
        } catch (ConnectionException) {
            throw new PrivatusApiException((string) __('privatus-filament::widgets.errors.unreachable'));
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw new PrivatusApiException((string) __('privatus-filament::widgets.errors.token'));
        }
        if ($response->status() === 404) {
            throw new PrivatusApiException((string) __('privatus-filament::widgets.errors.site'));
        }
        if ($response->status() === 429) {
            throw new PrivatusApiException((string) __('privatus-filament::widgets.errors.rate_limited'));
        }
        if (!$response->successful()) {
            throw new PrivatusApiException((string) __('privatus-filament::widgets.errors.failed', ['status' => $response->status()]));
        }

        $data = $response->json('data');

        return is_array($data) ? $data : [];
    }
}
