<?php

namespace App\Copilot\Engines;

use App\Copilot\Questionable;
use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
use Carbon\Carbon;
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
                    ->timeout(5 * Carbon::SECONDS_PER_MINUTE)
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
                    ->timeout(2 * Carbon::SECONDS_PER_MINUTE)
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
        try{

            $response = Http::acceptJson()
                ->timeout(2 * Carbon::SECONDS_PER_MINUTE)
                ->asJson()
                ->post(rtrim($this->config['host'], '/') . '/question', $question->jsonSerialize())
                ->throwIfServerError();

            $json = $response->json();

            if(isset($json['code']) && $json['code'] == 404){
                logs()->error("Question cannot be answered [{$response->status()}]", ['response' => $json, 'request' => $question]);
                throw new Exception("Document might not be ready to accept questions.");
            }

            if(empty($json['q_id'] ?? null) || $json['q_id'] && $json['q_id'] !== $question->id){
                throw new Exception("Communication error with the copilot. [{$response->status()}]");
            }
            
            if(empty($json['answer'] ?? null)){
                throw new Exception("Communication error with the copilot. Missing answer.");
            }

            logs()->info("Asking question", [
                'question' => $question->jsonSerialize(),
                'answer' => $json,
            ]);

            $answerText = $json['answer'][0]['text'] ?? $json['answer']['text'] ?? null;
            $answerReferences = $json['answer'][0]['references'] ?? $json['answer']['references'] ?? [];

            if(is_null($answerText)){
                throw new Exception(__('There was a problem while obtaining the answer. Please report it.'));
            }

            return new CopilotResponse($answerText, $answerReferences);
        }
        catch(Throwable $ex)
        {
            // TODO: response body can contain error information // {"code":500,"message":"Error while parsing file","type":"Internal Server Error"}
            // {"code":422,"message":"No content found in request","type":"Unprocessable Entity"}
            logs()->error("Error asking question copilot", ['error' => $ex->getMessage(), 'request' => $question]);
            throw $ex;
        }
    }

}
