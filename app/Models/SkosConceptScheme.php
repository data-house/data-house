<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;

class SkosConceptScheme extends Model
{
    protected $fillable = [
        'uri',
        'vocabulary_base_uri',
        'pref_label',
        'alt_labels',
        'hidden_labels',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'alt_labels' => AsCollection::class,
            'hidden_labels' => AsCollection::class,
        ];
    }



    public function concepts()
    {
        return $this->hasMany(SkosConcept::class);
    }
    
    public function topConcepts()
    {
        return $this->hasMany(SkosConcept::class)
            ->where('top_concept', true)
            ->orderBy('pref_label', 'ASC');
    }

    public function collections()
    {
        return $this->hasMany(SkosCollection::class);
    }
}
