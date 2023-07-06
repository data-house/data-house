<?php

namespace App\Http\Livewire;

use App\Models\Question;
use Livewire\Component;

class CurrentQuestion extends Component
{
    /**
     * @var \App\Models\Document
     */
    public $document;
    
    public ?Question $question;

    public ?string $ref = null;

    protected $queryString = [
        'ref',
    ];
    
    public function __construct($document)
    {
        $this->document = $document;
    }


    protected function getListeners()
    {
        return ['copilot_asking' => 'handleNewQuestion'];
    }


    public function handleNewQuestion(string $uuid)
    {
        $this->question = Question::whereUuid($uuid)->first();
        $this->ref = $uuid;
    }


    public function render()
    {
        $this->question = $this->question ?? $this->document->questions()->askedBy(auth()->user())->pending()->first();
        $this->ref = $this->question?->uuid;

        if($this->question && !$this->question->isPending()){
            $this->emit('copilot_answer', $this->question->uuid);
        }

        return view('livewire.current-question');
    }
}
