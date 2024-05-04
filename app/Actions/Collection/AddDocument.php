<?php

namespace App\Actions\Collection;

use App\Models\Collection;
use App\Models\Document;
use App\Models\LinkedDocument;
use App\Models\RelationType;
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
    public function __invoke(Document $document, Collection $collection, ?User $user = null, ?RelationType $relationType = null): LinkedDocument
    {
        $user = $user ?? auth()->user();

        if(is_null($user)){
            throw new AuthenticationException(__('Unauthenticated. Authentication is required to add a document to a collection'));
        }

        if (!($document->isVisibleBy($user) && $user->can('view', $collection))) { 
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

        $linkedDocument = LinkedDocument::findBy($document, $collection);

        if(!is_null($relationType)){
            $linkedDocument->linkTypes()->attach($relationType, [
                'user_id' => $user->getKey(),
            ]);
        }
        
        return $linkedDocument;
    }
}
