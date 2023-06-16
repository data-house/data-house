<?php

return [

    'enable' => env('CONVERSION_ENABLE', false),


    'disk' => env('CONVERSION_DISK', env('FILESYSTEM_DISK', 'local')),

    'drivers' => [
        'onlyoffice' => [
            'url' => env('CONVERSION_ONLYOFFICE_URL'),
            'jwt' => env('CONVERSION_ONLYOFFICE_TOKEN'),
        ],
    ],
];
