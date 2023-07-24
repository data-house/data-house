<?php

namespace App\Jobs;

use App\Models\Question;
use App\Models\QuestionStatus;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class AggregateMultipleQuestionAnswersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Question $question,
    )
    {
        //
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping(
                key: $this->question->lockKey(),
                releaseAfter: 2 * Carbon::SECONDS_PER_MINUTE,
                expiresAfter: 2 * Carbon::MINUTES_PER_HOUR * Carbon::SECONDS_PER_MINUTE
                )];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->hasBeenCancelled()) {
            return;
        }
        
        // list($mainQuestion, $questions) = $this->question->decompose();

        // Check if there are pending questions => if yes push back the job to the queue

        // If single related question execution is finished

        // - aggregate the answers
        // - mark the question as processed

        // if ( ! isset($report->nextPageToken)) {
        //     $this->lastPage();
        // } else {
        //     // Dispatch next page
        //     // TODO: dispatch the next page
        //     // self::dispatch($this->importMap, $report->nextPageToken)->delay(now()->addSeconds(5));
        // }
    }

    public function failed()
    {
        if ($this->question->status == QuestionStatus::ERROR) {
            return ;
        }
        
        Cache::lock($this->question->lockKey())->block(30, function() {
            $this->question->status = QuestionStatus::ERROR;
            $this->question->save();
        });
    }

    protected function hasBeenCancelled(): bool
    {
        return $this->question->status === QuestionStatus::CANCELLED 
            || $this->question->status === QuestionStatus::ERROR;
    }
}
