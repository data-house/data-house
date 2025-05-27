<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Catalog;
use App\Models\CatalogEntry;
use Illuminate\Auth\Access\Response;

class CatalogEntryPolicy
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
    public function view(User $user, CatalogEntry $catalogEntry): bool
    {
        return $user->can('view', $catalogEntry->catalog);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create', Catalog::class);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CatalogEntry $catalogEntry): bool
    {
        return $user->can('update', $catalogEntry->catalog);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CatalogEntry $catalogEntry): bool
    {
        return $user->can('delete', $catalogEntry->catalog);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CatalogEntry $catalogEntry): bool
    {
        return $user->can('restore', $catalogEntry->catalog);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CatalogEntry $catalogEntry): bool
    {
        return $user->can('forceDelete', $catalogEntry->catalog);
    }
}
