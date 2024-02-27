<?php

namespace App\Pipelines;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

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
     * Define a pipeline for a model.
     * 
     * You can only define one pipeline for each model.
     * 
     * @param  \Illuminate\Database\Eloquent\Model|string  $key The entity to which the pipeline is attached
     * @param  \App\Pipelines\PipelineTrigger  $trigger The trigger condition
     * @param  array  $steps
     * @return \App\Pipelines\PipelineConfiguration
     */
    public static function define(Model|string $model, PipelineTrigger $trigger, array $steps): PipelineConfiguration
    {
        $key = class_basename($model);

        return tap(new PipelineConfiguration($key, $trigger, $steps), function ($pipe) use ($key, $trigger) {

            if(!isset(static::$pipelines[$key])){
                static::$pipelines[$key] = [$trigger->value => $pipe];
                return;
            }

            static::$pipelines[$key][$trigger->value] = $pipe;
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
     * @param  \App\Pipelines\PipelineTrigger  $trigger The trigger condition
     * @return PipelineConfiguration|null
     */
    public static function get(Model|string $model, PipelineTrigger $trigger): ?PipelineConfiguration
    {
        return static::$pipelines[class_basename($model)][$trigger->value] ?? null;
    }

    /**
     * Dispatch the configured pipeline for a given model
     * 
     * @param \Illuminate\Database\Eloquent\Model  $model The model
     */
    public static function dispatch(Model $model, PipelineTrigger $trigger = null)
    {        
        $pipeline = static::get($model, $trigger);

        if(is_null($pipeline)){
            return;
        }

        $jobs = DB::transaction(function() use ($model, $trigger, $pipeline){

            // create a pipeline run entry for
            // each job defined in the pipeline

            $jobs = collect($pipeline->steps)->map(function(PipelineStepConfiguration $step) use ($model, $trigger) {
                
                $run = static::newPipelineRunModel()->forceFill([
                    'trigger' => $trigger,
                    'status' => PipelineState::CREATED,
                    'job' => $step->job,
                ]);

                $model->pipelineRuns()->save($run);
                
                return $step->asJob($model, $run);
            });

            return $jobs;
        });

        Bus::chain($jobs)->dispatch();
    }
    
    /**
     * Dispatch the configured pipeline for a given model
     * 
     * @param \Illuminate\Database\Eloquent\Model  $model The model
     */
    public static function dispatchOneShotJob(Model $model, string $job)
    {        
        $jobToDispatch = DB::transaction(function() use ($model, $job){
            $run = static::newPipelineRunModel()->forceFill([
                'trigger' => PipelineTrigger::MANUAL,
                'status' => PipelineState::CREATED,
                'job' => $job,
            ]);

            $model->pipelineRuns()->save($run);
            
            return (new PipelineStepConfiguration($job))->asJob($model, $run);
        });

        Bus::chain([$jobToDispatch])->dispatch();
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
}