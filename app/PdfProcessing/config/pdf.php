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

    'default' => env('PDF_PROCESSOR', PdfDriver::SMALOT_PDF->value),

    /*
    |--------------------------------------------------------------------------
    | PDF Processors
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many pdf processors as you wish.
    | Defaults have been set up for each driver as an example
    | of the required values.
    |
    | Supported Drivers: "smalot", "xpdf", "copilot"
    |
    */

    'processors' => [

        PdfDriver::SMALOT_PDF->value => [
        ],

        PdfDriver::XPDF->value => [

        ],

        PdfDriver::COPILOT->value => [
            'host' => env('PDF_PROCESSOR_COPILOT_URL'),
        ],
        
        PdfDriver::EXTRACTOR_SERVICE->value => [
            'host' => env('PDF_EXTRACTOR_SERVICE_URL'),
            'driver'=> env('PDF_EXTRACTOR_SERVICE_DRIVER', 'pymupdf'),
        ],

    ],
];
