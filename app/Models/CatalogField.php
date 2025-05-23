<?php

namespace App\Models;

use App\CatalogFieldType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class CatalogField extends Model implements Sortable
{
    /** @use HasFactory<\Database\Factories\CatalogFieldFactory> */
    use HasFactory;

    use SortableTrait;

    use HasUuids;

    protected $fillable = [
        'title',
        'description',
        'data_type',
    ];

    /**
     * Configure how sorting fields work
     */
    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected function casts(): array
    {
        return [
            'data_type' => CatalogFieldType::class,
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

    public function skosCollection(): HasOne
    {
        return $this->hasOne(SkosCollection::class);
    }


    /**
     * Create the sorting query to populate the order column when adding or moving a field
     */
    public function buildSortQuery()
    {
        return static::query()
            ->where('catalog_id', $this->catalog_id);
    }
}
