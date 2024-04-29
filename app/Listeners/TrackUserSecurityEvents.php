<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class TrackUserSecurityEvents
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $eventName = str(basename(get_class($event)))->kebab()->toString();

        Log::info("User " . basename(get_class($event)), [
            'user' => $event->user->id,
        ]);

        activity('security')
            ->performedOn($event->user)
            ->event($eventName)
            ->log("activity.{$eventName}");
    }
}
