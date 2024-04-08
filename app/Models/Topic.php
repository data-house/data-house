<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/** 
 * Project topics as defined in internal representation or in a connected graph.
 * As of now this is implemented by reading a materialed graph in a JSON file.
 */
class Topic
{
    private static $topics;

    public static $dataset;


    protected static function getTopicFileContent(): mixed
    {
        list('disk' => $disk, 'file' => $path) = config('library.topics');

        if(is_null($disk) || is_null($path)){
            $file = file_get_contents(resource_path(static::$dataset ?? "data/iki-topics.json"));

            return json_decode($file, true);
        }

        return Storage::disk($disk)->exists($path) ? Storage::disk($disk)->json($path) : [];

    }

    public static function enabledSchemes(): Collection
    {
        return str(config('library.topics.schemes', ''))->explode(',')->filter()->values();
    }
    
    public static function all()
    {
        if(!is_null(static::$topics)){
            return static::$topics;
        }

        $availableConcepts = collect(collect(static::getTopicFileContent())->get('concepts'));

        $enabledSchemes = self::enabledSchemes();

        static::$topics = $availableConcepts->whereIn('scheme', $enabledSchemes);

        return static::$topics;
    }


    /**
     * Select the topics from the hierarchy based on the applied ones
     */
    public static function from(array|Collection $names): Collection
    {
        static::all();

        return static::$topics->only($names)
            ->whereNotNull('parent')
            ->groupBy(['parent'])
            ->map(function($t, $keys) use ($names){
                
                return [
                    ...static::$topics->get($keys),
                    ...['id' => $keys],
                    'selected' => collect($t),
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

        $enabledSchemes = self::enabledSchemes();

        return static::$topics->whereNull('parent')->groupBy('scheme')
            ->filter();
    }

    public static function nameFromKey(string $key): string
    {
        static::all();

        $topic = static::$topics->get($key);

        if(!$topic){
            throw new InvalidArgumentException("No topic with key [{$key}]");
        }

        return $topic['name'] ?? $topic['id'];
    }

    public static function clear()
    {
        static::$topics = null;
    }
}
