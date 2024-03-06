<?php

namespace App\Models;


enum ImportStatus: int
{
    case CREATED   = 10;
    case RUNNING   = 20;
    case COMPLETED = 30;
    case CANCELLED = 40;
    case FAILED    = 50;


    public function label(): string
    {
        return str($this->name)->title()->toString();
    }

    public function style(): ?string
    {
        return match ($this) {
            self::CREATED => 'pending',
            self::RUNNING => 'pending',
            self::COMPLETED => 'success',
            self::CANCELLED => 'cancel',
            self::FAILED => 'failure',
        };
    }
}
