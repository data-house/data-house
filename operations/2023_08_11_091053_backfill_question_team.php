<?php

use App\Models\Question;
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
        // Backfill question team based on user that asked the question
        // Using the current team can be border-line as it might not be
        // the same team when the question was created. 
        // Considering the currently deployed instances (as of  
        // september 2023) this is safe as all users have only
        //  one team and is the current one

        Question::query()
            ->whereNull('team_id')
            ->doesntHave('ancestors')
            ->with(['user', 'user.currentTeam'])
            ->each(function(Question $question): void {
                $question->team_id = $question->user?->currentTeam?->getKey();

                $question->saveQuietly();
            });
    }
};
