<?php

use App\Models\Question;
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
        Question::whereNull('visibility')
            ->each(function(Question $question) {
                $question->visibility = Visibility::TEAM;

                $question->saveQuietly();
            });
    }
};
