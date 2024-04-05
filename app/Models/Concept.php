<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Concept extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'title',
        'alternateLabel',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function scopeLabelled($query, $label)
    {
        return $query->whereAny(['title', 'alternateLabel'], $label);
    }


    /**
     * Attempt to model the SKOS Semantic Relations
     *
     * @internal
     * @link https://www.w3.org/TR/2009/REC-skos-reference-20090818/#semantic-relations
     */
    public function belongsToConcepts(): BelongsToMany
    {
        return $this->belongsToMany(Concept::class, 'concept_relationships', 'source', 'target')
            ->withTimestamps()
            ->withPivot('type');
    }


    /**
     * Linked concepts that are associated to this concept.
     *
     * Althought skos:related is symmetric, this characteristic is not valid for Many to Many relations which are observed from one direction
     * So for example if A relatesTo B this relationship, if looked from B is not returning A
     * 
     * @link https://www.w3.org/TR/2009/NOTE-skos-primer-20090818/#secrel
     */
    public function relatesTo(): BelongsToMany
    {
        return $this->belongsToConcepts()->wherePivot('type', ConceptRelationType::RELATED);
    }
    
    /**
     * Linked concepts that are broader in meaning (i.e. more general)
     *
     * @link https://www.w3.org/TR/2009/NOTE-skos-primer-20090818/#secrel
     */
    public function broader(): BelongsToMany
    {
        return $this->belongsToConcepts()->wherePivot('type', ConceptRelationType::BROADER);
    }
    
    /**
     * Linked concepts that are narrower in meaning (i.e. more specific). Inverse of self::broader
     *
     * @link https://www.w3.org/TR/2009/NOTE-skos-primer-20090818/#secrel
     */
    public function narrower(): BelongsToMany
    {
        return $this->belongsToConcepts()->wherePivot('type', ConceptRelationType::NARROWER);
    }

    /**
     * Related schemes
     */
    public function schemes(): BelongsToMany
    {
        return $this->belongsToMany(ConceptScheme::class, 'concept_in_schemes')
            ->withTimestamps()
            ->withPivot('is_top_concept');
    }
    
    /**
     * Collections in which this concept is included
     *
     * @link https://www.w3.org/TR/2009/NOTE-skos-primer-20090818/#seccollections
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(ConceptCollection::class, 'concept_collection_members')
            ->withTimestamps();
    }
}
