<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class CatalogEntry extends Model implements Sortable
{
    /** @use HasFactory<\Database\Factories\CatalogEntryFactory> */
    use HasFactory;

    use HasUuids;

    use SortableTrait;

    use Searchable;

    protected $fillable = [
        'entry_index',
        'catalog_id',
        'user_id',
        'document_id',
        'project_id',
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
            // TODO: order by field.order
            // TODO: chaperone, otherwise I get problems accessing the field definition from within the value
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

            'document' => $this->document?->title,
            'project' => $this->project?->title,

            ...$values->all(),
            
        ];
    }
}
