<?php

namespace App\Models;

use Illuminate\Support\Arr;

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
            self::PROTECTED => __('Authenticated users'),
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
}
