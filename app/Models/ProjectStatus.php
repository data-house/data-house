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


    public static function facets()
    {
        return [
            self::ACTIVE,
            self::INACTIVE,
            self::COMPLETED,
        ];
    }
}
