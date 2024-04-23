<?php

namespace App\Topics\Facades;

use App\Topics\TopicManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Topics\Contracts\Driver driver(string $driver = null)
 * @method static \Illuminate\Support\Collection concepts();
 * @method static \Illuminate\Support\Collection from(array|\Illuminate\Support\Collection $names); 
 * @method static \Illuminate\Support\Collection facets();
 * 
 * @see \App\Topics\TopicManager
 */
class Topic extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return TopicManager::class;
    }
}