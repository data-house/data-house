<?php

namespace App\Livewire;

use App\Livewire\Concern\InteractWithUser;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Question extends Component
{
    use InteractWithUser;
    
    /**
     * @var \App\Models\Question
     */
    #[Locked]
    public $question;

    public $poll;

    public function mount($question, $poll)
    {
        $this->question = $question;
        $this->poll = $poll;
    }


    public function render()
    {
        return view('livewire.question');
    }
}
