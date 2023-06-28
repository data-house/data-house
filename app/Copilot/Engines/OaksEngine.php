<?php

namespace App\Copilot\Engines;

use App\Copilot\Questionable;
use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
use Exception;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Throwable;

class OaksEngine extends Engine
{

    public function __construct(array $config = [])
    {
        if(!isset($config['host'])){
            throw new InvalidArgumentException('Missing host in configuration');
        }

        parent::__construct($config);
    }
    
    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function update($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $objects = $models->map(function ($model) {

            $traits = class_uses_recursive($model);

            if(!isset($traits[Questionable::class])){
                return;
            }

            if (empty($questionableData = $model->toQuestionableArray())) {
                return;
            }

            return array_merge(
                $questionableData,
                [
                    'id' => $model->getCopilotKey(),
                    'key_name' => $model->getCopilotKeyName(),
                ],
            );
        })->filter()->values();

        if ($objects->isEmpty()) {
            return;
        }

        try{

            $objects->each(function($object){

                $response = Http::acceptJson()
                    ->asJson()
                    ->post(rtrim($this->config['host'], '/') . '/documents', $object)
                    ->throw();

                if($status = $response->json('status') !== 'ok'){
                    throw new Exception("Document not added [{$status}]");
                }
            });


        }
        catch(Throwable $ex)
        {
            // TODO: response body can contain error information // {"code":500,"message":"Error while parsing file","type":"Internal Server Error"}
            // {"code":422,"message":"No content found in request","type":"Unprocessable Entity"}
            logs()->error("Error adding documents to copilot", ['error' => $ex->getMessage()]);
            throw $ex;
        }
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function delete($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $keys = $models->map->getCopilotKey()->filter()->values();

        if ($keys->isEmpty()) {
            return;
        }

        try{

            $keys->each(function($key){

                $response = Http::acceptJson()
                    ->asJson()
                    ->delete(rtrim($this->config['host'], '/') . '/documents/' . $key)
                    ->throw();

                if($status = $response->json('status') !== 'ok'){
                    throw new Exception("Document not removed [{$key}: {$status}]");
                }
            });


        }
        catch(Throwable $ex)
        {
            // TODO: response body can contain error information // {"code":500,"message":"Error while parsing file","type":"Internal Server Error"}
            // {"code":422,"message":"No content found in request","type":"Unprocessable Entity"}
            logs()->error("Error removing documents from copilot", ['error' => $ex->getMessage()]);
            throw $ex;
        }
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return mixed
     */
    public function question(CopilotRequest $question): CopilotResponse
    {
        return new CopilotResponse('');
    }

}
