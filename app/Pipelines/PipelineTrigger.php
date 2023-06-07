<?php

namespace App\Pipelines;


/**
 * Starting triggers for @see \App\Pipelines\Models\Pipeline
 */
enum PipelineTrigger: string
{
    /**
     * The pipeline is triggered at each change of the model
     */
    case ALWAYS = 'always';

    /**
     * The pipeline is triggered at model's first creation
     */
    case MODEL_CREATED = 'created';

    /**
     * The pipeline is triggered at model's update
     */
    case MODEL_SAVED = 'saved';
    
    /**
     * The pipeline is triggered at model's removal
     */
    case MODEL_DELETED = 'deleted';

    /**
     * The pipeline is triggered at model's forced removal
     */
    case MODEL_FORCE_DELETED = 'forceDeleted';

    /**
     * The pipeline is triggered at model's restore from soft delete
     */
    case MODEL_RESTORED = 'restored';
}