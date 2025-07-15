<?php

namespace App\Policies;

use App\Models\Catalog;
use App\Models\CatalogField;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CatalogFieldPolicy
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
    public function view(User $user, CatalogField $catalogField): bool
    {
        return $user->can('view', $catalogField->catalog);
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
    public function update(User $user, CatalogField $catalogField): bool
    {
        return $user->can('update', $catalogField->catalog);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CatalogField $catalogField): bool
    {
        return $user->can('delete', $catalogField->catalog);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CatalogField $catalogField): bool
    {
        return $user->can('restore', $catalogField->catalog);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CatalogField $catalogField): bool
    {
        return $user->can('forceDelete', $catalogField->catalog);
    }
}
