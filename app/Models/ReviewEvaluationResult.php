<?php

namespace App\Models;

enum ReviewEvaluationResult: int
{
    case APPROVED = 10;

    case CHANGES_APPLIED = 20;
    
    case REJECTED = 30;


    public function label(): string
    {
        return match ($this) {
            self::APPROVED => __('Approved'),
            self::CHANGES_APPLIED => __('Approved with some changes'),
            self::REJECTED => __('Rejected'),
        };
    }
}
