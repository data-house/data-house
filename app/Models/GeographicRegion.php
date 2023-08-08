<?php

namespace App\Models;

use Illuminate\Support\Collection;

/** 
 * Geographic regions as defined in UN M49 https://unstats.un.org/unsd/methodology/m49/overview
 */
class GeographicRegion
{
    private static $countries;
    
    public static function all()
    {
        if(!is_null(static::$countries)){
            return static::$countries;
        }

        $file = file_get_contents(resource_path('data/iki-geographic-regions.json'));

        static::$countries = json_decode($file, true);

        return static::$countries;
    }



    /**
     * Get the geographic regions of the given countries 
     */
    public static function from(Collection $countryCodes): Collection
    {
        static::all();

        return $countryCodes?->map(fn($c) => static::$countries[$c])
            ->filter()
            ->map(fn($e) => collect($e)->only([
                "region-name",
                "sub-region-name",
                "intermediate-region-name",
            ])->values())
            ->flatten()
            ->filter()
            ->unique()
            ->values();
    }
    
    /**
     * Get the geographic regions for facets 
     */
    public static function facets(Collection $countryCodes): Collection
    {
        static::all();

        return $countryCodes?->map(fn($c) => static::$countries[$c])
            ->filter()
            ->map(fn($e) => collect($e)->only([
                "region-name",
                // "sub-region-name",
                // "intermediate-region-name",
            ])->values())
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->sort();
    }
}
