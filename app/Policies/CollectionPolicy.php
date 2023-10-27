<?php

namespace App\Policies;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CollectionPolicy
{
    

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('collection:view') ||
           $user->hasTeamPermission($user->currentTeam, 'collection:view') ||
           $user->tokenCan('collection:view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Collection $collection): bool
    {
        // TODO: Policies need to check the collection visibility

        return $user->hasPermission('collection:view') ||
           $user->hasTeamPermission($user->currentTeam, 'collection:view') ||
           $user->tokenCan('collection:view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('collection:create') ||
           $user->hasTeamPermission($user->currentTeam, 'collection:create') ||
           $user->tokenCan('collection:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Collection $collection): bool
    {
        // TODO: Policies need to check the collection visibility

        return $user->hasPermission('collection:update') ||
           $user->hasTeamPermission($user->currentTeam, 'collection:update') ||
           $user->tokenCan('collection:update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Collection $collection): bool
    {
        // TODO: Policies need to check the collection visibility

        return $user->hasPermission('collection:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'collection:delete') ||
           $user->tokenCan('collection:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Collection $collection): bool
    {
        // TODO: Policies need to check the collection visibility

        return $user->hasPermission('collection:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'collection:delete') ||
           $user->tokenCan('collection:delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Collection $collection): bool
    {
        // TODO: Policies need to check the collection visibility
        
        return $user->hasPermission('collection:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'collection:delete') ||
           $user->tokenCan('collection:delete');
    }
}
