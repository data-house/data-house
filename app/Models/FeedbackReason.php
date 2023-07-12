<?php

namespace App\Models;

enum FeedbackReason: int
{
    // please note that the order has impact on the UI

    case IMPRECISE_LANGUAGE = 31;

    case WRONG_ANSWER = 11;

    case PARTIAL_ANSWER = 21;

    case WRONG_REFERENCES = 12;
    
    case PARTIAL_REFERENCES = 22;
    


    public function label(): string
    {
        return match ($this) {
            self::WRONG_ANSWER => __('Wrong answer'),
            self::WRONG_REFERENCES => __('Wrong page references'),
            self::PARTIAL_ANSWER => __('Response is partial or incomplete'),
            self::PARTIAL_REFERENCES => __('Partial references'),
            self::IMPRECISE_LANGUAGE => __('Answer is unintelligible or robotic'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::WRONG_ANSWER => __('The answer given is not relevant or correct.'),
            self::PARTIAL_ANSWER => __('The answer given is missing important aspects or seems truncated.'),
            self::WRONG_REFERENCES => __('The suggested references do not reflect the text of the answer.'),
            self::PARTIAL_REFERENCES => __('Not all relevant pages are included in the suggested references.'),
            self::IMPRECISE_LANGUAGE => __('The language used is imprecise or contains errors.'),
        };
    }
}
