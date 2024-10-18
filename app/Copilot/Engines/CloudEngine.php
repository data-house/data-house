<?php

namespace App\Copilot\Engines;

use App\Copilot\AnswerAggregationCopilotRequest;
use App\Copilot\Questionable;
use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
use App\Copilot\CopilotSummarizeRequest;
use App\Copilot\Exceptions\CopilotException;
use App\Copilot\Exceptions\ModelNotFoundException;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use RuntimeException;
use Throwable;
use \Illuminate\Support\Str;

class CloudEngine extends Engine
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

    protected function getLibrarySettings(): array
    {
        return [
            "database" => [
                "index_fields" => $this->config['library-settings']['indexed-fields'] ?? ['resource_id']
            ],
            "text" => $this->config['library-settings']['text-processing'] ?? [
                "n_context_chunk" => 10,
                "chunk_length" => 490,
                "chunk_overlap" => 10
            ],
        ];
    }


    public function syncLibrarySettings()
    {
        $libConfig = $this->httpGetLibrary($this->getLibrary());

        if(is_null($libConfig) || empty($libConfig)){
            $this->httpCreateLibrary([
                "id" => $this->getLibrary(),
                "name" => $this->getLibraryName(),
                "config" => $this->getLibrarySettings(),
            ]);

            return;
        }

        $this->httpUpdateLibrary($this->getLibrary(), $this->getLibrarySettings());
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

            // TODO: Maybe we could check if document is already in copilot to not waste time

            if (empty($questionableData = $model->toQuestionableArray())) {
                return;
            }


            if(blank($questionableData['data'])){
                logs()->warning("Attempt to insert empty document in Copilot [{$model->getKey()} - {$model->getCopilotKey()}");
                return;
            }

            return array_merge(
                $questionableData,
                [
                    'id' => $model->getCopilotKey(),
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

                    $response = $this->getHttpClient()
                        ->post('/library/'.$this->getLibrary().'/documents', $object)
                        ->throw();

                    return $response->json();
                    
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
            logs()->error("Error adding documents to copilot", ['error' => $ex->getMessage(), 'type' => get_class($ex)]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
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

                $response = $this->getHttpClient()
                    ->delete('/library/'.$this->getLibrary().'/documents/' . $key)
                    ->throw();

            });


        }
        catch(Throwable $ex)
        {
            // TODO: response body can contain error information
            logs()->error("Error removing documents from copilot", ['error' => $ex->getMessage()]);

            if($ex->getCode() === 404){
                return;
            }

            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
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

            $endpoint = $question->multipleQuestionRequest() ? 'library/{library_id}/questions/transform' : 'library/{library_id}/documents/{document_id}/questions';

            // TODO: check if document is available in copilot before proceeding

            $queryParams = [
                'library_id' => $this->getLibrary(),
                'document_id' => collect($question->documents)->first(),
            ];

            $data = $question->jsonSerialize();

            logs()->info("Asking question [{$question->id}]", array_merge($queryParams, $data));

            $response = $this->getHttpClient()
                ->withUrlParameters($queryParams)
                ->post($endpoint, $data)
                ->throw();

            $json = $response->json();

            logs()->info("Response to question [{$question->id}]", [
                'answer' => $json,
            ]);
            
            if($json['id'] !== $question->id){
                // In case of a question decomposition request the original question id is not present
                throw new CopilotException("Communication error with the copilot. [{$response->status()}]");
            }

            $answerText = $json['text'] ?? null;
            $answerReferences = $json['refs'] ?? [];

            if(blank($answerText)){
                throw new CopilotException(__('There was a problem while obtaining the answer. Please report it.'));
            }

            return new CopilotResponse($answerText, $answerReferences);
        }
        catch(RequestException $ex)
        {
            logs()->error("Error asking question copilot", ['error' => $ex->getMessage(), 'request' => $question]);

            if($ex->response->notFound()){
                throw new ModelNotFoundException($ex->getMessage(), $ex->getCode(), $ex);    
            }
            
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
        }
        catch(Throwable $ex)
        {
            logs()->error("Error asking question copilot", ['error' => $ex->getMessage(), 'request' => $question]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
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

            $response = $this->getHttpClient()
                ->post("/library/{$this->getLibrary()}/summary", $request->jsonSerialize())
                ->throwIfServerError();

            $json = $response->json();

            logs()->info("Summarize text", [
                'request' => $request->jsonSerialize(),
                'response' => $json,
            ]);

            $summary = $json['text'] ?? null;

            if(blank($summary)){
                $details = $json['details'] ?? null;
                throw new CopilotException("Summary not generated. [$details]");
            }

            return new CopilotResponse($summary);
        }
        catch(Throwable $ex)
        {
            // TODO: response body can contain error information // {"code":500,"message":"Error while parsing file","type":"Internal Server Error"}
            // {"code":422,"message":"No content found in request","type":"Unprocessable Entity"}
            logs()->error("Error generating summary", ['error' => $ex->getMessage(), 'request' => $request]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
    
    public function aggregate(AnswerAggregationCopilotRequest $request): CopilotResponse
    {
        try{
            logs()->info("Aggregating answers for question [{$request->id}]");

            $response = $this->getHttpClient()
                ->post("/library/{$this->getLibrary()}/questions/aggregate", $request->jsonSerialize())
                ->throw();

            $json = $response->json();

            logs()->info("Answer aggregation complete for question [{$request->id}]", [
                'request' => $request->jsonSerialize(),
                'answer' => $json,
            ]);

            if($json['id'] !== $request->id){
                // In case of a question decomposition request the original question id is not present
                throw new CopilotException("Communication error with the copilot. [{$response->status()}]");
            }

            $answerText = $json['text'] ?? null;
            $answerReferences = $json['refs'] ?? [];

            if(blank($answerText)){
                throw new CopilotException(__('There was a problem while obtaining the answer. Please report it.'));
            }

            return new CopilotResponse($answerText, $answerReferences);
        }
        catch(Throwable $ex)
        {
            // TODO: response body can contain error information 
            logs()->error("Error asking question copilot", ['error' => $ex->getMessage(), 'request' => $request]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function addClassifier(string $classifier, string $url): string
    {
        try{
            logs()->info("Registering classifier [{$classifier}]...");

            $id = Str::slug($classifier);

            $response = $this->getHttpClient()
                ->post("/library/{$this->getLibrary()}/classifiers", [
                    "id" => $id,
                    "name" => $classifier,
                    "url" => $url,
                ])
                ->throw();

            return $id;
        }
        catch(Throwable $ex)
        {
            logs()->error("Error adding classifier", ['error' => $ex->getMessage(), 'classifier' => $classifier]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
    
    public function removeClassifier(string $classifier): void
    {
        try{
            logs()->info("Removing classifier [{$classifier}]...");

            $response = $this->getHttpClient()
                ->delete("/library/{$this->getLibrary()}/classifiers/{$classifier}")
                ->throw();
        }
        catch(Throwable $ex)
        {
            logs()->error("Error removing classifier", ['error' => $ex->getMessage(), 'classifier' => $classifier]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function classify(string $classifier, $model): Collection
    {

        $traits = class_uses_recursive($model);
    
        if(!isset($traits[Questionable::class])){
            throw new CopilotException('Model not questionable');
        }

        try{
            logs()->info("Classify model [{$classifier}][{$model->getCopilotKey()}]");

            $response = $this->getHttpClient()
                ->post("/library/{$this->getLibrary()}/documents/{$model->getCopilotKey()}/classify", [
                    'classifier' => $classifier,
                ])
                ->throw();

            $json = $response->json();


            if($json['id'] !== $model->getCopilotKey()){
                throw new CopilotException("Communication error with the copilot. [{$response->status()}]");
            }

            $results = $json['results'] ?? null;

            if(blank($results)){
                throw new CopilotException(__('No classification returned for model.'));
            }

            return collect($results);
        }
        catch(Throwable $ex)
        {
            // TODO: response body can contain error information 
            logs()->error("Error classify model", ['error' => $ex->getMessage(), 'model' => $model->getCopilotKey(), 'classifier' => $classifier]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function classifyText(string $classifier, string $text, string $lang = 'en'): Collection
    {
        try{
            $hash = sha1($text);

            logs()->info("Classify text [{$classifier}][{$hash}]");


            $response = $this->getHttpClient()
                ->post("/library/{$this->getLibrary()}/classify", [
                    'classifier' => $classifier,
                    'text' => [
                        'id' => $hash,
                        'lang' => $lang,
                        'text' => $text,
                    ],
                ])
                ->throw();

            $json = $response->json();


            if($json['id'] !== $hash){
                throw new CopilotException("Communication error with the copilot, response does not relate to request. [{$response->status()}]");
            }

            $results = $json['results'] ?? null;

            if(blank($results)){
                throw new CopilotException(__('No classification returned for text.'));
            }

            return collect($results);
        }
        catch(Throwable $ex)
        {
            // TODO: response body can contain error information 
            logs()->error("Error classify model", ['error' => $ex->getMessage(), 'text_hash' => $hash, 'classifier' => $classifier]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function refreshPrompts(): string
    {
        try{

            logs()->info("Refresh prompt on copilot.");

            $response = $this->getHttpClient()
                ->get("/prompts/update")
                ->throw();

            return $response->json('message');
        }
        catch(Throwable $ex)
        {
            logs()->error("Error refreshing prompts", ['error' => $ex->getMessage()]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    protected function getHttpClient(): PendingRequest
    {
        return Http::acceptJson()
                ->timeout($this->getRequestTimeout())
                ->asJson()
                ->baseUrl(rtrim($this->config['host'], '/'));
    }


    // Client specific methods that should be separate in a reusable package

    protected function httpGetLibrary(string $id): array|null
    {
        $response = $this->getHttpClient()->get('/libraries/' . $id);

        if($response->notFound()){
            return null;
        }

        return $response->json();
    }
    
    protected function httpUpdateLibrary(string $id, array $settings)
    {
        $response = $this->getHttpClient()->put('/libraries/' . $id, $settings)
            ->throwIfServerError()
            ->throwIfClientError();

        return $response->json();
    }
    
    protected function httpCreateLibrary(array $request)
    {
        $response = $this->getHttpClient()->post('/libraries', $request)
            ->throwIfServerError()
            ->throwIfClientError();

        return $response->json();
    }

}
