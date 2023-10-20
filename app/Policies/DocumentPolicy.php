<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ($user->hasPermission('document:view') ||
           $user->hasTeamPermission($user->currentTeam, 'document:view')) &&
           $user->tokenCan('document:view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Document $document): bool
    {
        // TODO: check user, team and visibility of document

        return ($user->hasPermission('document:view') ||
           $user->hasTeamPermission($user->currentTeam, 'document:view')) &&
           $user->tokenCan('document:view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ($user->hasPermission('document:create') ||
           $user->hasTeamPermission($user->currentTeam, 'document:create')) &&
           $user->tokenCan('document:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        // TODO: check user, team and visibility of document

        return ($user->hasPermission('document:update') ||
           $user->hasTeamPermission($user->currentTeam, 'document:update')) &&
           $user->tokenCan('document:update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Document $document): bool
    {
        // TODO: check user, team and visibility of document

        return ($user->hasPermission('document:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'document:delete')) &&
           $user->tokenCan('document:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Document $document): bool
    {
        // TODO: check user, team and visibility of document

        return ($user->hasPermission('document:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'document:delete')) &&
           $user->tokenCan('document:delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        // TODO: check user, team and visibility of document
        
        return ($user->hasPermission('document:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'document:delete')) &&
           $user->tokenCan('document:delete');
    }
}
