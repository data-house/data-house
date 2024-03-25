<?php

namespace App\Policies;

use App\Models\Star;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StarPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ($user->hasPermission('star:view') ||
           $user->hasTeamPermission($user->currentTeam, 'star:view')) &&
           $user->tokenCan('star:view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Star $star): bool
    {
        return $star->user_id == $user->getKey() && (($user->hasPermission('star:view') ||
           $user->hasTeamPermission($user->currentTeam, 'star:view')) &&
           $user->tokenCan('star:view'));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ($user->hasPermission('star:create') ||
           $user->hasTeamPermission($user->currentTeam, 'star:create')) &&
           $user->tokenCan('star:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Star $star): bool
    {
        return $star->user_id == $user->getKey() && (($user->hasPermission('star:update') ||
           $user->hasTeamPermission($user->currentTeam, 'star:update')) &&
           $user->tokenCan('star:update'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Star $star): bool
    {
        return $star->user_id == $user->getKey() && (($user->hasPermission('star:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'star:delete')) &&
           $user->tokenCan('star:delete'));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Star $star): bool
    {
        return $star->user_id == $user->getKey() && (($user->hasPermission('star:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'star:delete')) &&
           $user->tokenCan('star:delete'));
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Star $star): bool
    {
        return $star->user_id == $user->getKey() && (($user->hasPermission('star:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'star:delete')) &&
           $user->tokenCan('star:delete'));
    }
}
