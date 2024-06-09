<?php

namespace App\Livewire\QuestionReview;

use App\Actions\Review\RequestQuestionReview;
use App\Data\Notifications\ActivitySummaryNotificationData;
use App\Livewire\Concern\InteractWithUser;
use App\Models\Question;
use App\Models\Team;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use LivewireUI\Slideover\SlideoverComponent;

class QuestionReviewSlideover extends SlideoverComponent
{

    use InteractWithUser;


    #[Locked]
    public $questionId;

    public $editingForm = [
        'teams' => [],
        'note' => null,
    ];


    public function mount($question)
    {
        abort_unless($this->user, 401);

        $this->questionId = $question;
    
        $this->editingForm['teams'] = [$this->reviewerTeams->first()->getKey()];
    }

    #[Computed()]
    public function question()
    {
        return Question::query()->ViewableBy($this->user)->whereUuid($this->questionId)->first();
    }

    #[Computed()]
    public function reviews()
    {
        return $this->question->reviews()->with('notes', 'assignees')->get();
    }
    
    #[Computed()]
    public function hasReviews()
    {
        return $this->question->reviews()->exists();
    }

    #[Computed()]
    public function reviewerTeams()
    {
        return Team::query()
            ->questionReviewers()
            ->get();
    }

    public function rules() 
    {
        return [
            'editingForm.teams' => 'required|array|min:1|exists:teams,id',
        ];
    }

    public function messages() 
    {
        return [
            'editingForm.teams.required' => 'Please select a team.',
            'editingForm.teams.min' => 'Please select at least one team.',
            'editingForm.teams.exists' => 'Selected team is invalid.',
        ];
    }

    
    public function requestReview()
    {
        $this->validate();

        $askReview = app()->make(RequestQuestionReview::class);

        $this->reviewerTeams->each(fn($team) => $askReview($this->question, $team));

        $this->dispatch('review-requested');

        unset($this->reviews);
        unset($this->hasReviews);
    }

    
    public function render()
    {
        return view('livewire.question-review.question-review-slideover');
    }
}
