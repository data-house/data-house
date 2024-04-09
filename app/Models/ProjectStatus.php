<?php

namespace App\Models;

enum ProjectStatus: int
{
    case ACTIVE = 10;

    case COMPLETED = 20;

    case INACTIVE = 30;
    
    case CLOSED = 40;

    public static function parse(string $value): self|null
    {
        $cases = collect(static::cases())->keyBy('name')->merge([
            'ABSCHLUSS' => static::COMPLETED,
            'PROJEKTENDE' => static::COMPLETED,
            'INACTIVE' => static::INACTIVE,
        ]);

        return $cases[str($value)->upper()->toString()] ?? null;
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Open',
            self::COMPLETED => 'Completed',
            self::INACTIVE => 'Inactive',
            self::CLOSED => 'Closed',
        };
    }


    public static function facets()
    {

        $config = static::enabledStatuses();

        if($config->isEmpty()){

            return [
                self::ACTIVE,
                self::INACTIVE,
                self::COMPLETED,
            ];

        }

        return collect(self::cases())->whereIn('name', $config);

    }


    protected static function enabledStatuses()
    {
        return str(config('library.projects.filterable_status', ''))->explode(',')->filter()->values();
    }
}
