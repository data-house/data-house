<?php

namespace App\Livewire;

use App\Models\Note;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TakeNote extends Component
{

    #[Locked]
    public $resource;

    public $content = null;

    protected $rules = [
        'content' => 'required|min:1|max:4000',
    ];



    public function save()
    {
        $this->validate();

        $this->authorize('create', Note::class);
        
        $this->authorize('update', $this->resource);

        $this->resource->addNote($this->content);

        $this->dispatch('saved', resourceId: $this->resource->id); 

        $this->content = null;
    }

    public function render()
    {
        return view('livewire.take-note');
    }
}
