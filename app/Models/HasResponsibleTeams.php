<?php

namespace App\Models;

use Illuminate\Support\Str;
use Laravel\Jetstream\Jetstream;
use Laravel\Sanctum\HasApiTokens;

trait HasResponsibleTeams
{

    /**
     * Get all of the teams the are responsible for the project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function responsibleTeams()
    {
        return $this->teams()->wherePivotIn('role', [Role::ADMIN->value, Role::MANAGER->value]);
    }

    /**
     * Get all of the teams the project belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teams()
    {
        return $this->belongsToMany(Jetstream::teamModel())
                        ->withPivot('role')
                        ->withTimestamps();
    }

    /**
     * Determine if the project belongs to the given team.
     *
     * @param  mixed  $team
     * @return bool
     */
    public function belongsToTeam($team)
    {
        if (is_null($team)) {
            return false;
        }

        return $this->teams->contains(function ($t) use ($team) {
            return $t->id === $team->id;
        });
    }

    /**
     * Get the role that the user has on the team.
     *
     * @param  mixed  $team
     * @return \Laravel\Jetstream\Role|null
     */
    public function teamRole($team)
    {
        if (! $this->belongsToTeam($team)) {
            return ['project:view', 'document:view'];
        }

        $role = $team->projects
            ->where('id', $this->id)
            ->first()
            ->pivot
            ->role;

        return $role ? Jetstream::findRole($role) : null;
    }

    /**
     * Determine if the user has the given role on the given team.
     *
     * @param  mixed  $team
     * @param  string  $role
     * @return bool
     */
    public function hasTeamRole($team, string $role)
    {
        if (!$this->belongsToTeam($team)) {
            return false;
        }

        $assignedRole = $team->projects->where(
            'id', $this->id
        )->first()->pivot->role;

        return $this->belongsToTeam($team) && $assignedRole && optional(Jetstream::findRole($assignedRole))->key === $role;
    }

    /**
     * Get the user's permissions for the given team.
     *
     * @param  mixed  $team
     * @return array
     */
    public function teamPermissions($team)
    {
        if (! $this->belongsToTeam($team)) {
            return ['project:view', 'document:view'];
        }

        return (array) (optional($this->teamRole($team))->permissions ?? ['project:view', 'document:view']);
    }

    /**
     * Determine if the user has the given permission on the given team.
     *
     * @param  mixed  $team
     * @param  string  $permission
     * @return bool
     */
    public function hasTeamPermission($team, string $permission)
    {
        if (! $this->belongsToTeam($team)) {
            return in_array($permission, ['project:view', 'document:view']);
        }

        $permissions = $this->teamPermissions($team);

        return in_array($permission, $permissions) ||
               in_array('*', $permissions) ||
               (Str::endsWith($permission, ':create') && in_array('*:create', $permissions)) ||
               (Str::endsWith($permission, ':update') && in_array('*:update', $permissions));
    }
}
