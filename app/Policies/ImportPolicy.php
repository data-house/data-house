<?php

namespace App\Policies;

use App\Models\Import;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ImportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ($user->hasPermission('import:view') ||
           $user->hasTeamPermission($user->currentTeam, 'import:view')) &&
           $user->tokenCan('import:view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Import $import): bool
    {
        return ($user->hasPermission('import:view') ||
           $user->hasTeamPermission($user->currentTeam, 'import:view')) &&
           $user->tokenCan('import:view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ($user->hasPermission('import:create') ||
           $user->hasTeamPermission($user->currentTeam, 'import:create')) &&
           $user->tokenCan('import:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Import $import): bool
    {
        return ($user->hasPermission('import:update') ||
           $user->hasTeamPermission($user->currentTeam, 'import:update')) &&
           $user->tokenCan('import:update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Import $import): bool
    {
        return ($user->hasPermission('import:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'import:delete')) &&
           $user->tokenCan('import:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Import $import): bool
    {
        return ($user->hasPermission('import:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'import:delete')) &&
           $user->tokenCan('import:delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Import $import): bool
    {
        return ($user->hasPermission('import:delete') ||
           $user->hasTeamPermission($user->currentTeam, 'import:delete')) &&
           $user->tokenCan('import:delete');
    }
}
