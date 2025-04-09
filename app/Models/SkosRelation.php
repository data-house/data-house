<?php

namespace App\Models;

use App\SkosRelationType;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SkosRelation extends Pivot
{
    protected $fillable = [
        'source_skos_concept_id',
        'target_skos_concept_id',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => SkosRelationType::class,
        ];
    }
}
