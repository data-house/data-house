<?php

namespace App\Analytics\Facades;

use App\Analytics\AnalyticsManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Analytics\Contracts\Driver driver(string $driver = null)
 * @method static \Illuminate\Contracts\Support\Htmlable trackerCode()
 *
 * @see \App\Analytics\AnalyticsManager
 */
class Analytics extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return AnalyticsManager::class;
    }
}