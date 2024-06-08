<?php

namespace App\Models;

enum ReviewStatus: int
{
    case SUBMITTED = 10;

    case IN_PROGRESS = 20;
    
    case COMPLETED = 30;


    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => __('Submitted'),
            self::COMPLETED => __('Completed'),
            self::IN_PROGRESS => __('In-progress'),
        };
    }
}
