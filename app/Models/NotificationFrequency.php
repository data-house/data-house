<?php

namespace App\Models;

use App\Schedule\CronBuilder;
use Illuminate\Support\Collection;

enum NotificationFrequency: string
{

    case DAILY = 'daily'; 

    case WEEKLY = 'weekly'; 
    
    case MONTHLY = 'monthly'; 
    


    /**
     * Get the list of frequencies that can be used
     */
    public static function available(): Collection
    {
        return collect([
            self::DAILY,
        ]);
    }
}
