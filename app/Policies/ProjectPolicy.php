<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ($user->hasPermission('project:view') ||
           $user->hasTeamPermission($user->currentTeam, 'project:view')) &&
           $user->tokenCan('project:view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return ($user->hasPermission('project:view') ||
           $user->hasTeamPermission($user->currentTeam, 'project:view')) &&
           $user->tokenCan('project:view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ($user->hasPermission('project:create') ||
           $user->hasTeamPermission($user->currentTeam, 'project:create')) &&
           $user->tokenCan('project:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        return ($user->hasPermission('project:update') ||
           $user->hasTeamPermission($user->currentTeam, 'project:update')) &&
           $user->tokenCan('project:update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return ($user->hasPermission('project:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'project:delete')) &&
           $user->tokenCan('project:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return ($user->hasPermission('project:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'project:delete')) &&
           $user->tokenCan('project:delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return ($user->hasPermission('project:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'project:delete')) &&
           $user->tokenCan('project:delete');
    }
}
