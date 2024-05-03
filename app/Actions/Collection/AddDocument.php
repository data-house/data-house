<?php

namespace App\Actions\Collection;

use App\Models\Collection;
use App\Models\Document;
use App\Models\LinkedDocument;
use App\Models\Team;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class AddDocument
{
    /**
     * Add a document to the given collection.
     *
     * @param  array  $input
     */
    public function __invoke(Document $document, Collection $collection, ?User $user = null): LinkedDocument
    {
        $user = $user ?? auth()->user();

        if(is_null($user)){
            throw new AuthenticationException(__('Unauthenticated. Authentication is required to add a document to a collection'));
        }

        if ($user->cannot('update', $document) || $user->cannot('view', $collection)) { 
            throw new AuthorizationException(__('User not allowed to add document to collection'));
        }

        if($document->visibility !== Visibility::PROTECTED && $collection->visibility === Visibility::PROTECTED){
            throw ValidationException::withMessages([
                'collection' => __('Team document cannot be added to a collection visible by all authenticated users.'),
            ]);
        }

        $collection->documents()->attach($document->getKey(), [
            'user_id' => $user->getKey(),
        ]);

        return LinkedDocument::where('collection_id', $collection->getKey())->where('document_id', $document->getKey())->first();
    }
}
