<?php

namespace App\Actions\Collection;

use App\Models\Collection;
use App\Models\Document;
use App\Models\Team;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;


class AddDocument
{
    /**
     * Add a document to the given collection.
     *
     * @param  array  $input
     */
    public function __invoke(Document $document, Collection $collection, ?User $user = null): void
    {
        $user = $user ?? auth()->user();

        if(is_null($user)){
            throw new AuthenticationException(__('Unauthenticated. Authentication is required to add a document to a collection'));
        }

        if ($user->cannot('update', $document) || $user->cannot('view', $collection)) { 
            throw new AuthorizationException(__('User not allowed to add document to collection'));
        }

        // TODO: Collection should have a comparable visibility of the document

        $collection->documents()->attach($document->getKey());
    }
}
