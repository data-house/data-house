<?php

namespace App\Livewire;


trait CurrentUserProperty
{
    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return auth()->user();
    }

}
