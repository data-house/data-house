<?php

namespace App\Policies;

use App\Models\Catalog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CatalogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Catalog $catalog): bool
    {
        return ($user->hasPermission('catalog:view') ||
           $user->hasTeamPermission($user->currentTeam, 'catalog:view')) && $catalog->isVisibleBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('catalog:create') ||
           $user->hasTeamPermission($user->currentTeam, 'catalog:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Catalog $catalog): bool
    {
        return ($user->hasPermission('catalog:update') ||
           $user->hasTeamPermission($user->currentTeam, 'catalog:update')) && $catalog->isVisibleBy($user) && 
           (($user->currentTeam && $user->currentTeam->is($catalog->team)) || $user->is($catalog->user));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Catalog $catalog): bool
    {
        return ($user->hasPermission('catalog:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'catalog:delete')) && $catalog->isVisibleBy($user) && 
           (($user->currentTeam && $user->currentTeam->is($catalog->team)) || $user->is($catalog->user));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Catalog $catalog): bool
    {
        return ($user->hasPermission('catalog:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'catalog:delete')) && $catalog->isVisibleBy($user) && 
           (($user->currentTeam && $user->currentTeam->is($catalog->team)) || $user->is($catalog->user));
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Catalog $catalog): bool
    {
        return ($user->hasPermission('catalog:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'catalog:delete')) && $catalog->isVisibleBy($user) && 
           (($user->currentTeam && $user->currentTeam->is($catalog->team)) || $user->is($catalog->user));
    }
}
