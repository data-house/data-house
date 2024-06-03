<?php

namespace App\Models;

use App\Schedule\CronBuilder;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Carbon\CarbonPeriodImmutable;
use Illuminate\Support\Collection;

enum NotificationFrequency: string
{

    case DAILY = 'daily'; 

    case WEEKLY = 'weekly'; 
    
    case MONTHLY = 'monthly'; 
    

    /**
     * Get the corresponding period.
     */
    public function getPeriod(): CarbonPeriodImmutable
    {
        return match ($this) {
            self::DAILY => CarbonPeriodImmutable::since(today()->subHours(Carbon::HOURS_PER_DAY))->untilNow(),
            self::WEEKLY => CarbonPeriodImmutable::since(today()->subWeek()->startOfDay())->untilNow(),
            self::MONTHLY => CarbonPeriodImmutable::since(today()->subMonth()->startOfDay())->untilNow(),
        };
    }

    /**
     * Get the list of frequencies that can be used
     */
    public static function available(): Collection
    {
        return collect([
            self::DAILY,
            self::WEEKLY,
        ]);
    }
}
