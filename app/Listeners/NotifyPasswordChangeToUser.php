<?php

namespace App\Listeners;

use App\Events\Auth\PasswordChanged;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyPasswordChangeToUser implements ShouldQueue
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
    public function handle(PasswordReset|PasswordChanged $event): void
    {
        $event->user->notify(new PasswordChangedNotification());
    }
}
