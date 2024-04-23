<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Topic provider
    |--------------------------------------------------------------------------
    |
    |
    */

    'default' => env('TOPICS_PROVIDER', 'json-concept'),

    /*
    |--------------------------------------------------------------------------
    | Enabled schemes if using a driver that supports schemes
    |--------------------------------------------------------------------------
    */

    'schemes' => env('TOPICS_ENABLED_SCHEMES', env('TOPIC_SCHEMES_ENABLED', null)),


    'drivers' => [
        'json' => [
            'disk' => env('TOPIC_FILE_DISK', env('FILESYSTEM_DISK', 'local')),
            'file' => env('TOPIC_FILE_NAME', null),
        ],
        'json-concepts' => [
            'disk' => env('TOPIC_FILE_DISK', env('FILESYSTEM_DISK', 'local')),
            'file' => env('TOPIC_FILE_NAME', null),
        ],
    ],
];
