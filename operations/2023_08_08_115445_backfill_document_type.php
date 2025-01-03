<?php

use App\Actions\ClassifyDocumentType;
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
        $recognize = app()->make(ClassifyDocumentType::class);

        Document::whereNull('type')
            ->each(function(Document $document) use ($recognize): void {
                $document->type = $recognize($document);

                $document->saveQuietly();
            });
    }
};
