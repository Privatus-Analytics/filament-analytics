<?php

return [
    'name' => 'Privatus Analytics',

    'metrics' => [
        'visitors' => 'Visitors',
        'pageviews' => 'Pageviews',
        'bounce_rate' => 'Bounce rate',
        'visit_duration' => 'Visit time',
    ],

    'periods' => [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        '7d' => 'Last 7 days',
        '30d' => 'Last 30 days',
        'mtd' => 'This month',
        'last_month' => 'Last month',
        '90d' => 'Last 90 days',
        '12mo' => 'Last 12 months',
    ],

    'overview' => [
        'heading' => 'Website traffic, :period',
        'change' => ':change on the period before',
        'no_change' => 'Nothing to compare yet',
    ],

    'chart' => [
        'heading' => 'Visitors and pageviews',
    ],

    'top' => [
        'pages' => 'Top pages',
        'sources' => 'Top sources',
        'page' => 'Page',
        'source' => 'Source',
        'direct' => 'Direct or unknown',
        'empty' => 'No visits in this period yet.',
        'open' => 'Open in Privatus Analytics',
    ],

    'errors' => [
        'not_configured' => 'Add PRIVATUS_SITE_ID and PRIVATUS_API_TOKEN to your .env file to see your stats.',
        'unreachable' => 'Could not reach Privatus Analytics. Please try again soon.',
        'token' => 'Privatus Analytics did not accept the API token. Check PRIVATUS_API_TOKEN.',
        'site' => 'Privatus Analytics could not find this site. Check PRIVATUS_SITE_ID.',
        'rate_limited' => 'Too many requests to Privatus Analytics. The stats will come back in a minute.',
        'failed' => 'Privatus Analytics sent an error (:status). Please try again soon.',
    ],
];
