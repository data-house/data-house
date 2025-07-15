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
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Throwable;
use \Illuminate\Support\Str;
use OneOffTech\LibrarianClient\Connectors\LibrarianConnector;
use OneOffTech\LibrarianClient\Dto\Classifier;
use OneOffTech\LibrarianClient\Dto\Document;
use OneOffTech\LibrarianClient\Dto\Extraction;
use OneOffTech\LibrarianClient\Dto\Library;
use OneOffTech\LibrarianClient\Dto\LibraryConfiguration;
use OneOffTech\LibrarianClient\Dto\Text;
use Saloon\Exceptions\Request\ClientException;
use Saloon\Exceptions\Request\Statuses\NotFoundException;

class CloudEngine extends Engine
{

    protected const SUPPORTED_LANGUAGES = [
        LanguageAlpha2::English,
        LanguageAlpha2::German,
        LanguageAlpha2::Spanish_Castilian,
    ];

    protected LibrarianConnector $connnector;

    public function __construct(array $config = [])
    {
        if(blank($config['host'] ?? null)){
            throw new InvalidArgumentException('Missing host in configuration');
        }
        
        if(blank($config['key'] ?? null)){
            throw new InvalidArgumentException('Missing key in configuration');
        }

        $this->connnector = new LibrarianConnector($config['key'], rtrim($config['host'], '/'));

        parent::__construct($config);
    }

