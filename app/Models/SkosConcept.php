<?php

namespace App\Models;

use App\SkosRelationType;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;

class SkosConcept extends Model
{
    use Searchable;

    protected $fillable = [
        'uri',
        'pref_label',
        'alt_labels',
        'hidden_labels',
        'notation',
        'definition',
        'note',
        'top_concept',
    ];

    protected function casts(): array
    {
        return [
            'alt_labels' => AsCollection::class,
            'hidden_labels' => AsCollection::class,
            'top_concept' => 'boolean',
        ];
    }

    public function conceptScheme()
    {
        return $this->belongsTo(SkosConceptScheme::class, 'skos_concept_scheme_id');
    }


    public function allRelatedConcepts(): BelongsToMany
    {
        return $this->belongsToMany(SkosConcept::class, 'skos_relation','source_skos_concept_id', 'target_skos_concept_id')
            ->using(SkosRelation::class)
            ->withTimestamps()
            ->withPivot('type')
            ;
    }


    public function related(): BelongsToMany
    {
        return $this->allRelatedConcepts()->withPivotValue('type', SkosRelationType::RELATED);
    }
    
    public function broader(): BelongsToMany
    {
        return $this->allRelatedConcepts()->withPivotValue('type', SkosRelationType::BROADER);
    }
    
    public function narrower(): BelongsToMany
    {
        return $this->allRelatedConcepts()->withPivotValue('type', SkosRelationType::NARROWER);
    }

    /**
     * Other vocabularies mapped concepts
     */
    public function mappedConcepts(): BelongsToMany
    {
        return $this->belongsToMany(SkosConcept::class, 'skos_mapping_relation','source_skos_concept_id', 'target_skos_concept_id')
            ->using(SkosMappingRelation::class)
            ->withTimestamps()
            ->withPivot('type')
            ;
    }

    public function mappingMatches(): BelongsToMany
    {
        return $this->mappedConcepts();
    }
    
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class)
            ->withTimestamps()
            ->orderByPivot('created_at', 'desc');
    }


    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(SkosCollection::class);
    }

    /**
     * Scope to retrieve all descendants of a concept
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $conceptId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDescendantsOfConcept($query, $conceptId = null)
    {
        return $query->withRecursiveExpression(
            'concept_tree',
            function ($query) use ($conceptId) {
                $query->from('skos_concepts as base')
                    ->select(
                        'base.id as root_id', 
                        'base.id as descendant_id', 
                        'base.pref_label as pref_label',
                        DB::raw('0 as parent_id'),
                        DB::raw('0 as depth')
                    )
                    ->where('base.id', $conceptId ?? $this->id)
                    ->unionAll(
                        function ($query) {
                            $query->from('skos_concepts')
                                ->join('skos_relation', 'skos_relation.target_skos_concept_id', '=', 'skos_concepts.id')
                                ->join('concept_tree', 'concept_tree.descendant_id', '=', 'skos_relation.source_skos_concept_id')
                                ->select(
                                    'concept_tree.root_id',
                                    'skos_concepts.id',
                                    'skos_concepts.pref_label',
                                    'skos_relation.source_skos_concept_id',
                                    DB::raw('concept_tree.depth + 1')
                                )
                                ->where('skos_relation.type', SkosRelationType::NARROWER); // Narrower relation type
                        }
                    );
            }
        )
        ->from('concept_tree')
        ->select(
            'root_id',
            'descendant_id', 
            'parent_id',
            'pref_label', 
            'depth'
        )
        ->where('descendant_id', '!=', $conceptId ?? $this->id)
        ->orderBy('depth')
        ->orderBy('pref_label');
    }
    
    /**
     * Scope to retrieve all ancestors of a concept
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $conceptId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAncestorsOfConcept($query, $conceptId = null)
    {
        return $query->withRecursiveExpression(
            'concept_tree',
            function ($query) use ($conceptId) {
                $query->from('skos_concepts as base')
                    ->select(
                        'base.id as root_id', 
                        'base.id as ancestor_id', 
                        'base.pref_label as pref_label',
                        DB::raw('0 as parent_id'),
                        DB::raw('0 as depth')
                    )
                    ->where('base.id', $conceptId ?? $this->id)
                    ->unionAll(
                        function ($query) {
                            $query->from('skos_concepts')
                                ->join('skos_relation', 'skos_relation.target_skos_concept_id', '=', 'skos_concepts.id')
                                ->join('concept_tree', 'concept_tree.ancestor_id', '=', 'skos_relation.source_skos_concept_id')
                                ->select(
                                    'concept_tree.root_id',
                                    'skos_concepts.id',
                                    'skos_concepts.pref_label',
                                    'skos_relation.source_skos_concept_id',
                                    DB::raw('concept_tree.depth + 1')
                                )
                                ->where('skos_relation.type', SkosRelationType::BROADER); // Broader relation type
                        }
                    );
            }
        )
        ->from('concept_tree')
        ->select(
            'root_id',
            'ancestor_id', 
            'parent_id',
            'pref_label', 
            'depth'
        )
        ->where('ancestor_id', '!=', $conceptId ?? $this->id)
        ->orderBy('depth')
        ->orderBy('pref_label');
    }
    
    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['conceptScheme', 'mappingMatches', 'mappingMatches.conceptScheme']);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'uri' => $this->uri,
            'pref_label' => $this->pref_label,
            'alt_labels' => $this->alt_labels,
            'hidden_labels' => $this->hidden_labels,
            'notation' => $this->notation,
            'definition' => $this->definition,
            'note' => $this->note,
            'top_concept' => $this->top_concept,
            'scheme_id' => $this->conceptScheme->id,
            'scheme_pref_label' => $this->conceptScheme->pref_label,
            'linked_concepts' => $this->mappedConcepts->map->pref_label->merge($this->mappedConcepts->map->conceptScheme->map->pref_label)->unique()->values(),
        ];
    }
}
