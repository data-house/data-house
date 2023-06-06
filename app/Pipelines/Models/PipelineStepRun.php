<?php

namespace App\Pipelines\Models;

use App\Pipelines\Pipeline;
use App\Pipelines\PipelineState;
use App\Pipelines\Concerns\InteractWithRunStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents the execution of a specific step of a pipeline
 */
class PipelineStepRun extends Model
{
    use HasFactory;

    use HasUlids;

    use InteractWithRunStatus;

    protected $casts = [
        'status' => PipelineState::class,
    ];

    /**
     * Get the run that contains this step.
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::pipelineRunModel(), 'pipeline_run_id');
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
