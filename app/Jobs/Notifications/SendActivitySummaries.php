<?php

namespace App\Jobs\Notifications;

use App\Data\Notifications\ActivitySummaryNotificationData;
use App\Models\NotificationFrequency;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendActivitySummaries implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $currentDate = now();

        User::query()
            ->whereNull('notification_settings->activitySummary')
            ->orWhere('notification_settings->activitySummary->enable', 'true')
            ->each(function($user) use ($currentDate) {

                /**
                 * @var \App\Data\Notifications\ActivitySummaryNotificationData
                 */
                $preference = $user->notification_settings?->activitySummary ?? new ActivitySummaryNotificationData();

                if(!$preference->enable){
                    return;
                }

                if($preference->frequency !== NotificationFrequency::DAILY
                    && !$currentDate->isDayOfWeek($preference->day->value)){
                    return;
                }


                if(!Carbon::createFromTimeString($preference->time)->isCurrentHour()){
                    return;
                }
                
                dispatch(new SendActivitySummary($user));

            });
    }
}
