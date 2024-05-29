<?php

namespace App\Livewire\Notifications;

use App\Livewire\Concern\InteractWithUser;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationsList extends Component
{
    use InteractWithUser;


    public function mount()
    {
        abort_unless($this->user, 401);
    }
    
    #[Computed()]
    #[On("notifications")]
    public function notifications()
    {
        return $this->user->notifications;
    }


    public function markAllAsRead()
    {
        abort_unless($this->user, 401);

        $this->user->unreadNotifications->each->markAsRead();

        $this->dispatch('notifications');
    }

    public function markUnread($id)
    {
        abort_unless($this->user, 401);

        $this->user->notifications()->find($id)?->markAsUnread();

        $this->dispatch('notifications');
    }
    
    public function markRead($id)
    {

        abort_unless($this->user, 401);

        $this->user->notifications()->find($id)?->markAsRead();

        $this->dispatch('notifications');
    }
    
    public function render()
    {
        return view('livewire.notifications.notifications-list');
    }
}
