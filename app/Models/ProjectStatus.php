<?php

namespace App\Models;

enum ProjectStatus: int
{
    case ACTIVE = 10;

    case COMPLETED = 20;

    case CLOSED = 30;

    public static function parse(string $value): self|null
    {
        $cases = collect(static::cases())->keyBy('name')->merge([
            'ABSCHLUSS' => static::COMPLETED,
            'PROJEKTENDE' => static::COMPLETED,
            'INACTIVE' => static::CLOSED,
        ]);

        return $cases[str($value)->upper()->toString()] ?? null;
    }
}
