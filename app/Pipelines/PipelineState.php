<?php

namespace App\Pipelines;


/**
 * States for @see \App\Pipelines\Models\Pipeline
 */
enum PipelineState: string
{
    case CREATED = 'created';
    case QUEUED = 'queued';
    case PAUSED = 'paused';
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
    case STUCK = 'stuck';
}