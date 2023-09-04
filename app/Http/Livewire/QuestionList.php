<?php

namespace App\Http\Livewire;

use App\Copilot\CopilotResponse;
use App\Models\Document;
use App\Models\Visibility;
use Livewire\Component;

class QuestionList extends Component
{
    /**
     * @var \App\Models\Document
     */
    public $document;

    public $currentlyAskingQuestion;

    public function __construct($document)
    {
        $this->document = $document;
    }

    protected function getListeners()
    {
        return [
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
        $userQuestions = $this->document->questions()
            ->with('user')
            ->answered()
            ->askedBy(auth()->user())
            ->orderBy('created_at', 'DESC')
            ->get();
        
        $otherQuestions = $this->document->questions()
            ->with('user')
            ->answered()
            ->notAskedBy(auth()->user())
            ->viewableBy(auth()->user())
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('livewire.question-list', [
            'userQuestions' => $userQuestions,
            'otherQuestions' => $otherQuestions,
        ]);
    }
}
