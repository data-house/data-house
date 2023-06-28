<?php

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
    | Supported: "oaks", "null"
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
        'questionable' => 50,
        'unquestionable' => 50,
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

    ],



];
