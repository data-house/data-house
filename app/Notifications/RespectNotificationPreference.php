<?php

namespace App\Notifications;

use App\Models\User;

trait RespectNotificationPreference
{

    /**
     * Check if notification should be sent to the user using a specific channel
     */
    public function shouldSend($notifiable, $channel): bool
    {
        if(!$notifiable instanceof User){
            return false;
        }

        $preferences = $notifiable->notification_settings;

        if(!$preferences->notifyActivity){
            return false;
        }

        if(method_exists($this, 'shouldSendUsing')){
            return $this->shouldSendUsing($notifiable, $preferences, $channel);
        }

        if(($preferences->snooze || !$preferences->enableMailNotifications) && $channel === 'mail'){
            return false;
        }
        
        return true;
    }
}
