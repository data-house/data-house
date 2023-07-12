<?php

namespace App\Models;

enum FeedbackVote: int 
{
    case LIKE = 10;
    
    case DISLIKE = 20;

    /**
     * Get the number of points associated to the vote
     * 
     * @return int
     */
    public function points()
    {
        return match ($this) {
            self::LIKE => 1,
            self::DISLIKE => -1,
        };
    }
}
