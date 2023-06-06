<?php

namespace App\Pipelines;

use Illuminate\Database\Eloquent\Model;

class Pipeline
{
    
    /**
     * The pipelines that exist within the application.
     *
     * @var array
     */
    public static $pipelines = [];

    
    /**
     * The pipeline run model that should be used.
     *
     * @var string
     */
    public static $pipelineRunModel = 'App\\Pipelines\\Models\\PipelineRun';

    /**
     * The pipeline step run model that should be used.
     *
     * @var string
     */
    public static $pipelineStepRunModel = 'App\\Pipelines\\Models\\PipelineStepRun';

    /**
     * Define a pipeline for a model.
     * 
     * You can only define one pipeline for each model.
     * 
     * @param  \Illuminate\Database\Eloquent\Model|string  $key The entity to which the pipeline is attached
     * @param  array  $steps
     * @return \App\Pipelines\PipelineConfiguration
     */
    public static function define(Model|string $model, array $steps): PipelineConfiguration
    {
        $key = class_basename($model);

        return tap(new PipelineConfiguration($key, $steps), function ($pipe) use ($key) {
            static::$pipelines[$key] = $pipe;
        });
    }


    /**
     * Check if pipelines are configured
     * 
     * @param \Illuminate\Database\Eloquent\Model|string  $model (optional) The key or the model to search for
     * @return bool
     */
    public static function hasPipelines(Model|string $model = null): bool
    {
        if(!is_null($model)){
            return !is_null(static::$pipelines[class_basename($model)] ?? null);
        }

        return !empty(static::$pipelines);
    }

    /**
     * Get a configured pipeline
     * 
     * @param \Illuminate\Database\Eloquent\Model|string  $model (optional) The key or the model to search for
     * @return PipelineConfiguration|null
     */
    public static function get(Model|string $model = null): ?PipelineConfiguration
    {
        return static::$pipelines[class_basename($model)] ?? null;
    }
    
    /**
     * Get the name of the pipeline run model used by the application.
     *
     * @return string
     */
    public static function pipelineRunModel()
    {
        return static::$pipelineRunModel;
    }

    /**
     * Get a new instance of the pipeline run model.
     *
     * @return mixed
     */
    public static function newPipelineRunModel()
    {
        $model = static::pipelineRunModel();

        return new $model;
    }

    /**
     * Specify the pipeline run model that should be used.
     *
     * @param  string  $model
     * @return static
     */
    public static function usePipelineRunModel(string $model)
    {
        static::$pipelineRunModel = $model;

        return new static;
    }

    /**
     * Get the name of the pipeline step model used by the application.
     *
     * @return string
     */
    public static function pipelineStepRunModel()
    {
        return static::$pipelineStepRunModel;
    }

    /**
     * Get a new instance of the pipeline step model.
     *
     * @return mixed
     */
    public static function newPipelineStepRunModel()
    {
        $model = static::pipelineStepRunModel();

        return new $model;
    }

    /**
     * Specify the pipeline step model that should be used.
     *
     * @param  string  $model
     * @return static
     */
    public static function usePipelineStepRunModel(string $model)
    {
        static::$pipelineStepRunModel = $model;

        return new static;
    }
}