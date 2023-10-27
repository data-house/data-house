<?php

namespace App\Actions\Collection;

use App\Models\Collection;
use App\Models\Document;
use App\Models\Team;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;

class RemoveDocument
{
    /**
     * Removes a document from a collection.
     *
     * @param  array  $input
     */
    public function __invoke(Document $document, Collection $collection, ?User $user = null): void
    {
        $user = $user ?? auth()->user();

        if(is_null($user)){
            throw new AuthenticationException(__('Unauthenticated. Authentication is required to remove a document from a collection'));
        }

        if ($user->cannot('update', $document) || $user->cannot('view', $collection)) {
            throw new AuthorizationException(__('User not allowed to remove document from collection'));
        }


        $collection->documents()->detach($document->getKey());
    }
}
