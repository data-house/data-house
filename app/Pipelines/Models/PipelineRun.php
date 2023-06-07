<?php

namespace App\Pipelines\Models;

use App\Pipelines\Pipeline;
use App\Pipelines\PipelineState;
use App\Pipelines\Concerns\InteractWithRunStatus;
use App\Pipelines\PipelineTrigger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Represents an execution of a pipeline on a specific model
 */
class PipelineRun extends Model
{
    use HasFactory;

    use HasUlids;

    use InteractWithRunStatus;

    protected $casts = [
        'status' => PipelineState::class,
        'trigger' => PipelineTrigger::class,
    ];

    /**
     * Get the input pipeable model for this run.
     */
    public function pipeable(): MorphTo
    {
        return $this->morphTo();
    }

    
    /**
     * Get the steps contained in this run
     */
    public function steps(): HasMany
    {
        return $this->hasMany(Pipeline::pipelineStepRunModel(), 'pipeline_run_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', [PipelineState::CREATED, PipelineState::QUEUED, PipelineState::RUNNING]);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'id',
    ];
    
    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds()
    {
        return ['ulid'];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'ulid';
    }

}
