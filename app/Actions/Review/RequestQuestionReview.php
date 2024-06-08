<?php

namespace App\Actions\Review;

use App\Data\ReviewSettings;
use App\Models\User;
use App\Models\Question;
use App\Models\QuestionReview;
use App\Models\Team;
use App\Notifications\QuestionReviewRequested;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Nette\InvalidStateException;

class RequestQuestionReview
{
    /**
     * Request a question review
     *
     * @param \App\Models\Question $question
     * @param \App\Models\Team $team The team that is asked the review
     */
    public function __invoke(Question $question, Team $team, string $comment = null, ?User $user = null): QuestionReview
    {
        $user = $user ?? auth()->user();

        if(is_null($user)){
            throw new InvalidArgumentException(__('User not recognized. Authentication is required to request a review'));
        }

        $reviewSettings = $team->settings?->review ?? new ReviewSettings();

        if(!$reviewSettings->questionReview){
            throw new InvalidArgumentException(__('Team is not allowed to review questions.'));
        }

        if(empty($reviewSettings->assignableUserRoles)){
            throw new InvalidArgumentException(__('Team has no eligible reviewers.'));
        }
        
        $reviewers = $team
            ->users()
            ->wherePivotIn('role', $reviewSettings->assignableUserRoles)
            ->get();

        if($reviewers->isEmpty()){
            throw new InvalidStateException(__('Team has no eligible reviewers.'));
        }

        if($question->reviews()->underReview()->exists()){
            throw new InvalidStateException(__('A review is still in progress.'));
        }
        
        $review = DB::transaction(function() use ($user, $reviewers, $question, $team, $comment) {
            $review = $question->reviews()->create([
                'user_id' => $user->getKey(),
                'team_id' => $team->getKey(),
            ]);
    
            $review->assignees()->attach($reviewers->modelKeys());

            if($comment){
                $review->addNote($comment);
            }

            return $review;
        });

        Notification::send($reviewers, new QuestionReviewRequested($review));
        
        return $review;
    }

}
