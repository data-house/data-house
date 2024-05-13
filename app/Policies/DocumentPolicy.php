<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Models\Visibility;
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
        return ($user->hasPermission('document:view') ||
           $user->hasTeamPermission($user->currentTeam, 'document:view') ||
           $user->tokenCan('document:view')) && $document->isVisibleBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ($user->hasPermission('document:create') ||
           $user->hasTeamPermission($user->currentTeam, 'document:create') ||
           $user->tokenCan('document:create'));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        if($document->uploader && $document->visibility === Visibility::PERSONAL){
            return $user->hasPermission('document:update') && $document->uploader->is($user)
                && $document->isVisibleBy($user);
        }

        if($document->team){
            return $user->hasTeamPermission($document->team, 'document:update')
                && $document->isVisibleBy($user);
        }

        return ($user->hasPermission('document:update') ||
           $user->hasTeamPermission($user->currentTeam, 'document:update') || 
           $user->tokenCan('document:update'))
           && $document->isVisibleBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Document $document): bool
    {
        return ($user->hasPermission('document:delete') ||
           $user->hasTeamPermission($document->team, 'document:delete')) &&
           $user->tokenCan('document:delete') && $document->isVisibleBy($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Document $document): bool
    {
        return ($user->hasPermission('document:delete') ||
           $user->hasTeamPermission($document->team, 'document:delete')) &&
           $user->tokenCan('document:delete') && $document->isVisibleBy($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Document $document): bool
    {       
        return ($user->hasPermission('document:delete') ||
           $user->hasTeamPermission($document->team, 'document:delete')) &&
           $user->tokenCan('document:delete') && $document->isVisibleBy($user);
    }
}
