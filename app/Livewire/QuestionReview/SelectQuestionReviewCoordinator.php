<?php

namespace App\Livewire\QuestionReview;

use App\Livewire\Concern\InteractWithUser;
use App\Models\QuestionReview;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SelectQuestionReviewCoordinator extends Component
{
    use InteractWithUser;

    #[Locked]
    public $reviewId;

    public $selectedCoordinator;

    public function mount($review)
    {
        abort_unless($this->user, 401);

        $this->reviewId = $review->getKey();

        $this->selectedCoordinator = $review->coordinator_user_id;
    
    }

    #[Computed()]
    public function review()
    {
        return QuestionReview::find($this->reviewId);
    }
    
    #[Computed()]
    public function coordinator()
    {
        return $this->review->coordinator;
    }
    
    #[Computed()]
    public function availableCoordinators()
    {
        return $this->review->assignees;
    }


    public function assignMyselfAsCoordinator()
    {
        $this->review->coordinator_user_id = $this->user->getKey();

        $this->selectedCoordinator = $this->user->getKey();

        $this->review->save();

        unset($this->review);
        unset($this->coordinator);

        $this->dispatch('closedropdown');
    }

    public function removeCoordinator()
    {
        $this->review->coordinator_user_id = $this->selectedCoordinator = null;

        $this->review->save();

        unset($this->review);
        unset($this->coordinator);

        $this->selectedCoordinator = null;

        $this->dispatch('closedropdown');
    }
    
    public function render()
    {
        return view('livewire.question-review.select-question-review-coordinator');
    }
}
