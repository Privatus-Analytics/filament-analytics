<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Privatus\FilamentAnalytics\PrivatusApiException;
use Privatus\FilamentAnalytics\Support\Format;
use Privatus\FilamentAnalytics\Support\Periods;
use Privatus\FilamentAnalytics\Widgets\Concerns\UsesPrivatus;

/** Visitors, pageviews, bounce rate and visit time, with the change against the period before. */
class VisitorsOverview extends StatsOverviewWidget
{
    use UsesPrivatus;

    protected static ?int $sort = -3;

    protected ?string $pollingInterval = null;

    public function getHeading(): ?string
    {
        return (string) __('privatus-filament::widgets.overview.heading', [
            'period' => Periods::options()[Periods::clean(null)] ?? '',
        ]);
    }

    /** @return array<Stat> */
    protected function getStats(): array
    {
        try {
            $data = $this->privatus()->aggregate(Periods::clean(null));
        } catch (PrivatusApiException $e) {
            return [
                Stat::make((string) __('privatus-filament::widgets.name'), '-')
                    ->description($e->getMessage())
                    ->color('warning'),
            ];
        }

        $metrics = is_array($data['metrics'] ?? null) ? $data['metrics'] : [];
        $change = is_array($data['change'] ?? null) ? $data['change'] : [];

        return [
            $this->stat('visitors', Format::number($metrics['visitors'] ?? 0), $change['visitors'] ?? null),
            $this->stat('pageviews', Format::number($metrics['pageviews'] ?? 0), $change['pageviews'] ?? null),
            $this->stat('bounce_rate', Format::percent($metrics['bounce_rate'] ?? 0), $change['bounce_rate'] ?? null, lowerIsBetter: true),
            $this->stat('visit_duration', Format::duration($metrics['visit_duration'] ?? 0), $change['visit_duration'] ?? null),
        ];
    }

    protected function stat(string $metric, string $value, mixed $change, bool $lowerIsBetter = false): Stat
    {
        $stat = Stat::make((string) __('privatus-filament::widgets.metrics.' . $metric), $value)
            ->url($this->privatus()->dashboardUrl(), shouldOpenInNewTab: true);

        if (!is_numeric($change)) {
            return $stat->description((string) __('privatus-filament::widgets.overview.no_change'));
        }

        $change = (float) $change;
        $up = $change >= 0;
        $good = $lowerIsBetter ? !$up : $up;

        return $stat
            ->description((string) __('privatus-filament::widgets.overview.change', [
                'change' => ($up ? '+' : '') . Format::percent($change),
            ]))
            ->descriptionIcon($up ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($change == 0.0 ? 'gray' : ($good ? 'success' : 'danger'));
    }
}
