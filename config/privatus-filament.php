<?php

// Settings for the Privatus Analytics widgets in Filament.
// API docs: https://privatusanalytics.com/docs/api

return [
    // The site to show. It starts with pa_. You find it under Site settings, Tracking.
    'site_id' => env('PRIVATUS_SITE_ID'),

    // An API token. It starts with pat_. Make one under Workspace settings, API tokens.
    // Keep it in .env, never in code.
    'api_token' => env('PRIVATUS_API_TOKEN'),

    // Where the API lives. Change it only for testing.
    'api_url' => env('PRIVATUS_API_URL', 'https://privatusanalytics.com'),

    // The date range the widgets show first: today, 7d, 30d, mtd, 90d, 12mo and so on.
    'period' => env('PRIVATUS_FILAMENT_PERIOD', '30d'),

    // How long to keep API results, in seconds. Widgets reload often, so this saves API calls.
    'cache_seconds' => 300,

    // How long to wait for the API, in seconds.
    'timeout' => 10,

    // How many rows the top pages and top sources widgets show.
    'rows' => 7,
];
