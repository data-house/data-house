<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('note:view') ||
           $user->hasTeamPermission($user->currentTeam, 'note:view') ||
           $user->tokenCan('note:view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Note $note): bool
    {
        return $note->user_id == $user->getKey() && (($user->hasPermission('note:view') ||
           $user->hasTeamPermission($user->currentTeam, 'note:view')) &&
           $user->tokenCan('note:view'));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ($user->hasPermission('note:view') ||
           $user->hasTeamPermission($user->currentTeam, 'note:view')) &&
           $user->tokenCan('note:view');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Note $note): bool
    {
        return $note->user_id == $user->getKey() && (($user->hasPermission('note:update') ||
           $user->hasTeamPermission($user->currentTeam, 'note:update')) &&
           $user->tokenCan('note:update'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Note $note): bool
    {
        return $note->user_id == $user->getKey() && (($user->hasPermission('note:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'note:delete')) &&
           $user->tokenCan('note:delete'));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Note $note): bool
    {
        return $note->user_id == $user->getKey() && (($user->hasPermission('note:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'note:delete')) &&
           $user->tokenCan('note:delete'));
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Note $note): bool
    {
        return $note->user_id == $user->getKey() && (($user->hasPermission('note:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'note:delete')) &&
           $user->tokenCan('note:delete'));
    }
}
