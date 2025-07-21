<?php

namespace App\Models;

use App\Catalog\Flow\FlowSourceEntity;
use App\Catalog\Flow\FlowTargetEntity;
use App\Catalog\Flow\FlowTrigger;
use App\Data\Catalog\Flows\FlowConfiguration;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogFlow extends Model
{
    
    use HasUuids;

    protected $fillable = [
        'trigger',
        'target_entity',
        'title',
        'description',
        'configuration',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'trigger' => FlowTrigger::class,
            'target_entity' => FlowSourceEntity::class,
            'configuration' => FlowConfiguration::class,
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(CatalogFlowRun::class);
    }
}
