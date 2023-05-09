<?php

namespace App\PdfProcessing\Facades;

use App\PdfProcessing\PdfProcessingManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\PdfProcessing\Contracts\Driver driver(string $driver = null)
 * @method static string text(string $path)
 * @method static \App\PdfProcessing\DocumentProperties properties(string $path)
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