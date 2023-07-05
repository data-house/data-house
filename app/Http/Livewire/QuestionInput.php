<?php

namespace App\Http\Livewire;

use App\Copilot\CopilotResponse;
use App\Models\Document;
use Livewire\Component;
use \Illuminate\Support\Str;

class QuestionInput extends Component
{
    /**
     * @var \App\Models\Document
     */
    public $document;

    public $question;

    public $length = 0;

    public $exceededMaximumLength = false;

    public $askingQuestion = false;

    protected $rules = [
        'question' => 'required|min:10|max:200',
    ];

    public function __construct($document)
    {
        $this->document = $document;
    }
    
    protected function getListeners()
    {
        return ['copilot_answer' => 'handleAnswer'];
    }


    public function makeQuestion()
    {
        $this->validate();

        $pendingQuestion = $this->document->question($this->question);

        $this->emit('copilot_asking', $pendingQuestion->uuid);
    }

    public function handleAnswer()
    {
        $this->question = '';
    }


    public function render()
    {

        $this->length = Str::length($this->question ?? '');

        $this->exceededMaximumLength = $this->length > config('copilot.limits.question_length');


        return view('livewire.question-input');
    }
}
