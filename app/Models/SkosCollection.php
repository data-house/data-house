<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SkosCollection extends Model
{
    protected $fillable = [
        'uri',
        'pref_label',
        'alt_labels',
        'hidden_labels',
        'notation',
        'definition',
        'note',
        'skos_concept_scheme_id',
    ];

    protected function casts(): array
    {
        return [
            'alt_labels' => AsCollection::class,
            'hidden_labels' => AsCollection::class,
        ];
    }

    public function conceptScheme()
    {
        return $this->belongsTo(SkosConceptScheme::class);
    }

    public function concepts(): BelongsToMany
    {
        return $this->belongsToMany(SkosConcept::class);
    }
}
