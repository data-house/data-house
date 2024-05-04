<?php

namespace App\Actions\Star;

use App\Events\StarRemoved;
use App\Models\Star;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function() use ($star){
            $star->notes()->delete();
    
            $star->delete();
        });

        event(new StarRemoved($user, $model));
    }
}
