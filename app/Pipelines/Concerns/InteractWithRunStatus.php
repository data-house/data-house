<?php

namespace App\Pipelines\Concerns;

use App\Pipelines\Pipeline;
use App\Pipelines\PipelineState;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Bus;

trait InteractWithRunStatus
{

    public function markAsRunning()
    {
        $this->updateState(PipelineState::RUNNING);
    }

    public function markAsCompleted()
    {
        $this->updateState(PipelineState::COMPLETED);
    }

    public function markAsStuck()
    {
        $this->updateState(PipelineState::STUCK);
    }

    public function markAsFailed()
    {
        $this->updateState(PipelineState::FAILED);
    }

    public function markAsCancelled()
    {
        $this->updateState(PipelineState::CANCELLED);
    }

    public function markAsQueued()
    {
        $this->updateState(PipelineState::QUEUED);
    }

    private function updateState(PipelineState $state)
    {
        $this->status = $state;
        $this->save();
    }
}