<?php

namespace App\Models;

use Carbon\Carbon;

enum WeekDays: int
{
    case SUNDAY = Carbon::SUNDAY;
    case MONDAY = Carbon::MONDAY;
    case TUESDAY = Carbon::TUESDAY;
    case WEDNESDAY = Carbon::WEDNESDAY;
    case THURSDAY = Carbon::THURSDAY;
    case FRIDAY = Carbon::FRIDAY;
    case SATURDAY = Carbon::SATURDAY;
}
