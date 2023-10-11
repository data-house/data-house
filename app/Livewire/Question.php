<?php

namespace App\Livewire;

use Livewire\Component;

class Question extends Component
{
    /**
     * @var \App\Models\Question
     */
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
