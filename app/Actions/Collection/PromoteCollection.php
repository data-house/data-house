<?php

namespace App\Actions\Collection;

use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\CollectionType;
use App\Models\Team;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;

class PromoteCollection
{
    /**
     * Promote the visibility of a collection
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Collection  $collection
     * @param  \App\Models\Visibility  $visibility
     */
    public function __invoke(User $user, Collection $collection, Visibility $target): Collection
    {
        throw_if($target === Visibility::PUBLIC, new InvalidArgumentException(__('Collection cannot be promoted.')));

        throw_if($collection->visibility === Visibility::TEAM && is_null($collection->team_id), new AuthorizationException(__('Cannot identify owning team.')));

        throw_unless($user->can('update', $collection), new AuthorizationException(__('User not allowed to promote collection')));

        throw_if($collection->visibility === Visibility::SYSTEM, new InvalidArgumentException(__('Collection cannot be promoted.')));

        throw_if($target->lowerThan($collection->visibility), new InvalidArgumentException(__('Downgrade collection visibility not allowed.')));

        if($collection->visibility == Visibility::PERSONAL && is_null($collection->team_id)){
            $collection->team_id = $user->currentTeam->getKey();
        }

        $collection->visibility = $target;

        $collection->save();

        return $collection->fresh();
    }
}
