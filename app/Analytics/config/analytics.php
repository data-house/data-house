<?php

use App\PdfProcessing\PdfDriver;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Analytics processor
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default Analytics processor that should be used
    | by the framework.
    |
    */

    'default' => env('ANALYTICS_PROCESSOR', null),

    /*
    |--------------------------------------------------------------------------
    | Tracking Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many analytics processors as you wish.
    | Defaults have been set up for each driver as an example
    | of the required values.
    |
    | Supported Drivers: "matomo"
    |
    */

    'tracking' => [
        'user' => (bool)env('ANALYTICS_TRACK_USERS', true),
        'guest' => (bool)env('ANALYTICS_TRACK_GUESTS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Processors
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many analytics processors as you wish.
    | Defaults have been set up for each driver as an example
    | of the required values.
    |
    | Supported Drivers: "matomo"
    |
    */

    'processors' => [

        'matomo' => [
            'host' => env('ANALYTICS_MATOMO_HOST', null),
            'tracker_endpoint' => env('ANALYTICS_MATOMO_TRACKER_ENDPOINT', 'matomo.php'),
            'tracker_script' => env('ANALYTICS_MATOMO_TRACKER_SCRIPT', 'matomo.js'),
            'site_id' => env('ANALYTICS_MATOMO_SITE_ID', null),
            'events_prefix' => env('ANALYTICS_MATOMO_EVENTS_PREFIX', 'mtm-'),
            'user_tracking' => (bool)env('ANALYTICS_MATOMO_USER_TRACKING', env('ANALYTICS_TRACK_USERS', false)),
        ],

    ],
];
