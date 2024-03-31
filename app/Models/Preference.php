<?php

namespace App\Models;

use Illuminate\Support\Collection;

enum Preference: int
{
    case LOCALE = 1;

    case VISUALIZATION_LAYOUT = 10;
    
    case DO_NOT_TRACK = 20;



    public function acceptableValues(): Collection
    {
        switch ($this) {
            case self::LOCALE:
                return collect(['en']);
                break;
            case self::VISUALIZATION_LAYOUT:
                return collect(['grid','list']);
                break;
            case self::DO_NOT_TRACK:
                return collect(['yes','no']);
                break;
            default:
                return collect([]);
                break;
        } 
    }
}
