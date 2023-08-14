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
        // Backfill question team based on user that asked the question
        // Currently using the currentTeam as users with multiple teams are not important

        // First, process all single questions
        Question::whereNull('team_id')
            ->each(function(Question $question) {
                $question->visibility = Visibility::TEAM;

                $question->saveQuietly();
            });
    }
};
