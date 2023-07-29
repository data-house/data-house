<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;

class MultipleQuestionInput extends Component
{

    public $strategy; 

    /**
     * @var \App\Models\Collection|null
     */
    public $collection;

    public $question;

    public $length = 0;

    public $exceededMaximumLength = false;

    public $askingQuestion = false;

    protected $rules = [
        'question' => 'required|min:10|max:200',
    ];

    public function mount($strategy, $collection = null)
    {
        // :target="DocumentSelection || Collection || Document || Askable"
        $this->strategy = $strategy;
        $this->collection = $collection;
    }
    
    protected function getListeners()
    {
        return ['copilot_answer' => 'handleAnswer'];
    }


    public function makeQuestion()
    {
        
    }

    public function handleAnswer()
    {
        $this->question = '';
    }


    public function render()
    {

        $this->length = Str::length($this->question ?? '');

        $this->exceededMaximumLength = $this->length > config('copilot.limits.question_length');

        return view('livewire.multiple-question-input');
    }
}
