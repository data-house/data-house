<?php

namespace App\Copilot\Engines;

use App\Copilot\AnswerAggregationCopilotRequest;
use App\Copilot\Questionable;
use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
use App\Copilot\CopilotSummarizeRequest;
use App\Copilot\Exceptions\CopilotException;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Throwable;

class OaksEngine extends Engine
{

    protected const SUPPORTED_LANGUAGES = [
        LanguageAlpha2::English,
        LanguageAlpha2::German,
        LanguageAlpha2::Spanish_Castilian,
    ];

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
                    'library_id' => $this->getLibrary(),
                    'key_name' => $model->getCopilotKeyName(),
                ],
            );
        })->filter()->values();

        if ($objects->isEmpty()) {
            return;
        }

        try{

            // Currently the Copilot service is not able to handle more than one request
            // at a time, therefore we process all operations sequentially

            $objects->each(function($object){

                try {   

                    $response = Http::acceptJson()
                        ->timeout($this->getRequestTimeout())
                        ->asJson()
                        ->post(rtrim($this->config['host'], '/') . '/documents', $object)
                        ->throw();

                    if($status = $response->json('status') !== 'ok'){
                        throw new Exception("Document not added [{$status}]");
                    }
                    
                } catch (RequestException $th) {
                    if($th->getCode() !== 409){
                        logs()->error("Failed to add document to copilot", ['id' => $object['id'], 'error' => $th]);
                        throw $th;
                    }
                                        
                    logs()->warning("Duplicate document", ['id' => $object['id']]);
                }
            });


        }
        catch(Throwable $ex)
        {
            // TODO: response body can contain error information
            // {"code":500,"message":"Error while parsing file","type":"Internal Server Error"}
            // {"code":422,"message":"No content found in request","type":"Unprocessable Entity"}
            logs()->error("Error adding documents to copilot", ['error' => $ex->getMessage(), 'type' => get_class($ex)]);
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
                    ->timeout($this->getRequestTimeout())
                    ->asJson()
                    ->delete(rtrim($this->config['host'], '/') . '/documents/' . $key, [
                        'library_id' => $this->getLibrary()
                    ])
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

            if($ex->getCode() === 404){
                return;
            }

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

            $endpoint = $question->multipleQuestionRequest() ? '/transform-question' : '/question';

            $data = array_merge(
                $question->jsonSerialize(),
                ['library_id' => $this->getLibrary()],
            );

            $response = Http::acceptJson()
                ->timeout($this->getRequestTimeout())
                ->asJson()
                ->post(rtrim($this->config['host'], '/') . $endpoint, $data)
                ->throwIfServerError();

            $json = $response->json();

            logs()->info("Asking question", [
                'question' => $data,
                'answer' => $json,
            ]);

            if(isset($json['code']) && $json['code'] == 404){
                logs()->error("Question cannot be answered [{$response->status()}]", ['response' => $json, 'request' => $question]);
                throw new CopilotException("Document might not be ready to accept questions.");
            }

            if($question->multipleQuestionRequest()){

                // TODO: this is not a correct response handling, but for now transformation will be in answer
                return new CopilotResponse('', $json);

            }
            
            
            if(empty($json['q_id'] ?? null) || $json['q_id'] && $json['q_id'] !== $question->id){
                // In case of a question decomposition request the original question id is not present
                throw new CopilotException("Communication error with the copilot. [{$response->status()}]");
            }
            
            if(empty($json['answer'] ?? null)){
                throw new CopilotException("Communication error with the copilot. Missing answer.");
            }

            $answerText = $json['answer'][0]['text'] ?? $json['answer']['text'] ?? null;
            $answerReferences = $json['answer'][0]['references'] ?? $json['answer']['references'] ?? [];

            if(is_null($answerText)){
                throw new CopilotException(__('There was a problem while obtaining the answer. Please report it.'));
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


    public function summarize(CopilotSummarizeRequest $request): CopilotResponse
    {
        if(!in_array($request->language, self::SUPPORTED_LANGUAGES)){
            throw new InvalidArgumentException(__(':Document_language language not supported. Automated summaries are supported only for text in :languages.', [
                'languages' => collect(self::SUPPORTED_LANGUAGES)->map->name->join(', '),
                'document_language' => $request->language->name,
            ]));
        }

        try{

            $response = Http::acceptJson()
                ->timeout($this->getRequestTimeout())
                ->asJson()
                ->post(rtrim($this->config['host'], '/') . '/summarize', $request->jsonSerialize())
                ->throwIfServerError();

            $json = $response->json();

            logs()->info("Summarize text", [
                'request' => $request->jsonSerialize(),
                'response' => $json,
            ]);
            
            if(empty($json['summary'] ?? null)){
                throw new CopilotException("Communication error with the copilot. Missing answer.");
            }

            return new CopilotResponse($json['summary']);
        }
        catch(Throwable $ex)
        {
            // TODO: response body can contain error information // {"code":500,"message":"Error while parsing file","type":"Internal Server Error"}
            // {"code":422,"message":"No content found in request","type":"Unprocessable Entity"}
            logs()->error("Error asking summary to copilot", ['error' => $ex->getMessage(), 'request' => $request]);
            throw $ex;
        }
    }
    
    public function aggregate(AnswerAggregationCopilotRequest $request): CopilotResponse
    {
        try{

            $endpoint = '/answer-aggregation';

            $response = Http::acceptJson()
                ->timeout($this->getRequestTimeout())
                ->asJson()
                ->post(rtrim($this->config['host'], '/') . $endpoint, $request->jsonSerialize())
                ->throwIfServerError();

            $json = $response->json();

            logs()->info("Aggregating question answers", [
                'request' => $request->jsonSerialize(),
                'answer' => $json,
            ]);

            if(isset($json['code']) && $json['code'] == 404){
                logs()->error("Question cannot be answered [{$response->status()}]", ['response' => $json, 'request' => $question]);
                throw new CopilotException("Document might not be ready to accept questions.");
            }
            
            if(empty($json['answer'] ?? null)){
                throw new CopilotException("Communication error with the copilot. Missing answer.");
            }

            $answerText = $json['answer'][0]['text'] ?? $json['answer']['text'] ?? $json['answer'] ?? null;
            $answerReferences = $json['answer'][0]['references'] ?? $json['answer']['references'] ?? $json['references'] ?? [];

            if(is_null($answerText)){
                throw new CopilotException(__('There was a problem while obtaining the answer. Please report it.'));
            }

            return new CopilotResponse($answerText, $answerReferences);
        }
        catch(Throwable $ex)
        {
            // TODO: response body can contain error information // {"code":500,"message":"Error while parsing file","type":"Internal Server Error"}
            // {"code":422,"message":"No content found in request","type":"Unprocessable Entity"}
            logs()->error("Error asking question copilot", ['error' => $ex->getMessage(), 'request' => $request]);
            throw $ex;
        }
    }

    public function defineTagList(string $name, array $tags)
    {
        $requestData = [
            'topic_list_id' => $name,
            'library_id' => $this->getLibrary(),
            'topics' => $tags,
        ];

        try{

            $endpoint = '/topic';

            $response = Http::acceptJson()
                ->timeout($this->getRequestTimeout())
                ->asJson()
                ->post(rtrim($this->config['host'], '/') . $endpoint, $requestData)
                ->throwIfServerError();

            $json = $response->json();

            if(!$json ){
                logs()->error("Topic list not added [{$response->status()}]", ['response' => $response->body(), 'request' => $requestData]);
                throw new CopilotException("Error adding topic list. See error log for more details.");
            }

            if(isset($json['code']) && $json['code'] != 200){
                logs()->error("Topic list not added [{$response->status()}]", ['response' => $json, 'request' => $requestData]);
                throw new CopilotException(isset($json['message']) ? $json['message'] : "Error adding topic list. [{$json['code']}]");
            }
            
            if(!isset($json['id'])){
                logs()->error("Topic list not added [{$response->status()}]", ['response' => $response->body(), 'request' => $requestData]);
                throw new CopilotException("Error adding topic list.");
            }
            
        }
        catch(Throwable $ex)
        {
            logs()->error("Error creating topic list", ['error' => $ex->getMessage(), 'request' => $requestData]);
            throw $ex;
        }
    }

    public function tag($list, $model): Collection
    {
        $traits = class_uses_recursive($model);

        if(!isset($traits[Questionable::class])){
            throw new InvalidArgumentException("Model not questionable");
        }

        $requestData = [
            'topic_list_id' => $list,
            'library_id' => $this->getLibrary(),
            'doc_id' => $model->getCopilotKey(),
        ];
        
        try{

            $endpoint = '/topic/classify';

            $response = Http::acceptJson()
                ->timeout($this->getRequestTimeout())
                ->asJson()
                ->post(rtrim($this->config['host'], '/') . $endpoint, $requestData)
                ->throwIfServerError();

            $json = $response->json();

            if(isset($json['code']) && $json['code'] != 200){
                logs()->error("Document not tagged [{$response->status()}]", ['response' => $json, 'request' => $requestData]);
                throw new CopilotException(isset($json['message']) ? $json['message'] : "Error document tagging. [{$json['code']}]");
            }
            
            return collect($json['topics'] ?? []);
        }
        catch(Throwable $ex)
        {
            logs()->error("Error tagging document", ['error' => $ex->getMessage(), 'request' => $requestData]);
            throw $ex;
        }

    }

    public function removeTagList(string $name)
    {
        try{

            $response = Http::acceptJson()
                ->timeout($this->getRequestTimeout())
                ->asJson()
                ->delete(rtrim($this->config['host'], '/') . '/topic/' . $name, [
                    'library_id' => $this->getLibrary()
                ])
                ->throw();

            $json = $response->json();

            if(isset($json['code']) && $json['code'] != 200){
                logs()->error("Document not tagged [{$response->status()}]", ['response' => $json, 'request' => $name]);
                throw new CopilotException(isset($json['message']) ? $json['message'] : "Error document tagging. [{$json['code']}]");
            }
        }
        catch(Throwable $ex)
        {
            logs()->error("Error removing tag list from copilot", ['error' => $ex->getMessage()]);

            if($ex->getCode() === 404){
                return;
            }

            throw $ex;
        }
    }

}
