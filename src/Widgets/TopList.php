<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Widgets;

use Filament\Widgets\Widget;
use Privatus\FilamentAnalytics\PrivatusApiException;
use Privatus\FilamentAnalytics\Support\Format;
use Privatus\FilamentAnalytics\Support\Periods;
use Privatus\FilamentAnalytics\Widgets\Concerns\UsesPrivatus;

/** A short ranked list of one dimension, such as pages or sources. */
abstract class TopList extends Widget
{
    use UsesPrivatus;

    protected string $view = 'privatus-filament::widgets.top-list';

    protected int | string | array $columnSpan = 1;

    /** The API dimension, for example page or referrer. */
    abstract protected function dimension(): string;

    /** The row field to rank by, for example pageviews or visitors. */
    abstract protected function metric(): string;

    abstract protected function heading(): string;

    /** The name of the first column, for example Page. */
    abstract protected function columnLabel(): string;

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $period = Periods::clean(null);
        $rows = [];
        $error = null;

        try {
            $limit = (int) config('privatus-filament.rows', 7);
            $data = $this->privatus()->breakdown($this->dimension(), $period, $limit);
            foreach ((array) ($data['rows'] ?? []) as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $value = $row['value'] ?? null;
                $rows[] = [
                    'label' => is_scalar($value) && (string) $value !== '' ? (string) $value : (string) __('privatus-filament::widgets.top.direct'),
                    'count' => Format::number($row[$this->metric()] ?? 0),
                    'share' => max(0.0, min(100.0, is_numeric($row['share'] ?? null) ? (float) $row['share'] : 0.0)),
                ];
            }
        } catch (PrivatusApiException $e) {
            $error = $e->getMessage();
        }

        return [
            'heading' => $this->heading(),
            'description' => Periods::options()[$period] ?? '',
            'columnLabel' => $this->columnLabel(),
            'metricLabel' => (string) __('privatus-filament::widgets.metrics.' . $this->metric()),
            'rows' => $rows,
            'error' => $error,
            'dashboardUrl' => $this->privatus()->dashboardUrl(),
        ];
    }
}
