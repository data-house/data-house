<?php

namespace App\Models;


enum Role: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case CONTRIBUTOR = 'contributor';
    case GUEST = 'guest';


    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Focal Point',
            self::CONTRIBUTOR => 'Contributor',
            self::GUEST => 'Guest',
        };
    }
}
