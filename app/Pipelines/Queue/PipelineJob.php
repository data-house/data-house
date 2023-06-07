<?php

namespace App\Pipelines\Queue;

use App\Pipelines\Queue\Middleware\ReportJobStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

abstract class PipelineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * The reference to the model
     * 
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;
    
    /**
     * The current run
     */
    public Model $run;

    /**
     * Create a new job instance.
     * 
     * @param \Illuminate\Database\Eloquent\Model $model The instance of the Eloquent model that is the input of the pipeline job
     * @param \Illuminate\Database\Eloquent\Model $run The instance of the Eloquent model representing the pipeline step run
     */
    public function __construct(
        Model $model,
        Model $run,
    )
    {
        $this->model = $model;
        $this->run = $run;
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [
            new ReportJobStatus, 
            // new PreventPausedJobsExecution,
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $this->run->markAsFailed();
    }

}
