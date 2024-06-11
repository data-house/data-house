<?php

namespace App\Livewire;

use App\Data\ReviewSettings;
use App\Livewire\Concern\InteractWithUser;
use App\Models\QuestionReview;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SelectQuestionReviewAssignee extends Component
{
    use InteractWithUser;

    #[Locked]
    public $reviewId;

    public $selectedAssignees = [];


    public function rules() 
    {
        return [
            'selectedAssignees' => 'array|max:10|exists:users,id', // todo: ensure that users exists in the team of the review
        ];
    }

    public function mount($review)
    {
        abort_unless($this->user, 401);

        $this->reviewId = $review?->getKey();
    
        $this->selectedAssignees = $this->review->assignees->modelKeys();
    }

    #[Computed()]
    public function review()
    {
        return QuestionReview::find($this->reviewId);
    }
    
    #[Computed()]
    public function assignees()
    {
        return $this->review->assignees;
    }
    
    #[Computed()]
    public function availableAssignees()
    {
        $reviewSettings = $this->review->team->settings?->review ?? new ReviewSettings();

        if(!$reviewSettings->questionReview){
            return collect();
        }

        if(empty($reviewSettings->assignableUserRoles)){
            return collect();
        }
        
        return $this->review->team
            ->users()
            ->wherePivotIn('role', $reviewSettings->assignableUserRoles)
            ->get();
    }


    public function assignMyself()
    {

        $this->review->assignees()->attach(auth()->user());

        $this->selectedAssignees[] = auth()->user()->getKey();

        unset($this->review);
        unset($this->assignees);
    }
    
    public function removeAssignees()
    {
        $this->review->assignees()->detach($this->assignees->pluck('id'));

        $this->selectedAssignees = [];

        unset($this->review);
        unset($this->assignees);

        $this->dispatch('closedropdown');
    }

    public function save()
    {
        // save currently selected assignees

        $this->validate();

        $ids = array_map('intval', $this->selectedAssignees);

        $this->review->assignees()->sync($ids);

        unset($this->review);
        unset($this->assignees);

        $this->dispatch('closedropdown');
    }

    public function render()
    {
        return view('livewire.select-question-review-assignee');
    }
}
