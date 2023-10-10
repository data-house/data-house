<?php

namespace App\Livewire;

use App\Models\FeedbackReason;
use App\Models\FeedbackVote;
use App\Models\Question;
use App\Models\QuestionFeedback as ModelsQuestionFeedback;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Locked;
use Livewire\Component;

class QuestionFeedback extends Component
{
    /**
     * @var \App\Models\Question
     */
    public Question $question;

    public $showingCommentModal = false;

    /**
     * @var string
     */
    public $note = null;

    /**
     * @var \App\Models\FeedbackReason
     */
    public $reason = null;

    public ?ModelsQuestionFeedback $feedback;
    
    protected function rules()
    {
        return [
            'note' => 'nullable|string|min:1|max:400',
            'reason' => ['required', new Enum(FeedbackReason::class)],
        ];
    }
    
    public function mount($question)
    {
        /**
         * @var \App\Models\User
         */
        $user = auth()->user();

        $this->question = $question;
        $this->showingCommentModal = false;

        $this->feedback = $this->question->feedbacks()->author($user)->first();
        $this->note = $this->feedback?->note;
        $this->reason = $this->feedback?->reason?->value;
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
                    'note' => $this->note,
                    'reason' => $this->reason,
                ]);

                $existing->save();
            }

            return $existing;
        }

        return $this->feedback = $this->question->feedbacks()->create([
            'user_id' => $user->getKey(),
            'vote' => $vote,
            'points' => $vote->points(),
            'note' => $this->note,
            'reason' => $this->reason,
        ]);
    }


    /**
     * Track a like feedback
     */
    public function recordPositiveFeedback()
    {
        $this->recordFeedback(FeedbackVote::LIKE);

        $this->dispatch('saved');

        $this->question = $this->question->fresh();
    }
    
    /**
     * Track a dislike feedback
     */
    public function recordNeutralFeedback()
    {

        if($this->showingCommentModal){
            $this->showingCommentModal = false;
            $this->note = null;
            $this->reason = null;

            return;
        }

        $this->recordFeedback(FeedbackVote::IMPROVABLE);

        $this->question = $this->question->fresh();

        $this->showingCommentModal = true;

        $this->dispatch('saved');
    }
    
    /**
     * Track a dislike feedback
     */
    public function recordNegativeFeedback()
    {

        if($this->showingCommentModal){
            $this->showingCommentModal = false;
            $this->note = null;
            $this->reason = null;

            return;
        }

        $this->recordFeedback(FeedbackVote::DISLIKE);

        $this->question = $this->question->fresh();

        $this->showingCommentModal = true;

        $this->dispatch('saved');
    }

    public function saveComment()
    {
        $this->validate();
 
        $this->feedback->reason = $this->reason;
        $this->feedback->note = $this->note;
        $this->feedback->save();


        $this->showingCommentModal = false;

        $this->dispatch('saved');
    }


    public function updatedShowingDislikeModal($value)
    {
        if(!$value){
            $this->note = null;
            $this->reason = null;
        }
    }
    
    public function render()
    {
        return view('livewire.question-feedback');
    }
}
