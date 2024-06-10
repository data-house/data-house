<?php

namespace App\Policies;

use App\Models\QuestionReview;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuestionReviewPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ($user->hasPermission('question-review:create') ||
            $user->hasTeamPermission($user->currentTeam, 'question-review:create')) &&
            $user->tokenCan('question-review:create');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, QuestionReview $questionReview): bool
    {
        $permission = ($user->hasPermission('question-review:view') ||
            $user->hasTeamPermission($user->currentTeam, 'question-review:view')) &&
            $user->tokenCan('question-review:view');

        return $permission
            && (($user->currentTeam && $user->currentTeam->canReviewQuestions() && $user->current_team_id === $questionReview->team_id)
            || $questionReview->isAssigned($user) || $questionReview->isCoordinator($user));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ($user->hasPermission('question-review:create') ||
            $user->hasTeamPermission($user->currentTeam, 'question-review:create')) &&
            $user->tokenCan('question-review:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, QuestionReview $questionReview): bool
    {
        $permission = ($user->hasPermission('question-review:view') ||
            $user->hasTeamPermission($user->currentTeam, 'question-review:view')) &&
            $user->tokenCan('question-review:view');

        return $permission
            && (($user->currentTeam && $user->currentTeam->canReviewQuestions() && $user->current_team_id === $questionReview->team_id)
            || $questionReview->isAssigned($user) || $questionReview->isCoordinator($user));
    }

    // /**
    //  * Determine whether the user can delete the model.
    //  */
    // public function delete(User $user, QuestionReview $questionReview): bool
    // {
    //     //
    // }

    // /**
    //  * Determine whether the user can restore the model.
    //  */
    // public function restore(User $user, QuestionReview $questionReview): bool
    // {
    //     //
    // }

    // /**
    //  * Determine whether the user can permanently delete the model.
    //  */
    // public function forceDelete(User $user, QuestionReview $questionReview): bool
    // {
    //     //
    // }
}
