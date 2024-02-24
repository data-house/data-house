<?php

return [

    'enable' => env('THUMBNAIL_ENABLE', false),


    'disk' => env('THUMBNAIL_DISK', 'thumbnails'),

    'drivers' => [
        'imaginary' => [
            'url' => env('THUMBNAIL_IMAGINARY_URL'),
        ],
    ],
];
