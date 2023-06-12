<?php

namespace App\DocumentConversion\Facades;

use App\DocumentConversion\DocumentConversionManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\DocumentConversion\Contracts\Driver driver(string $driver = null)
 * @method static string text(string $path)
 * @method static \App\DocumentConversion\DocumentProperties properties(string $path)
 *
 * @see \App\DocumentConversion\DocumentConversionManager
 */
class Convert extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return DocumentConversionManager::class;
    }
}