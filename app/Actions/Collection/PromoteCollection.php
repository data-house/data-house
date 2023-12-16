<?php

namespace App\Actions\Collection;

use App\Models\Collection;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class PromoteCollection
{
    /**
     * Promote the visibility of a collection
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Collection  $collection
     * @param  \App\Models\Visibility  $visibility
     *
     * @throws \InvalidArgumentException if collection is not compatible with promotion target
     * @throws \Illuminate\Auth\Access\AuthorizationException if user cannot perform update action on the collection
     * @throws \Illuminate\Validation\ValidationException if collection title is not unique among the target visibility
     */
    public function __invoke(User $user, Collection $collection, Visibility $target): Collection
    {
        throw_if($target === Visibility::PUBLIC, new InvalidArgumentException(__('Collection cannot be promoted.')));

        throw_if($collection->visibility === Visibility::TEAM && is_null($collection->team_id), new AuthorizationException(__('Cannot identify owning team.')));

        throw_unless($user->can('update', $collection), new AuthorizationException(__('User not allowed to promote collection')));

        throw_if($collection->visibility === Visibility::SYSTEM, new InvalidArgumentException(__('Collection cannot be promoted.')));

        throw_if($target->lowerThan($collection->visibility), new InvalidArgumentException(__('Downgrade collection visibility not allowed.')));

        Validator::make(['title' => $collection->title], [
            'title' => [
                'required',
                'string',
                'min:1',
                'max:255',
                Rule::unique($collection->getTable(), 'title')
                    ->where('visibility', $target)
                    ->when($target === Visibility::TEAM, function ($rule, $targetIsTeam) use ($target, $collection, $user) {
                        return $rule->where('team_id', $collection->team_id ?? $user->currentTeam->getKey());
                    })
                ],
        ], [
            'unique' => __('A collection with the same name already is already present at :Level level.', ['level' => str($target->name)->lower()]),
        ])->validate();

        if($collection->visibility == Visibility::PERSONAL && is_null($collection->team_id)){
            $collection->team_id = $user->currentTeam->getKey();
        }

        $collection->visibility = $target;

        $collection->save();

        return $collection->fresh();
    }
}
