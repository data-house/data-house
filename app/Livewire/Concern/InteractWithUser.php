<?php 

namespace App\Livewire\Concern;

use Livewire\Attributes\Locked;

trait InteractWithUser
{
    
    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    #[Locked]
    public function getUserProperty()
    {
        return auth()->user();
    }

}
