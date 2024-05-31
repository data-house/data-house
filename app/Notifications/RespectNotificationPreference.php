<?php

namespace App\Notifications;

use App\Models\User;

trait RespectNotificationPreference
{

    public function channels()
    {
        return ['mail', 'database'];
    }

    protected function shouldSend($notifiable)
    {
        if(!$notifiable instanceof User){
            return false;
        }

        $preferences = $notifiable->notification_settings;

        if(!$preferences->notifyActivity){
            return false;
        }

        if(method_exists($this, 'shouldSendUsing')){
            return $this->shouldSendUsing($notifiable, $preferences);
        }
        
        return true;
    }


    protected function filterDeliveryChannels($notifiable, array $channels)
    {
        $preferences = $notifiable->notification_settings;

        $allowedChannels = array_merge([], $channels);

        if($preferences->snooze || !$preferences->enableMailNotifications){
            $allowedChannels = array_diff($allowedChannels, ['mail']);
        }

        return array_values($allowedChannels);
    }


    
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if(!$this->shouldSend($notifiable)){
            return [];
        }

        return $this->filterDeliveryChannels($notifiable, $this->channels());
    }
}
