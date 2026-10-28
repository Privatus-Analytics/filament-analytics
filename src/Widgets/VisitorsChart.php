<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Widgets;

use DateTimeImmutable;
use Exception;
use Filament\Widgets\ChartWidget;
use Privatus\FilamentAnalytics\PrivatusApiException;
use Privatus\FilamentAnalytics\Support\Periods;
use Privatus\FilamentAnalytics\Widgets\Concerns\UsesPrivatus;

/** Visitors and pageviews over time, with a date range picker. */
class VisitorsChart extends ChartWidget
{
    use UsesPrivatus;

    protected static ?int $sort = -2;

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    protected ?string $error = null;

    public function mount(): void
    {
        $this->filter = Periods::clean($this->filter);

        parent::mount();
    }

    public function getHeading(): ?string
    {
        return (string) __('privatus-filament::widgets.chart.heading');
    }

    public function getDescription(): ?string
    {
        // Load the data first, so an API problem shows under the heading.
        $this->getCachedData();

        return $this->error;
    }

    /** @return array<string, string> */
    protected function getFilters(): ?array
    {
        return Periods::options();
    }

    protected function getType(): string
    {
        return 'line';
    }

    /** @return array<string, mixed> */
    protected function getData(): array
    {
        $this->error = null;

        try {
            $data = $this->privatus()->timeseries(Periods::clean($this->filter));
        } catch (PrivatusApiException $e) {
            $this->error = $e->getMessage();

            return ['datasets' => [], 'labels' => []];
        }

        $series = is_array($data['series'] ?? null) ? $data['series'] : [];
        $interval = is_string($data['interval'] ?? null) ? $data['interval'] : 'day';
        $labels = [];
        $visitors = [];
        $pageviews = [];

        foreach ($series as $point) {
            if (!is_array($point)) {
                continue;
            }
            $labels[] = $this->label($point['date'] ?? null, $interval);
            $visitors[] = (int) ($point['visitors'] ?? 0);
            $pageviews[] = (int) ($point['pageviews'] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => (string) __('privatus-filament::widgets.metrics.visitors'),
                    'data' => $visitors,
                    'fill' => 'start',
                    'tension' => 0.3,
                    'pointRadius' => 0,
                ],
                [
                    'label' => (string) __('privatus-filament::widgets.metrics.pageviews'),
                    'data' => $pageviews,
                    'tension' => 0.3,
                    'borderColor' => '#94a3b8',
                    'backgroundColor' => '#94a3b8',
                    'pointRadius' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function label(mixed $date, string $interval): string
    {
        if (!is_string($date) || $date === '') {
            return '';
        }
        try {
            // Keep the offset the API sent, so a bucket never moves to the day before.
            $time = new DateTimeImmutable($date);
        } catch (Exception) {
            return '';
        }

        return match ($interval) {
            'minute', 'hour' => $time->format('H:i'),
            'month' => $time->format('M Y'),
            default => $time->format('M j'),
        };
    }
}
