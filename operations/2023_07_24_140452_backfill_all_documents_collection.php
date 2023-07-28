<?php

use App\Actions\Collection\CreateCollection;
use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\CollectionType;
use App\Models\Document;
use App\Models\Role;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Support\Facades\DB;
use TimoKoerber\LaravelOneTimeOperations\OneTimeOperation;

return new class extends OneTimeOperation
{
    /**
     * Determine if the operation is being processed asyncronously.
     */
    protected bool $async = false;

    /**
     * The queue that the job will be dispatched to.
     */
    protected string $queue = 'default';

    /**
     * A tag name, that this operation can be filtered by.
     */
    protected ?string $tag = null;

    /**
     * Process the operation.
     */
    public function process(): void
    {

        $existing = Collection::query()
            ->where('visibility', Visibility::SYSTEM->value)
            ->where('strategy', CollectionStrategy::LIBRARY->value)
            ->first();

        if(!is_null($existing)){
            return;
        }

        $documents = Document::query()->get()->modelKeys();

        DB::transaction(function() use ($documents) {

            $collection = Collection::create([
                'title' => 'All Documents',
                'visibility' => Visibility::SYSTEM,
                'type' => CollectionType::STATIC,
                'strategy' => CollectionStrategy::LIBRARY,
                'draft' => false
            ]);

            $collection->documents()->syncWithoutDetaching($documents);
        });

    }
};
