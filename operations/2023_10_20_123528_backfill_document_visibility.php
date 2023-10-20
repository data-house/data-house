<?php

use App\Models\Document;
use App\Models\Visibility;
use TimoKoerber\LaravelOneTimeOperations\OneTimeOperation;

return new class extends OneTimeOperation
{
    /**
     * Determine if the operation is being processed asyncronously.
     */
    protected bool $async = true;

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
        Document::query()
            ->whereNull('visibility')
            ->where('draft', true)
            ->update(['visibility' => Visibility::TEAM]);

        Document::query()
            ->whereNull('visibility')
            ->where('draft', false)
            ->update(['visibility' => Visibility::PROTECTED]);
    }
};
