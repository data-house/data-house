<?php

namespace App\Livewire\Notifications;

use App\Livewire\Concern\InteractWithUser;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    use InteractWithUser;

    /**
     * @var int
     */
    #[Locked]
    public $lastChecked;

    public function mount()
    {
        $this->lastChecked = $this->user->unreadNotifications()->count();
    }

    #[Computed()]
    public function hasUnreadNotifications()
    {
        return $this->unreadNotificationsCount > 0;
    }
    
    #[Computed()]
    #[On("notifications")]
    public function unreadNotificationsCount()
    {
        return $this->user->unreadNotifications()->count();
    }

    public function pollingBeat()
    {
        $count = $this->user->unreadNotifications()->count();

        if($count != $this->lastChecked){
            $this->lastChecked = $count;
            $this->dispatch('notifications');
        }
    }
    
    public function render()
    {
        return view('livewire.notifications.notification-bell');
    }
}
