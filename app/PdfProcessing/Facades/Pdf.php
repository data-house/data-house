<?php

namespace App\PdfProcessing\Facades;

use App\PdfProcessing\PdfProcessingManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\PdfProcessing\Contracts\Driver driver(string $driver = null)
 * @method static \App\PdfProcessing\DocumentContent text(\App\PdfProcessing\DocumentReference $document)
 * @method static \App\PdfProcessing\DocumentProperties properties(\App\PdfProcessing\DocumentReference $document)
 *
 * @see \App\PdfProcessing\PdfProcessingManager
 */
class Pdf extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return PdfProcessingManager::class;
    }
}