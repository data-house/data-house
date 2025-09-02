<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Laravel\Scout\Searchable;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use MeiliSearch\Endpoints\Indexes;
use Laravel\Scout\Builder;

class CatalogEntry extends Model implements Sortable
{
    /** @use HasFactory<\Database\Factories\CatalogEntryFactory> */
    use HasFactory;

    use HasUuids;

    use SortableTrait;

    use Searchable;

    use SoftDeletes;

    protected const DELETED_AT = 'trashed_at';

    protected $fillable = [
        'entry_index',
        'catalog_id',
        'user_id',
        'document_id',
        'project_id',
        'catalog_flow_run_id',
    ];

    /**
     * Configure how sorting fields work
     */
    public $sortable = [
        'order_column_name' => 'entry_index',
        'sort_when_creating' => true,
        'ignore_timestamps' => true, // do not touch update_at when sorting
    ];

    protected function casts(): array
    {
        return [
            'entry_index' => 'int',
        ];
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds()
    {
        return ['uuid'];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Create the sorting query to populate the order column when adding or moving a field
     */
    public function buildSortQuery()
    {
        return static::query()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->where('catalog_id', $this->catalog_id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    public function trashedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trashed_by');
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
    
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function catalogValues(): HasMany
    {
        return $this->hasMany(CatalogValue::class);
    }
    
    public function catalogFlowRun(): BelongsTo
    {
        return $this->belongsTo(CatalogFlowRun::class);
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with([
            'document',
            'project',
            'catalogValues.catalogField',
            'catalogValues.concept',
        ]);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {

        $values = $this->catalogValues->mapWithKeys(function($value){

            $fieldType = $value->catalogField->data_type;

            $field = $fieldType->valueFieldName();

            if($fieldType->isReference()){
                // TODO: currently works only for the SkosConcept referenced type

                if(is_null($value->value_concept)){
                    return [
                        $value->catalogField->uuid => null,
                    ];
                }

                return [
                    $value->catalogField->uuid => collect([
                        $value->concept->pref_label,
                        $value->concept->notation,
                    ])
                    ->merge($value->concept->alt_labels)
                    ->merge($value->concept->hidden_labels)
                    ->filter()
                    ->values()
                    ->toArray(),
                ];
            }

            return [
                $value->catalogField->uuid => $value->{$field},
            ];
        });


        return [
            'id' => $this->id,
            'entry_index' => $this->entry_index,
            'catalog_id' => $this->catalog_id,
            'document_id' => $this->document_id,
            'project_id' => $this->project_id,
            'created_at' => $this->created_at->toDateString(),
            'trashed_at' => $this->trashed_at?->toDateString(),

            'document' => $this->document?->title,
            'project' => $this->project?->title,

            'fields' => [
                ...$values->all(),
            ]
            
        ];
    }


    public static function searchWithCustomFilters($query = '', string $filters, ?User $user = null, ?Project $project = null)
    {
        $escapedQuery = htmlspecialchars($query ?? '', ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $modelClass = static::class;

        /**
         * @var \App\Models\User
         */
        $user = $user ?? auth()->user();

        $team = $user->currentTeam;

        return static::search($escapedQuery, function(Indexes $meilisearch, string $query, array $options) use ($filters, $user, $team, $project){
            
            // Laravel Scout doesn't support Tenant Token, 
            // so we include additional filters to 
            // select only accessible documents. 
            // https://www.meilisearch.com/docs/learn/security/tenant_tokens
            // https://blog.meilisearch.com/role-based-access-guide/

            // $userTenantFilters = collect([
            //     "uploaded_by = {$user->getKey()} AND visibility = ". Visibility::PERSONAL->value,
            //     "visibility IN [".Visibility::PROTECTED->value.",".Visibility::PUBLIC->value."]",
            // ])
            // ->when($team, function (BaseCollection $collection, Team $value) {
            //     return $collection->push("team_id = {$value->getKey()} AND visibility = ". Visibility::TEAM->value);
            // })->join(' OR ');

            // $projectTenantFilters = collect([
            //     $project ? "project_id = {$project->getKey()}" : null,
            // ])->filter()->join(' OR ');

            // $tenantFilters = $projectTenantFilters ? "({$projectTenantFilters} AND ({$userTenantFilters}))" : "({$userTenantFilters})";

            $options['filter'] = ($options['filter'] ?? false) ? "{$filters} AND ({$options['filter']})" : "{$filters}";

            // using same strategy as the scout driver
            // this will be the entrypoint to use the extra facets information
            // included in the search result response
            return $meilisearch->rawSearch($query, $options);

        })
        
        // ->when(!empty($filters), function(Builder $builder) use ($filters) {
        //     foreach($filters as $filter => $value){
        //         $builder->whereIn($filter, Arr::wrap($value));
        //     }
        //     return $builder;
        // })
        // ->options([
        //     // Set extra options for the query
        //     // https://www.meilisearch.com/docs/reference/api/search#facets
        //     // facets parameter can be added, so we get pre-calculated results in the search result response
        //     'facets' => config("scout.meilisearch.index-settings.{$modelClass}.filterableAttributes", []),
        // ])
        ;
    }
}
