<?php

namespace App\Livewire;

use App\Models\Note;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TakeNote extends Component
{

    #[Locked]
    public $resource;
    
    #[Locked]
    public $description;

    public $content = null;


    public function rules() 
    {
        return [
            'content' => 'required|min:1|max:4000',
        ];
    }

    public function messages() 
    {
        return [
            'content.required' => 'Please add some content to the note before continuing.',
            'content.min' => 'Please add some content to the note before continuing. At least two characters are required.',
            'content.max' => 'The note can contain up to 4000 characters.',
        ];
    }



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
