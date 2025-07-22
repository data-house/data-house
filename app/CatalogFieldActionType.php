<?php

namespace App;

enum CatalogFieldActionType: int
{
    /**
     * Summarize {{column}}
     */
    case AI_SUMMARY = 601;
    
    /**
     * Summarize {{column}}
     */
    case AI_REWRITE = 602;

    /**
     * Translate {{column}}
     */
    case AI_TRANSLATE = 603;

    /**
     * Extract keywords from {{column}}
     */
    case AI_EXTRACT_KEYWORDS = 604;
    
    /**
     * Extract keywords from {{column}}
     */
    case AI_CLASSIFY = 605;
    
    /**
     * Do something with {{ column }}, free prompt
     */
    case AI_DO_SOMETHING = 606;


    // public function label(): string
    // {
    //     return trans("catalog-lang.field_types.label.{$this->name}");
    // }
    
    // public function description(): string
    // {
    //     return trans("catalog-lang.field_types.description.{$this->name}");
    // }

    // public static function allLabels(): array
    // {
    //     return collect(static::cases())->mapWithKeys(fn($entry) => [$entry->value => $entry->label()])->toArray();
    // }
    
    // public static function allDescriptions(): array
    // {
    //     return collect(static::cases())->mapWithKeys(fn($entry) => [$entry->value => $entry->description()])->toArray();
    // }
    
}
