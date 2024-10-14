<?php

use App\PdfProcessing\PdfDriver;

return [

    /*
    |--------------------------------------------------------------------------
    | Default PDF processor
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default PDF processor that should be used
    | by the framework.
    |
    */

    'default' => env('PDF_PROCESSOR', PdfDriver::SMALOT->value),

    /*
    |--------------------------------------------------------------------------
    | PDF Processors
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many pdf processors as you wish.
    | Defaults have been set up for each driver as an example
    | of the required values.
    |
    | Supported Drivers: "smalot", "parse" (https://github.com/data-house/pdf-text-extractor)
    |
    */

    'processors' => [

        PdfDriver::SMALOT->value => [
        ],
        
        PdfDriver::PARSE->value => [
            'host' => env('PARSE_URL', env('PDF_EXTRACTOR_SERVICE_URL')),
            'token' => env('PARSE_TOKEN'),
            'processor'=> env('PARSE_PROCESSOR', env('PDF_EXTRACTOR_SERVICE_DRIVER', 'pymupdf')),
        ],

    ],
];
