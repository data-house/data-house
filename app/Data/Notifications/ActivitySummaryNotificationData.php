<?php

namespace App\Data\Notifications;

use App\Models\NotificationFrequency;
use App\Models\WeekDays;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ActivitySummaryNotificationData extends Data
{
    public function __construct(

      /**
       * Activate the activity summary notification
       */
      public bool $enable = true,

      /**
       * Set the frequency that the user want to receive those notifications. Default on Weekly.
       */
      #[WithCast(EnumCast::class)]
      public NotificationFrequency $frequency = NotificationFrequency::WEEKLY,
      
      /**
       * Set the preferred day to receive the summary. Default on Monday. Ignored if frequency is daily
       */
      #[WithCast(EnumCast::class)]
      public WeekDays $day = WeekDays::MONDAY,

      /**
       * Set the preferred UTC time to receive the notifications. Default to 3pm.
       */
      public ?string $time = '15:00',
    ) {}
}
