<?php

namespace App\Livewire;

use App\Copilot\CopilotManager;
use App\Copilot\CopilotResponse;
use App\Models\Document;
use Livewire\Component;
use \Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Nette\InvalidStateException;

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

    public $dailyQuestionLimit = null;

    public function rules() 
    {
        return [
            'question' => 'required|min:2|max:'.config('copilot.limits.question_length'),
        ];
    }

    public function mount($document)
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

        try {
            $pendingQuestion = $this->document->question($this->question);
    
            $this->dispatch('copilot_asking', $pendingQuestion->uuid);
        } catch (InvalidStateException $th) {
            throw ValidationException::withMessages(['question' => $th->getMessage()]);
        }
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

        return view('livewire.question-input');
    }
}
