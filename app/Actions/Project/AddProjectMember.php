<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Rules\Role;

class AddProjectMember
{
    /**
     * Add a new team member to the given team.
     */
    public function add(Project $project, Team $team, string $role = null): void
    {
        // Gate::forUser($user)->authorize('addProjectMember', $project);

        $this->validate($team, $project, $role);

        $project->teams()->attach(
            $team, ['role' => $role]
        );
    }

    /**
     * Validate the add member operation.
     */
    protected function validate(Team $team, Project $project, ?string $role): void
    {
        Validator::make([
            'role' => $role,
        ], $this->rules())
        ->after(
            $this->ensureProjectIsNotAlreadyOnTeam($team, $project)
        )->validateWithBag('addProjectMember');
    }

    /**
     * Get the validation rules for adding a team member.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function rules(): array
    {
        return array_filter([
            'role' => Jetstream::hasRoles()
                            ? ['required', 'string', new Role]
                            : null,
        ]);
    }

    /**
     * Ensure that the user is not already on the team.
     */
    protected function ensureProjectIsNotAlreadyOnTeam(Team $team, Project $project): Closure
    {
        return function ($validator) use ($team, $project): void {
            $validator->errors()->addIf(
                $project->belongsToTeam($team),
                'team',
                __('This team already belongs to the project.')
            );
        };
    }
}
