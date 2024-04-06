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
    
    public static function all()
    {
        if(!is_null(static::$topics)){
            return static::$topics;
        }

        static::$topics = collect(static::getTopicFileContent());

        return static::$topics;
    }


    /**
     * Select the topics from the hierarchy based on the applied ones
     */
    public static function from(array|Collection $names, ?string $model = null): Collection
    {
        return static::conceptsForModel($model)->mapWithKeys(function($t, $key){
                return [

                    $key => collect($t['children'] ?? [])->mapWithKeys(function($child) use ($t) {

                        if($child['children'] ?? false){
                            return [$child['name'] => collect($child['children'] ?? [])->mapWithKeys(fn($sub) => [$sub['name'] => $t['name'].'.'.$child['name'].'.'.$sub['name']])];
                        }

                        return [$child['name'] => $t['name'].'.'.$child['name']];
                    })->toArray(),
                ];
            })
            ->dot()
            ->filter(fn($value, $key) => str($key)->contains($names))

            ->undot();

        // return static::$topics->only($childToParent->values()->unique())
        //     ->map(function($t) use ($names){
        //         return [
        //             ...$t,
        //             'selected' => collect($t['children'] ?? [])->whereIn('name', $names)->values()->toArray(),
        //         ];
        //     })
        //     ->values();
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

    public static function nameFromKey(string $key): string
    {
        static::all();

        $topic = static::$topics->get($key);

        if(!$topic){
            throw new InvalidArgumentException("No topic with key [{$key}]");
        }

        return $topic['name'] ?? $topic['id'];
    }

    protected static function conceptsForModel(?string $model = null): Collection
    {
        static::all();

        return static::$topics
            ->when($model, function(Collection $collection, $model){
                return $collection->filter(fn($entry) => in_array($model, $entry['resources'] ?? []));
            });
    }
    
    /**
     * @internal
     */
    public static function conceptCollections(?string $model = null): Collection
    {
        return static::conceptsForModel($model)
            ->mapWithKeys(fn($t) => [
                $t['id'] => [
                    'title' => str($t['name'])->title()->toString(),
                    'children' => $t['children']
                ]
            ]);
    }
    
    /**
     * @internal
     */
    public static function selectConceptsForIndexing(string $model, array $selection): Collection
    {

        $selection = collect($selection)->values();

        $flattenedTopics = static::conceptsForModel($model)
            ->flatMap(function($entry){
                $mappedChildren = collect($entry['children'])
                    ->dot()
                    ->filter(fn($value, $key) => str($key)->endsWith('.name'))
                    ->values();

                return [
                    [$entry['id'] => $mappedChildren],
                ];
            })
            ->collapse();
        

        return $flattenedTopics->mapWithKeys(function($entries, $key) use ($selection){
                return [$key => $entries->intersect($selection)->values()];
            });
    }


    public static function clear()
    {
        static::$topics = null;
    }
}
