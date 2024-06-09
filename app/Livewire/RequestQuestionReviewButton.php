<?php

namespace App\Livewire;

use App\Actions\Review\RequestQuestionReview;
use App\Livewire\Concern\InteractWithUser;
use App\Models\Question;
use App\Models\QuestionReview;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RequestQuestionReviewButton extends Component
{
    use InteractWithUser;

    #[Locked]
    public $questionId;

    public function mount(Question $question)
    {
        $this->questionId = $question->uuid;
    }


    #[Computed()]
    public function question()
    {
        return Question::query()->ViewableBy($this->user)->whereUuid($this->questionId)->first();
    }


    #[Computed()]
    public function isUnderReview(): bool
    {
        return $this->question->reviews()->underReview()->exists();
    }
    
    #[Computed()]
    public function isApproved(): bool
    {
        return $this->question->reviews()->approved()->exists();
    }
    
    #[Computed()]
    public function isApprovedWithChanges(): bool
    {
        return $this->question->reviews()->approvedWithChanges()->exists();
    }
    
    #[Computed()]
    public function isRejected(): bool
    {
        return $this->question->reviews()->rejected()->exists();
    }
    
    #[Computed()]
    public function reviewerTeamNames()
    {
        return Team::query()
            ->questionReviewers()
            ->get()
            ->map
            ->name;
    }


    public function render()
    {
        return view('livewire.request-question-review-button');
    }
}
