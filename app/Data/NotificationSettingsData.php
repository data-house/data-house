<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Data\Notifications\ActivitySummaryNotificationData;

class NotificationSettingsData extends Data
{
    public function __construct(
      public bool $enableMailNotifications = true,
      public bool $snooze = false,
      public bool $notifyActivity = true,
      public ?ActivitySummaryNotificationData $activitySummary = null,
    ) {}
}
