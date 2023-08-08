<?php

namespace App;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as BaseCollection;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable as ScoutSearchable;
use MeiliSearch\Endpoints\Indexes;

trait Searchable
{
    use ScoutSearchable;
    
    public static function advancedSearch($query = '', array $filters = [])
    {
        $modelClass = static::class;

        return static::search($query, function(Indexes $meilisearch, string $query, array $options){

            // using same strategy as the scout driver
            // this will be the entrypoint to use the extra facets information
            // included in the search result response
            return $meilisearch->rawSearch($query, $options);

        })
        ->when(!empty($filters), function(Builder $builder) use ($filters) {
            foreach($filters as $filter => $value){
                $builder->whereIn($filter, Arr::wrap($value));
            }
            return $builder;
        })
        ->options([
            // Set extra options for the query
            // https://www.meilisearch.com/docs/reference/api/search#facets
            // facets parameter can be added, so we get pre-calculated results in the search result response
            'facets' => config("scout.meilisearch.index-settings.{$modelClass}.filterableAttributes", []),
        ]);
    }

}
