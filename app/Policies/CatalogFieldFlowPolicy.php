<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Catalog;
use App\Models\CatalogFieldFlow;
use Illuminate\Auth\Access\Response;

class CatalogFieldFlowPolicy
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
    public function view(User $user, CatalogFieldFlow $catalogFlow): bool
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
    public function update(User $user, CatalogFieldFlow $catalogFlow): bool
    {
        return $user->can('update', $catalogFlow->catalog);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CatalogFieldFlow $catalogFlow): bool
    {
        return $user->can('delete', $catalogFlow->catalog);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CatalogFieldFlow $catalogFlow): bool
    {
        return $user->can('restore', $catalogFlow->catalog);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CatalogFieldFlow $catalogFlow): bool
    {
        return $user->can('forceDelete', $catalogFlow->catalog);
    }
}
