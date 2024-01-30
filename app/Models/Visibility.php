<?php

namespace App\Models;

use Illuminate\Support\Arr;
use InvalidArgumentException;

enum Visibility: int
{
    /**
     * Define a resource visible by any authenticated user that no one can control except system administrator
     * 
     * e..g Collection for all Documents
     * e.g. Collection for User starred docs
     */
    case SYSTEM = 1;

    /**
     * The resource is only visible to a specific user
     */
    case PERSONAL = 10;

    /**
     * The resource is visible to all members of the team
     */
    case TEAM = 20;

    /**
     * The resource is visible to all authenticated users
     */
    case PROTECTED = 30;

    /**
     * The resource is publicly visible
     */
    case PUBLIC = 40;

    public function label(): string
    {
        return match ($this) {
            self::PERSONAL => __('Only to me (personal)'),
            self::TEAM => __('Team members'),
            self::PROTECTED => __('All authenticated users'),
            self::PUBLIC => __('Public'),
        };
    }
        
    public function icon(): string
    {
        return match ($this) {
            Visibility::PERSONAL => 'heroicon-m-lock-closed',
            Visibility::TEAM => 'heroicon-m-lock-closed',
            Visibility::PROTECTED => 'heroicon-m-building-library',
            Visibility::PUBLIC => 'heroicon-m-globe-europe-africa',
            null => 'heroicon-o-eye',
        };
    }

    /**
     * Check if the visibility level is lower than the given visibility
     */
    public function lowerThan(Visibility $visibility)
    {
        return $this->value < $visibility->value;
    }

    /**
     * Get the list of visibilities that can be used with \App\Models\Document
     */
    public static function forDocuments(): array
    {
        return tap(collect(self::cases())->skip(1), fn($c) => $c->pop(1))->values()->all();
    }

    /**
     * Get the default document visibility as configured at instance level
     */
    public static function defaultDocumentVisibility(): static
    {
        $configuredValue = config('library.default_document_visibility', Visibility::TEAM->name);

        $nameValueMapping = collect(self::cases())->mapWithKeys(fn ($c) => [$c->name => $c->value]);

        $value = $nameValueMapping[str($configuredValue)->upper()->__toString()] ?? null;

        throw_if(is_null($value), InvalidArgumentException::class, "Invalid visibility [{$configuredValue}].");

        $defaultVisibility = static::from($value);

        throw_if($defaultVisibility === static::SYSTEM, InvalidArgumentException::class, "System visibility cannot be used as default value.");
        
        throw_if($defaultVisibility === static::PUBLIC, InvalidArgumentException::class, "Public visibility cannot be used as default value.");

        return $defaultVisibility;    
    }
}
