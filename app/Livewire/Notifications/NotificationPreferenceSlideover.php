<?php

namespace App\Livewire\Notifications;

use App\Data\Notifications\ActivitySummaryNotificationData;
use App\Livewire\Concern\InteractWithUser;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use LivewireUI\Slideover\SlideoverComponent;

class NotificationPreferenceSlideover extends SlideoverComponent
{

    use InteractWithUser;


    /**
     * @var bool
     */
    public $snooze;

    /**
     * @var bool
     */
    public $activitySummaryEnabled;


    public function mount()
    {
        abort_unless($this->user, 401);

        $this->snooze = $this->user->notification_settings?->snooze ?? false;

        $this->activitySummaryEnabled = $this->user->notification_settings?->activitySummary?->enable ?? (new ActivitySummaryNotificationData())->enable;
    }

    public function updated($property)
    { 
        if ($property === 'snooze') {
            $this->user->notification_settings->snooze = $this->snooze;

            $this->user->save(); // this is probably risky so a dynamic casting on the preferences table might work better

            $this->dispatch('notification');
        }
        
        if ($property === 'activitySummaryEnabled') {

            $currentSummarySettings = $this->user->notification_settings?->activitySummary ?? new ActivitySummaryNotificationData();

            $currentSummarySettings->enable = $this->activitySummaryEnabled;

            $this->user->notification_settings->activitySummary = $currentSummarySettings;

            $this->user->save();
        }
    }

    
    public function render()
    {
        return view('livewire.notifications.notifications-preferences');
    }
}
