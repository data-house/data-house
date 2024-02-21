<?php

namespace App;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Models\Visibility;
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
    
    /**
     * Perform a search over indexed documents.
     *
     * The search is scoped to the documents accessible by the user.
     * This mimics the multitenancy approach as achievable using tenant tokens
     * https://www.meilisearch.com/docs/learn/security/tenant_tokens 
     *
     * @param string $query The terms to search for
     * @param array $filters The filters to apply
     * @param User|null $user The user that is performing the search. If null the currently authenticated user is considered
     */
    public static function advancedSearch($query = '', array $filters = [])
    {
        $escapedQuery = htmlspecialchars($query ?? '', ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $modelClass = static::class;

        return static::search($escapedQuery, function(Indexes $meilisearch, string $query, array $options) {
            
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
    
    /**
     * Perform a search over indexed documents respecting user access requirements.
     *
     * The search is scoped to the documents accessible by the user.
     * This mimics the multitenancy approach as achievable using tenant tokens
     * https://www.meilisearch.com/docs/learn/security/tenant_tokens 
     *
     * @param string $query The terms to search for
     * @param array $filters The filters to apply
     * @param User|null $user The user that is performing the search. If null the currently authenticated user is considered
     */
    public static function tenantSearch($query = '', array $filters = [], ?User $user = null, ?Project $project = null)
    {
        $escapedQuery = htmlspecialchars($query ?? '', ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $modelClass = static::class;

        /**
         * @var \App\Models\User
         */
        $user = $user ?? auth()->user();

        $team = $user->currentTeam;

        return static::search($escapedQuery, function(Indexes $meilisearch, string $query, array $options) use ($user, $team, $project){
            
            // Laravel Scout doesn't support Tenant Token, 
            // so we include additional filters to 
            // select only accessible documents. 
            // https://www.meilisearch.com/docs/learn/security/tenant_tokens
            // https://blog.meilisearch.com/role-based-access-guide/

            $userTenantFilters = collect([
                "uploaded_by = {$user->getKey()} AND visibility = ". Visibility::PERSONAL->value,
                "visibility IN [".Visibility::PROTECTED->value.",".Visibility::PUBLIC->value."]",
            ])
            ->when($team, function (BaseCollection $collection, Team $value) {
                return $collection->push("team_id = {$value->getKey()} AND visibility = ". Visibility::TEAM->value);
            })->join(' OR ');

            $projectTenantFilters = collect([
                $project ? "project_id = {$project->getKey()}" : null,
            ])->filter()->join(' OR ');

            $tenantFilters = $projectTenantFilters ? "{$projectTenantFilters} AND ({$userTenantFilters})" : "({$userTenantFilters})";

            $options['filter'] = ($options['filter'] ?? false) ? "({$tenantFilters}) AND ({$options['filter']})" : "({$tenantFilters})";

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
