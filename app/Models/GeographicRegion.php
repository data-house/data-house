<?php

namespace App\Models;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use PrinsFrank\Standards\Country\CountryAlpha3;
use PrinsFrank\Standards\Region\GeographicRegion as RegionGeographicRegion;

/** 
 * Geographic regions as defined in UN M49 https://unstats.un.org/unsd/methodology/m49/overview
 */
class GeographicRegion
{
    private static $countries;

    public static $dataset;
    
    public static function all()
    {
        if(!is_null(static::$countries)){
            return static::$countries;
        }

        $file = file_get_contents(resource_path(static::$dataset ?? "data/geographic-regions.json"));

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
            ])->filter()->values())
            ->unique()
            ->values();
    }
    
    public static function getRegionFrom(CountryAlpha3 $code): RegionGeographicRegion
    {
        static::all();

        $country = static::$countries[$code->value] ?? null;

        if(!$country){
            throw new InvalidArgumentException(__('No country found for :value', ['value' => $code->value]));
        }

        return RegionGeographicRegion::from($country['region-code']);
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
                // "region-name",
                "sub-region-name",
                // "intermediate-region-name",
            ])->values())
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->sort();
    }
}
