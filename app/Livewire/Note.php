<?php

namespace App\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Component;

class Note extends Component
{

    #[Locked]
    public $note;



    public function remove()
    {
        $this->authorize('delete', $this->note);
        
        $this->note->delete();

        $this->dispatch('removed');
    }


    public function render()
    {
        return view('livewire.note');
    }
}
