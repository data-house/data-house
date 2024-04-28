<?php

namespace App\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class NoteList extends Component
{
    #[Locked]
    public $resource;

    #[Computed()]
    public function notes()
    {
        return $this->resource->notes;
    }
    
    public function render()
    {
        return view('livewire.note-list');
    }
}
