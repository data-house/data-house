<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\CatalogFlow;
use Illuminate\Auth\Access\Response;

class CatalogFlowPolicy
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
    public function view(User $user, CatalogFlow $catalogFlow): bool
    {
        return $user->can('view', $catalogFlow->catalog);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Catalog $catalog): bool
    {
        return $user->can('update', $catalog);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CatalogFlow $catalogFlow): bool
    {
        return $user->can('update', $catalogFlow->catalog);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CatalogFlow $catalogFlow): bool
    {
        return $user->can('delete', $catalogFlow->catalog);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CatalogFlow $catalogFlow): bool
    {
        return $user->can('restore', $catalogFlow->catalog);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CatalogFlow $catalogFlow): bool
    {
        return $user->can('forceDelete', $catalogFlow->catalog);
    }
}
