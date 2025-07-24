<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogValue extends Model
{
    /** @use HasFactory<\Database\Factories\CatalogValueFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'value_text',
        'value_int',
        'value_date',
        'value_float',
        'value_bool',
        'value_concept',
        'user_id',
        'catalog_id',
        'catalog_field_id',
    ];

    protected function casts(): array
    {
        return [
            'value_int' => 'integer',
            'value_date' => 'datetime',
            'value_float' => 'float',
            'value_bool' => 'bool',
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

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }
    
    public function catalogEntry(): BelongsTo
    {
        return $this->belongsTo(CatalogEntry::class);
    }
    
    public function catalogField(): BelongsTo
    {
        return $this->belongsTo(CatalogField::class);
    }
    
    public function concept(): BelongsTo
    {
        return $this->belongsTo(SkosConcept::class, 'value_concept');
    }

    


    /**
     * Return the name of the value column based on the data type as defined in the catalog field
     */
    protected function getValueColumnName(): string
    {
        return $this->catalogField->data_type->valueFieldName();
    }

    public function hasNoValue(): bool
    {
        return blank($this->value_text) &&
            blank($this->value_int) &&
            blank($this->value_date) &&
            blank($this->value_float) &&
            blank($this->value_bool) &&
            blank($this->value_concept);
    }


    public function toFilamentFieldValue(): array
    {
        $html_identifier = "f_{$this->catalog_field_id}";

       $valueField = $this->catalogField->data_type->valueFieldName();

        return [$html_identifier => $this->{$valueField}];
    }
}
