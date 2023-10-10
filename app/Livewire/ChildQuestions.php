<?php

namespace App\Livewire;

use Livewire\Component;

class ChildQuestions extends Component
{
    /**
     * @var \App\Models\Question
     */
    public $question;

    public function mount($question)
    {
        $this->question = $question;
    }
    
    public function render()
    {
        return view('livewire.child-questions');
    }
}
