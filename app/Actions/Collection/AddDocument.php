<?php

namespace App\Actions\Collection;

use App\Models\Collection;
use App\Models\Document;
use App\Models\Team;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;

class AddDocument
{
    /**
     * Add a document to the given collection.
     *
     * @param  array  $input
     */
    public function __invoke(Document $document, Collection $collection): void
    {
        // Gate::forUser($user)->authorize('update', $team);

        // TODO: how I can verify that a user is entitled to add a document to a collection


        $collection->documents()->attach($document->getKey());
    }
}
