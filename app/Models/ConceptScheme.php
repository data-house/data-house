<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ConceptScheme extends Model
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
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    /**
     * Concept in scheme
     */
    public function concepts(): BelongsToMany
    {
        return $this->belongsToMany(Concept::class, 'concept_in_schemes')
            ->withTimestamps()
            ->withPivot('is_top_concept');
    }
}
