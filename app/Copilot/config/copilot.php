<?php

use Carbon\Carbon;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Copilot Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default connection that gets used while using
    | Copilot. This connection is used when syncing all models to the
    | copilot service. You should adjust this based on your needs.
    |
    | Supported: "oaks", "cloud", "null"
    |
    */

    'driver' => env('COPILOT_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the copilot engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */

    'chunk' => [
        'questionable' => 1,
        'unquestionable' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate and various limits
    |--------------------------------------------------------------------------
    |
    | When interacting with artificial intelligence is mandatory to observe
    | how users interact with the system and have control of the used
    | resources. These settings allows you to control various
    | usage limits
    |
    */

    'limits' => [
        'question_length' => 200,
        'questions_per_user_per_day' => (int)env('COPILOT_DAILY_QUESTIONS_PER_USER', 100),
        
        'summaries_per_team' => (int)env('COPILOT_TOTAL_SUMMARIES_PER_TEAM', 50),
    ],

    'timeout' => env('COPILOT_REQUEST_TIMEOUT_MINUTES', 3),

    /*
    |--------------------------------------------------------------------------
    | Cache configuration
    |--------------------------------------------------------------------------
    |
    | This option allows to control whether to store received answers in
    | a common cache. This can reduce the time required to obtain an
    | answer. Please note that the cache is shared among all users.
    |
    */

    'cache' => [
        'store' => env('COPILOT_CACHE', env('CACHE_DRIVER', 'file')),

        'ttl' => env('COPILOT_CACHE_TTL', Carbon::HOURS_PER_DAY * Carbon::MINUTES_PER_HOUR * Carbon::SECONDS_PER_MINUTE), // 1 day
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature configuration
    |--------------------------------------------------------------------------
    |
    | Configure which Copilot features are available for all users.
    |
    */

    'features' => [
        'summary' => env('COPILOT_SUMMARY', false),
        'question' => env('COPILOT_QUESTION', false),
        'tagging' => env('COPILOT_TAGGING', false),
    ],
    
    'engines' => [
    
        /*
        |--------------------------------------------------------------------------
        | Oaks Copilot Configuration
        |--------------------------------------------------------------------------
        |
        | This is a closed source service developed by OneOffTech (https://www.oneofftech.xyz)
        | in collaboration with Oaks (https://www.oaks.cloud).
        | The OAKS Copilot is powered by OpenAI to support questioning documents
        | in multiple languages.
        |
        */

        'oaks' => [
            'host' => env('OAKS_COPILOT_HOST', 'http://localhost:5000'),
        ],
    
        /*
        |--------------------------------------------------------------------------
        | Copilot Cloud Configuration
        |--------------------------------------------------------------------------
        |
        | This is a closed source service developed by OneOffTech (https://www.oneofftech.xyz).
        | To access the Copilot Cloud service contact OneOffTech (https://www.oneofftech.xyz).
        |
        */

        'cloud' => [
            'host' => env('COPILOT_CLOUD_HOST', null),
            'key' => env('COPILOT_CLOUD_KEY', null),
            'library' => env('COPILOT_CLOUD_LIBRARY', null),
            'library-settings' => [
                'indexed-fields' => [
                    'resource_id',
                ],
                'text-processing' => [
                    'n_context_chunk' => env('COPILOT_CLOUD_TEXT_CONTEXT_CHUNKS', 10),
                    'chunk_length' => env('COPILOT_CLOUD_LIBRARY_CHUNK_LENGTH', 490),
                    'chunk_overlap' => env('COPILOT_CLOUD_LIBRARY_CHUNK_OVERLAP', 10),
                ]
            ],
        ],

    ],



];
