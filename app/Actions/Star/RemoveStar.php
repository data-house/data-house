<?php

namespace App\Actions\Star;

use App\Events\StarRemoved;
use App\Models\Star;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;

class RemoveStar
{
    /**
     * Remove a starred model.
     */
    public function __invoke(User $user, Star $star): void
    {
        if(is_null($user)){
            throw new AuthenticationException(__('Unauthenticated. Authentication is required to remove a document from a collection'));
        }

        if ($user->cannot('delete', $star)) {
            throw new AuthorizationException(__('User not allowed to remove star'));
        }

        $model = $star->starrable;

        $star->delete();

        event(new StarRemoved($user, $model));

        // TODO: if star has a note remove also the attached notes
    }
}
