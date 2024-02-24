<?php

namespace App\DocumentThumbnail\Facades;

use App\DocumentThumbnail\DocumentThumbnailManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\DocumentThumbnail\Contracts\Driver driver(string $driver = null)
 * @method static \App\DocumentThumbnail\FileThumbnail thumbnail(\App\PdfProcessing\DocumentReference $reference)
 *
 * @see \App\DocumentThumbnail\DocumentThumbnailManager
 */
class Thumbnail extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return DocumentThumbnailManager::class;
    }


    public static function enabled(): bool
    {
        return (bool)config('thumbnail.enable', false);
    }
    
    public static function disabled(): bool
    {
        return !static::enabled();
    }
}