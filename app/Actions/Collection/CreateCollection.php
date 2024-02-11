<?php

namespace App\Actions\Collection;

use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\CollectionType;
use App\Models\Team;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
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
        abort_unless($user->can('create', Collection::class), 403);

        $input = [
            'visibility' => Visibility::TEAM,
            'type' => CollectionType::STATIC,
            'strategy' => CollectionStrategy::STATIC,
            'draft' => false,
            ...$input,
        ];

        Validator::make($input, [
            'title' => ['required',
                'string',
                'min:1',
                'max:255',
                Rule::unique((new Collection)->getTable(), 'title')
                    ->where('visibility', Visibility::TEAM)
                    ->when($user->currentTeam, function ($rule, $targetTeam) {
                        return $rule->where('team_id', $targetTeam->getKey());
                    }),
                Rule::unique((new Collection)->getTable(), 'title')
                    ->where('visibility', Visibility::PROTECTED),
            ],
            'visibility' => ['required', new Enum(Visibility::class)],
            'type' => ['required', new Enum(CollectionType::class)],
            'strategy' => ['nullable', new Enum(CollectionStrategy::class)],
            'draft' => ['nullable', 'bool'],
        ])->validate();

        if($input['visibility'] == Visibility::TEAM && is_null($user->currentTeam)){
            throw ValidationException::withMessages(['team' => __('Team required, but User doesn\'t have a current team set.')]);
        }

        return Collection::create([
            'user_id' => $user->getKey(),
            'title' => $input['title'],
            'visibility' => $input['visibility'] ?? Visibility::TEAM,
            'type' => $input['type'],
            'strategy' => $input['strategy'] ?? CollectionStrategy::STATIC,
            'draft' => $input['draft'] ?? true,
            'team_id' => $user->currentTeam?->getKey(),
        ]);

    }
}
