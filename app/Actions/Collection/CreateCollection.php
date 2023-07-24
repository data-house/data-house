<?php

namespace App\Actions\Collection;

use App\Models\Collection;
use App\Models\Team;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;

class CreateCollection
{
    /**
     * Validate and create the given collection.
     *
     * @param  array  $input
     */
    public function __invoke(User $user, array $input): Collection
    {
        $user->can('create', Collection::class);

        Validator::make($input, [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'visibility' => [new Enum(Visibility::class)],
            // TODO: handle validation in case of Team collection as team_id is required
        ])->validate();

        return Collection::create([
            'user_id' => $user->getKey(),
            'title' => $input['title'],
            'visibility' => $input['visibility'],
        ]);

    }
}