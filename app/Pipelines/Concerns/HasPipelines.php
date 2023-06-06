<?php

namespace App\Pipelines\Concerns;

use App\Pipelines\Pipeline;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasPipelines
{

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


    // TODO: Allow to retrieve defined pipelines for the model

    // TODO: Allow to run the defined pipeline for the model
    
    // TODO: Listen to model events to trigger a new pipeline
}