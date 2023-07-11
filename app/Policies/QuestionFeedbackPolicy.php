<?php

namespace App\Policies;

use App\Models\QuestionFeedback;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuestionFeedbackPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('question-feedback:view') ||
           $user->hasTeamPermission($user->currentTeam, 'question-feedback:view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, QuestionFeedback $questionFeedback): bool
    {
        return $user->hasPermission('question-feedback:view') ||
           $user->hasTeamPermission($user->currentTeam, 'question-feedback:view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('question-feedback:create') ||
           $user->hasTeamPermission($user->currentTeam, 'question-feedback:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, QuestionFeedback $questionFeedback): bool
    {
        return $user->hasPermission('question-feedback:update') ||
           $user->hasTeamPermission($user->currentTeam, 'question-feedback:update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, QuestionFeedback $questionFeedback): bool
    {
        return $user->hasPermission('question-feedback:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'question-feedback:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, QuestionFeedback $questionFeedback): bool
    {
        return $user->hasPermission('question-feedback:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'question-feedback:delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, QuestionFeedback $questionFeedback): bool
    {
        return $user->hasPermission('question-feedback:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'question-feedback:delete');
    }
}
