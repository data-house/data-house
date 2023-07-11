<?php

namespace App\Http\Livewire;

use App\Models\FeedbackReason;
use App\Models\FeedbackVote;
use App\Models\Question;
use Illuminate\Validation\Rules\Enum;
use Livewire\Component;

class QuestionFeedback extends Component
{
    /**
     * @var \App\Models\Question
     */
    public $question;

    public $showingDislikeModal = false;

    /**
     * @var \App\Models\QuestionFeedback
     */
    public $feedback = null;
    
    protected function rules()
    {
        return [
            'feedback.note' => 'nullable|string|min:1|max:400',
            'feedback.reason' => ['required', new Enum(FeedbackReason::class)],
        ];
    }
    
    public function mount($question)
    {
        $this->question = $question;
        $this->showingDislikeModal = false;
    }


    protected function recordFeedback(FeedbackVote $vote)
    {
        /**
         * @var \App\Models\User
         */
        $user = auth()->user();

        $existing = $this->feedback ?? $this->question->feedbacks()->author($user)->first();

        if($existing){

            if($existing->vote !== $vote){

                $existing->fill([
                    'vote' => $vote,
                    'points' => $vote->points(),
                ]);

                $existing->save();
            }

            return $existing;
        }

        return $this->question->feedbacks()->create([
            'user_id' => $user->getKey(),
            'vote' => $vote,
            'points' => $vote->points(),
        ]);
    }


    /**
     * Track a like feedback
     */
    public function like()
    {
        $this->feedback = $this->recordFeedback(FeedbackVote::LIKE);

        $this->emit('saved');

        $this->question = $this->question->fresh();
    }
    
    /**
     * Track a dislike feedback
     */
    public function dislike()
    {

        if($this->showingDislikeModal){
            $this->showingDislikeModal = false;
            $this->feedback = null;

            return;
        }

        $this->feedback = $this->recordFeedback(FeedbackVote::DISLIKE);

        $this->question = $this->question->fresh();

        $this->showingDislikeModal = true;
    }

    public function saveDislikeComment()
    {
        $this->validate();
 
        $this->feedback->save();

        $this->showingDislikeModal = false;

        $this->feedback = null;

        $this->emit('saved');
    }


    public function updatedShowingDislikeModal($value)
    {
        if(!$value){
            $this->feedback = null;
        }
    }
    
    public function render()
    {
        return view('livewire.question-feedback');
    }
}
