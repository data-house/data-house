<?php

namespace App\Livewire;

use App\Copilot\CopilotManager;
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

    public $guided = false;

    public $dailyQuestionLimit = null;

    public function rules() 
    {
        return [
            'question' => 'required|min:2|max:'.config('copilot.limits.question_length'),
        ];
    }

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

    public function switchToGuided()
    {
        $this->guided = true;
    }

    public function switchToFreeForm()
    {
        $this->guided = false;
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

        $this->dailyQuestionLimit = CopilotManager::questionLimitFor(auth()->user());

        return view('livewire.multiple-question-input');
    }
}