    /**
     * Create or update the library on Copilot based on the current settings
     */
    public function syncLibrarySettings()
    {
        try {
            $library = $this->connnector->libraries()->get($this->getLibrary());

            $this->connnector->libraries()->update($this->getLibrary(), $this->getLibrarySettings());

        } catch (NotFoundException $th) {
            $this->connnector->libraries()->create(new Library(
                id: $this->getLibrary(),
                name: $this->getLibraryName(),
                configuration: $this->getLibrarySettings(),
            ));
        }
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

        $documentConnector = $this->connnector->documents($this->getLibrary());

        $objects = $models->map(function ($model) use ($documentConnector) {

            $traits = class_uses_recursive($model);

            if(!isset($traits[Questionable::class])){
                return;
            }

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

            $objects->each(function($object) use ($documentConnector) {

                try {   

                    $response = $documentConnector->create(
                        new Document(
                            id: $object['id'],
                            language: $object['lang'],
                            data: $object['data']->toArray()
                        ));

                    return $response->json();
                    
                } catch (ClientException $th) {
                    if($th->getStatus() !== 409){
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

            $documentConnector = $this->connnector->documents($this->getLibrary());

            $keys->each(function($key) use ($documentConnector) : void {
                try{
                    $documentConnector->delete($key);
                }
                catch(NotFoundException $nex)
                {
                    logs()->warning("Copilot: removing not found document", ['key' => $key]);
                }
            });

        }
        catch(Throwable $ex)
        {
            // TODO: response body can contain error information
            logs()->error("Error removing documents from copilot", ['error' => $ex->getMessage()]);

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
            
            $library = $this->getLibrary();

            $librarianQuestion = $question->getLibrarianQuestion();

            logs()->info("Asking question [{$question->id}] to library [{$library}]", ['document' => collect($question->documents)->first()]);

            if($question->multipleQuestionRequest()){

                $librarianTransformation = $question->getLibrarianQuestionTransformation();

                $transformedQuestion = $this->connnector->questions($library)->transform($librarianQuestion, $librarianTransformation);

                if($transformedQuestion->id !== $question->id){
                    // In case of a question decomposition request the original question id is not present
                    throw new CopilotException("Communication error with copilot. [419]");
                }

                return new CopilotResponse($transformedQuestion->text, []);
            }

            $answer = $this->connnector->documents($library)->ask(collect($question->documents)->first(), $librarianQuestion);

            logs()->info("Response obtained to to question [{$question->id}]");
            
            if($answer->id !== $question->id){
                // In case of a question decomposition request the original question id is not present
                throw new CopilotException("Communication error with the copilot. [419]");
            }

            $answerText = $answer->text ?? null;
            $answerReferences = $answer->refs ?? [];

            if(blank($answerText)){
                throw new CopilotException(__('There was a problem while obtaining the answer. Please report it.'));
            }

            return new CopilotResponse($answerText, $answerReferences);
        }
        catch(ClientException $ex)
        {
            logs()->error("Error asking question copilot", ['error' => $ex->getMessage(), 'request' => $question]);

            if($ex->getResponse()->status() === 404){
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

    public function aggregate(AnswerAggregationCopilotRequest $request): CopilotResponse
    {
        try{
            logs()->info("Aggregating answers for question [{$request->id}]");
    
            $aggregatedAnswer = $this->connnector->questions($this->getLibrary())->aggregate(
                $request->getLibrarianQuestion(),
                $request->getAnswerCollection(),
                $request->getLibrarianQuestionTransformation()
            );

            logs()->info("Answer aggregation complete for question [{$request->id}]");

            if($aggregatedAnswer->id !== $request->id){
                // In case of a question decomposition request the original question id is not present
                throw new CopilotException("Communication error with the copilot. [{$aggregatedAnswer->getResponse()->status()}]");
            }

            if(blank($aggregatedAnswer->text)){
                throw new CopilotException(__('There was a problem while obtaining the answer. Please report it.'));
            }

            return new CopilotResponse($aggregatedAnswer->text, $aggregatedAnswer->refs);
        }
        catch(Throwable $ex)
        {
            logs()->error("Error asking question copilot", ['error' => $ex->getMessage(), 'request' => $request]);
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

            $summary = $this->connnector->summaries($this->getLibrary())->generate(new Text(
                id: $request->id,
                content: $request->text,
                language: $request->language?->value,
            ));

            logs()->info("Summarize text request"); // TODO: use Laravel Contexts to retain relevant data from the original request that triggered this call

            if(blank($summary->content)){
                throw new CopilotException("Summary not generated.");
            }

            return new CopilotResponse($summary->content);
        }
        catch(Throwable $ex)
        {
            logs()->error("Error generating summary", ['error' => $ex->getMessage(), 'request' => $request]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
    
    

    public function addClassifier(string $classifier, string $url): string
    {
        try{
            logs()->info("Registering classifier [{$classifier}]...");

            $id = Str::slug($classifier);

            $data = new Classifier($id, $url, $classifier);

            $response = $this->connnector->classifiers($this->getLibrary())->create($data);

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

            $response = $this->connnector->classifiers($this->getLibrary())->delete($classifier);
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

            $classification = $this->connnector->documents($this->getLibrary())->classify($classifier, $model->getCopilotKey());

            if($classification->id !== $model->getCopilotKey()){
                throw new CopilotException("Communication error with the copilot, response does not relate to request. [{$classification->getResponse()->status()}]");
            }

            if(blank($classification->results)){
                throw new CopilotException(__('No classification returned for text.'));
            }

            return collect($classification->results);
        }
        catch(Throwable $ex)
        {
            logs()->error("Error classify model", ['error' => $ex->getMessage(), 'model' => $model->getCopilotKey(), 'classifier' => $classifier]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function classifyText(string $classifier, string $text, string $lang = 'en'): Collection
    {
        try{
            $hash = sha1($text);

            logs()->info("Classify text [{$classifier}][{$hash}]");

            $classification = $this->connnector->classifiers($this->getLibrary())->classify($classifier, new Text($hash, $lang, $text));

            if($classification->id !== $hash){
                throw new CopilotException("Communication error with the copilot, response does not relate to request. [{$classification->getResponse()->status()}]");
            }

            if(blank($classification->results)){
                throw new CopilotException(__('No classification returned for text.'));
            }

            return collect($classification->results);
        }
        catch(Throwable $ex)
        {
            logs()->error("Error classify model", ['error' => $ex->getMessage(), 'text_hash' => $hash, 'classifier' => $classifier]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function extract(string $structuredResponseModel, Document $document, ?array $sections = null, ?string $instructions = null): Extraction
    {
        try{
            logs()->info("Performing structured extraction...");

            $response = $this->connnector->extractions($this->getLibrary())->extract(
                structuredResponseModel: $structuredResponseModel,
                from: $document,
                sections: $sections,
                instructions: $instructions,
            );

            return $response;
        }
        catch(Throwable $ex)
        {
            logs()->error("Error structured extraction", ['error' => $ex->getMessage(), 'classifier' => $classifier]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function refreshPrompts(): string
    {
        try{

            logs()->info("Refresh prompts on copilot.");

            $response = $this->connnector->prompts()->sync();

            return $response->json('message');
        }
        catch(Throwable $ex)
        {
            logs()->error("Error refreshing prompts", ['error' => $ex->getMessage()]);
            throw new CopilotException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }



}
