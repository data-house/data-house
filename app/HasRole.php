<?php

namespace App;

use Illuminate\Support\Str;
use Laravel\Jetstream\Jetstream;

trait HasRole
{

    /**
     * Get the user's role.
     *
     * @return \Laravel\Jetstream\Role|null
     */
    public function userRole()
    {
        $role = $this->role;

        return $role ? Jetstream::findRole($role->value) : null;
    }

    /**
     * Determine if the user has the given role.
     *
     * @param  string  $role
     * @return bool
     */
    public function hasRole(string $role)
    {
        return optional($this->userRole())->key === $role;
    }

    /**
     * Get the user's permissions for the given role.
     *
     * @return array
     */
    public function permissions()
    {
        return (array) optional($this->userRole())->permissions;
    }

    /**
     * Determine if the user has the given permission.
     *
     * @param  string  $permission
     * @return bool
     */
    public function hasPermission(string $permission)
    {
        if (in_array(HasApiTokens::class, class_uses_recursive($this)) &&
            ! $this->tokenCan($permission) &&
            $this->currentAccessToken() !== null) {
            return false;
        }

        $permissions = $this->permissions();

        return in_array($permission, $permissions) ||
               in_array('*', $permissions) ||
               (Str::endsWith($permission, ':create') && in_array('*:create', $permissions)) ||
               (Str::endsWith($permission, ':update') && in_array('*:update', $permissions));
    }
}
