<?php

namespace App\Actions\Star;

use App\Events\StarCreated;
use App\Starrable;
use App\Models\Star;
use App\Models\User;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;

class AddStar
{
    /**
     * Star a given starrable model
     */
    public function __invoke(User $user, Model $model, ?string $note = null): Star
    {
        $traits = class_uses_recursive($model);

        if(!($traits[Starrable::class] ?? false)){
            throw new InvalidArgumentException("Given model is not starrable");
        }

        if(is_null($user)){
            throw new AuthenticationException(__('Unauthenticated. Authentication is required to add a document to a collection'));
        }

        
        if ($user->cannot('create', Star::class)) { 
            throw new AuthorizationException(__('User not allowed to star'));
        }

        if ($user->cannot('view', $model)) { 
            throw new AuthorizationException(__('User not allowed to star'));
        }

        $star = $model->stars()->create([
            'user_id' => $user->getKey(),
        ]);

        if($note){
            // TODO: create the corresponding note
        }

        event(new StarCreated($user, $star));

        return $star;
    }
}
