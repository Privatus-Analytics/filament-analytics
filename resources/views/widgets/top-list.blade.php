<x-filament-widgets::widget>
    {{-- Plain CSS with Filament's color variables, so it works without a custom theme. --}}
    <style>
        .pa-top-note { margin: 0; font-size: .875rem; color: var(--gray-500); }
        .pa-top-head { display: flex; justify-content: space-between; padding-bottom: .5rem; font-size: .75rem; font-weight: 500; color: var(--gray-500); }
        .pa-top-list { list-style: none; margin: 0; padding: 0; display: grid; gap: .25rem; }
        .pa-top-row { position: relative; display: flex; align-items: center; justify-content: space-between; gap: .75rem; overflow: hidden; border-radius: .375rem; padding: .375rem .5rem; font-size: .875rem; }
        .pa-top-bar { position: absolute; inset: 0 auto 0 0; border-radius: .375rem; background: color-mix(in oklab, var(--primary-500) 12%, transparent); }
        .pa-top-label { position: relative; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--gray-950); }
        .pa-top-count { position: relative; flex-shrink: 0; font-variant-numeric: tabular-nums; color: var(--gray-700); }
        .dark .pa-top-note, .dark .pa-top-head { color: var(--gray-400); }
        .dark .pa-top-bar { background: color-mix(in oklab, var(--primary-400) 18%, transparent); }
        .dark .pa-top-label { color: #fff; }
        .dark .pa-top-count { color: var(--gray-200); }
    </style>
    <x-filament::section :heading="$heading" :description="$description">
        <x-slot name="afterHeader">
            <x-filament::link :href="$dashboardUrl" target="_blank" rel="noopener" size="sm">
                {{ __('privatus-filament::widgets.top.open') }}
            </x-filament::link>
        </x-slot>

        @if ($error)
            <p class="pa-top-note">{{ $error }}</p>
        @elseif (count($rows) === 0)
            <p class="pa-top-note">{{ __('privatus-filament::widgets.top.empty') }}</p>
        @else
            <div class="pa-top-head">
                <span>{{ $columnLabel }}</span>
                <span>{{ $metricLabel }}</span>
            </div>
            <ul class="pa-top-list">
                @foreach ($rows as $row)
                    <li class="pa-top-row">
                        <span class="pa-top-bar" style="width: {{ number_format($row['share'], 1, '.', '') }}%"></span>
                        <span class="pa-top-label" title="{{ $row['label'] }}">{{ $row['label'] }}</span>
                        <span class="pa-top-count">{{ $row['count'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
