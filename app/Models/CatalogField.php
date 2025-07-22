<?php

namespace App\Models;

use App\CatalogFieldType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
            'make_hidden' => 'boolean',
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

    public function flows(): HasMany
    {
        return $this->hasMany(CatalogFieldFlow::class, 'source_field_id')->chaperone();
    }

    public function skosCollection(): BelongsTo
    {
        return $this->belongsTo(SkosCollection::class);
    }

    
    public function scopeVisible($query)
    {
        return $query
            ->where(fn($q) => $q->whereNull('make_hidden')->orWhere('make_hidden', false));
    }

    public function scopeHidden($query)
    {
        return $query
            ->where('make_hidden', '==', true);
    }


    /**
     * Create the sorting query to populate the order column when adding or moving a field
     */
    public function buildSortQuery()
    {
        return static::query()
            ->where('catalog_id', $this->catalog_id);
    }

    public function sourceActions(): HasMany
    {
        return $this->hasMany(CatalogFieldAction::class, 'source_field_id');
    }

    public function targetActions(): HasMany
    {
        return $this->hasMany(CatalogFieldAction::class, 'target_field_id');
    }


    /**
     * Return the Laravel validation rules to use when verifying input data for this field
     */
    public function validationRules(): array
    {
        return array_filter([
            'nullable',
            $this->data_type === CatalogFieldType::TEXT || $this->data_type === CatalogFieldType::MULTILINE_TEXT ? 'min:1' : null,
            $this->data_type === CatalogFieldType::TEXT ? 'max:600' : null,
            $this->data_type === CatalogFieldType::MULTILINE_TEXT ? 'max:6000' : null,
            $this->data_type === CatalogFieldType::NUMBER ? 'numeric' : null,
            $this->data_type === CatalogFieldType::DATETIME ? 'date' : null,
            $this->data_type === CatalogFieldType::BOOLEAN ? 'boolean' : null,
        ]);
    }

    public function hide(): void
    {
        $this->make_hidden = true;
        $this->save();
    }
    
    public function show(): void
    {
        $this->make_hidden = false;
        $this->save();
    }

    public function toggleVisibility(): void
    {
        if($this->make_hidden){
            $this->show();
            return;
        }

        $this->hide();
    }


    public function toFilamentField(): Field
    {

        $html_identifier = "f_{$this->id}";

        $fieldInstanceType = match($this->data_type) {
            CatalogFieldType::TEXT => TextInput::make($html_identifier),
            CatalogFieldType::MULTILINE_TEXT => Textarea::make($html_identifier)->autosize(),
            CatalogFieldType::NUMBER => TextInput::make($html_identifier)->numeric()->inputMode('decimal'),
            CatalogFieldType::DATETIME => DateTimePicker::make($html_identifier),
            CatalogFieldType::BOOLEAN => Toggle::make($html_identifier)->default(false),
            CatalogFieldType::SKOS_CONCEPT => Select::make($html_identifier)->options($this->skosCollection->concepts()->pluck('pref_label', 'id')),
        };

        if($this->order === 1)
        {
            $fieldInstanceType
                ->autofocus();
        }

        return $fieldInstanceType
            ->nullable()
            ->label($this->title)
            ->validationAttribute($this->title)
            ->helperText($this->description)
            ->rules($this->validationRules());
    }
}
