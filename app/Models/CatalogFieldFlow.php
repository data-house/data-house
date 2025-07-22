<?php

namespace App\Models;

use App\CatalogFieldActionType;
use App\Data\Catalog\Flows\FlowConfiguration;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogFieldFlow extends Model
{
    /** @use HasFactory<\Database\Factories\CatalogFieldFlowFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'action',
        'catalog_id',
        'title',
        'description',
        'user_id',
        'configuration',
        'source_field_id',
        'target_field_id',
        'auto_trigger',
        'overwrite_existing',
    ];

    public function casts()
    {
        return [
            'action' => CatalogFieldActionType::class,
            'auto_trigger' => 'boolean',
            'overwrite_existing' => 'boolean',
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

    

    public function sourceField(): BelongsTo
    {
        return $this->belongsTo(CatalogField::class, 'source_field_id');
    }

    public function targetField(): BelongsTo
    {
        return $this->belongsTo(CatalogField::class, 'target_field_id');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(CatalogFieldFlowRun::class);
    }

    /**
     * Get the action handler instance
     */
    // public function getHandler(): ActionHandler
    // {
    //     return match($this->type) {
    //         'summarize' => new SummarizeFieldAction($this),
    //         default => throw new \InvalidArgumentException("Unknown action type: {$this->type}")
    //     };
    // }

    // /**
    //  * Execute this action on a specific catalog entry
    //  */
    // public function execute(CatalogEntry $entry): void
    // {
    //     if (!$this->shouldExecute($entry)) {
    //         return;
    //     }

    //     $handler = $this->getHandler();
    //     $handler->handle($entry);
    // }

    // /**
    //  * Determine if the action should be executed for a given entry
    //  */
    // protected function shouldExecute(CatalogEntry $entry): bool
    // {
    //     if (!$this->overwrite_existing) {
    //         // Check if target field already has a value
    //         $existingValue = $entry->catalogValues()
    //             ->where('catalog_field_id', $this->target_field_id)
    //             ->exists();

    //         if ($existingValue) {
    //             return false;
    //         }
    //     }

    //     return true;
    // }
}
