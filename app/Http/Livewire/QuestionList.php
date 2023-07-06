<?php

namespace App\Http\Livewire;

use App\Copilot\CopilotResponse;
use App\Models\Document;
use Livewire\Component;

class QuestionList extends Component
{
    /**
     * @var \App\Models\Document
     */
    public $document;

    public $questions;

    public $currentlyAskingQuestion;

    public function __construct($document)
    {
        $this->document = $document;
    }

    protected function getListeners()
    {
        return [
            'copilot_asking' => 'handleAsking',
            'copilot_asking' => '$refresh',
            'copilot_answer' => 'handleAnswer',
        ];
    }

    public function handleAsking($question)
    {
        $this->currentlyAskingQuestion = $question;
    }

    public function handleAnswer()
    {
        $this->currentlyAskingQuestion = null;
    }


    public function render()
    {
        $this->questions = $this->document->questions()->answered()->get();

        return view('livewire.question-list');
    }
}
