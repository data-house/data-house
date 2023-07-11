<?php

namespace App\Models;

enum FeedbackReason: int
{
    case WRONG_ANSWER = 11;

    case WRONG_REFERENCES = 12;

    case PARTIAL_ANSWER = 21;
    
    case PARTIAL_REFERENCES = 22;


    public function label(): string
    {
        return match ($this) {
            self::WRONG_ANSWER => __('Answer is not correct'),
            self::WRONG_REFERENCES => __('Wrong page references'),
            self::PARTIAL_ANSWER => __('Partial or incomplete answer'),
            self::PARTIAL_REFERENCES => __('Partials references'),
        };
    }

    public function description(): string
    {
        
    }
}
