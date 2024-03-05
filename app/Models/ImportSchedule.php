<?php

namespace App\Models;

use App\Schedule\CronBuilder;

enum ImportSchedule: string
{

    case NOT_SCHEDULED = 'none';

    // case HOURLY = 'hourly'; // Hourly is currently not offered as the overlap prevention of jobs is set to expire after 2 hours

    case EVERY_TWO_HOURS = 'everyTwoHours'; 

    case EVERY_THREE_HOURS = 'everyThreeHours'; 

    case EVERY_FOUR_HOURS = 'everyFourHours'; 

    case EVERY_SIX_HOURS = 'everySixHours'; 

    case DAILY = 'daily'; 
    
    case CUSTOM = 'custom'; 



    public function getExpression(): ?string
    {
        return match ($this) {
            self::NOT_SCHEDULED => null,
            self::CUSTOM => null,
            self::EVERY_TWO_HOURS => CronBuilder::make()->everyTwoHours()->getExpression(),
            self::EVERY_THREE_HOURS => CronBuilder::make()->everyThreeHours()->getExpression(),
            self::EVERY_FOUR_HOURS => CronBuilder::make()->everyFourHours()->getExpression(),
            self::EVERY_SIX_HOURS => CronBuilder::make()->everySixHours()->getExpression(),
            self::DAILY => CronBuilder::make()->daily()->at('22:15')->getExpression(),
            default => null
        };
    }
}
