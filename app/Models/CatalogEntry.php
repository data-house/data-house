<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CatalogEntry extends Model
{
    /** @use HasFactory<\Database\Factories\CatalogEntryFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'entry_index',
        'catalog_id',
        'user_id',
        'document_id',
        'project_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
