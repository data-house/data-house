<?php

namespace App\Models;

enum ProjectType: int
{
    case BILATERAL = 10;
    
    case REGIONAL = 20;

    case GLOBAL = 30;


    public static function parse(string $value): self|null
    {
        $cases = collect(static::cases())->keyBy('name');

        return $cases[str($value)->upper()->toString()] ?? null;
    }


    public function label(): string
    {
        return match ($this) {
            self::BILATERAL => 'Bilateral',
            self::REGIONAL => 'Transnational',
            self::GLOBAL => 'Worldwide',
        };
    }
}
