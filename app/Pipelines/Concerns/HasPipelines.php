<?php

namespace App\Pipelines\Concerns;

use App\Pipelines\Pipeline;
use App\Pipelines\PipelineTrigger;
use App\Pipelines\Observers\PipeableModelObserver;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;

trait HasPipelines
{

    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootHasPipelines()
    {
        static::observe(new PipeableModelObserver);
    }

    /**
     * Get all of the model's pipeline runs.
     */
    public function pipelineRuns(): MorphMany
    {
        return $this->morphMany(Pipeline::pipelineRunModel(), 'pipeable');
    }

    /**
     * Get the model's most recent pipeline run.
     */
    public function latestPipelineRun(): MorphOne
    {
        return $this->morphOne(Pipeline::pipelineRunModel(), 'pipeable')->latestOfMany();
    }

    public function dispatchPipeline(?PipelineTrigger $trigger = null)
    {
        Pipeline::dispatch($this, $trigger ?? PipelineTrigger::ALWAYS);
    }

    /**
     * Return the active pipelines (created, queued or running)
     */
    public function activePipelines(): Collection
    {
        return $this->pipelineRuns()->active()->latest()->get();
    }

    /**
     * Check if there are active pipelines (created, queued or running)
     */
    public function hasActivePipelines(): bool
    {
        return $this->activePipelines()->isNotEmpty();
    }
}