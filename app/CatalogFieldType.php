<?php

namespace App;

enum CatalogFieldType: int
{
    /**
     * A single line text field
     */
    case TEXT = 10;
    
    /**
     * A multiline text field
     */
    case MULTILINE_TEXT = 11;

    /**
     * A number field (integer or float)
     */
    case NUMBER = 20;

    /**
     * A date and time field
     */
    case DATETIME = 30;

    /**
     * A yes/no field
     */
    case BOOLEAN = 40;

    /**
     * A single SKOS Concept contained in a specific SKOS Collection
     */
    case SKOS_CONCEPT = 50;

    public function icon(): string
    {
        return match($this) {
            self::TEXT => 'heroicon-m-bars-3-bottom-left',
            self::MULTILINE_TEXT => 'heroicon-m-bars-4',
            self::NUMBER => 'heroicon-m-hashtag',
            self::DATETIME => 'heroicon-m-calendar',
            self::BOOLEAN => 'heroicon-m-check-circle',
            self::SKOS_CONCEPT => 'heroicon-m-tag',
        };
    }
    
    public function valueFieldName(): string
    {
        return match($this) {
            self::TEXT => 'value_text',
            self::MULTILINE_TEXT => 'value_text',
            self::NUMBER => 'value_float',
            self::DATETIME => 'value_date',
            self::BOOLEAN => 'value_bool',
            self::SKOS_CONCEPT => 'value_concept',
        };
    }


    public function label(): string
    {
        return trans("catalog-lang.field_types.label.{$this->name}");
    }
    
    public function description(): string
    {
        return trans("catalog-lang.field_types.description.{$this->name}");
    }

    public static function allLabels(): array
    {
        return collect(static::cases())->mapWithKeys(fn($entry) => [$entry->value => $entry->label()])->toArray();
    }
    
    public static function allDescriptions(): array
    {
        return collect(static::cases())->mapWithKeys(fn($entry) => [$entry->value => $entry->description()])->toArray();
    }

    public function isReference(): bool
    {
        return $this === self::SKOS_CONCEPT;
    }
    
    public function isPrimitive(): bool
    {
        return $this !== self::SKOS_CONCEPT;
    }
    
}
