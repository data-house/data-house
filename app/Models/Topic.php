<?php

namespace App\Models;

use Illuminate\Support\Collection;

/** 
 * Project topics as defined in internal representation or in a connected graph.
 * As of now this is implemented by reading a materialed graph in a JSON file.
 */
class Topic
{
    private static $topics;

    public static $dataset;
    
    public static function all()
    {
        if(!is_null(static::$topics)){
            return static::$topics;
        }

        $file = file_get_contents(resource_path(static::$dataset ?? "data/iki-topics.json"));

        static::$topics = collect(json_decode($file, true));

        return static::$topics;
    }


    /**
     * Select the topics from the hierarchy based on the applied ones
     */
    public static function from(array|Collection $names): Collection
    {
        static::all();

        // Select children
        // group by parent?

        $childToParent = static::$topics->mapWithKeys(function($t){
                return collect($t['children'] ?? [])->mapWithKeys(fn($child) => [$child['name'] => $t['name']]);
            })
            ->only($names);

        return static::$topics->only($childToParent->values()->unique())
            ->map(function($t) use ($names){
                return [
                    ...$t,
                    'selected' => collect($t['children'] ?? [])->whereIn('name', $names)->values()->toArray(),
                ];
            })
            ->values();
    }


    
    /**
     * Get the topics to be used in filters/facets
     */
    public static function facets(): Collection
    {
        static::all();

        return static::$topics->flatMap(function($t){
                return $t['children'] ?? null;
            })
            ->filter()
            ->mapWithKeys(fn($t) => [$t['name'] => str($t['name'])->title()->toString()]);
    }
}
