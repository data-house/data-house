<?php

use App\Models\Document;
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
            ->each(function(Document $document): void {
                $document->properties =  ($document->properties?->collect() ?? collect())
                    ->merge(['has_textual_content' => $document->hasTextualContent()]);

                $document->saveQuietly();
            });
    }
};
