<?php

namespace App\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Component;

class Note extends Component
{

    #[Locked]
    public $note;


    public $isEditing = false;
    
    public $trashed = false;

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


    public function remove()
    {
        $this->authorize('delete', $this->note);
        
        $this->note->delete();

        $this->note = null;

        $this->trashed = true;

        $this->dispatch('removed');
    }

    public function toggleEditMode()
    {
        if($this->isEditing){
            $this->isEditing = false;
    
            $this->content = null;

            return;
        }

        $this->isEditing = true;

        $this->content = $this->note->content;
    }

    public function save()
    {
        $this->validate();

        $this->authorize('update', $this->note);
        
        $this->note->content = $this->content;

        $this->note->save();

        $this->isEditing = false;

        $this->content = null;
    }


    public function render()
    {
        return view('livewire.note');
    }
}
