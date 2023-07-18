<?php

namespace App\Models;

enum FeedbackVote: int 
{
    case LIKE = 10;

    case IMPROVABLE = 15; // considered a positive feedback
    
    case DISLIKE = 20;

    /**
     * Get the number of points associated to the vote
     * 
     * @return int
     */
    public function points()
    {
        return match ($this) {
            self::LIKE => 2, // TODO: migrate existing feedbacks
            self::IMPROVABLE => 1,
            self::DISLIKE => -1,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::LIKE => __('Good'),
            self::IMPROVABLE => __('Improvable'),
            self::DISLIKE => __('Poor'),
        };
    }
}
