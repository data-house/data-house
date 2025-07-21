<?php

namespace App\Copilot\Engines;

use App\Copilot\AnswerAggregationCopilotRequest;
use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
use App\Copilot\CopilotSummarizeRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use OneOffTech\LibrarianClient\Dto\Document;
use OneOffTech\LibrarianClient\Dto\Extraction;
use OneOffTech\LibrarianClient\Dto\LibraryConfiguration;

abstract class Engine
{
    // TODO: Maybe a Copilot should have different engines based on purposes, e.g. document chat, summary, ...

    protected readonly array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get the configured request timeout in seconds
     */
    protected function getRequestTimeout(): int
    {
        return config('copilot.timeout', 3) * Carbon::SECONDS_PER_MINUTE;
    }
    
    /**
     * Get the library tenant
     */
    public function getLibrary(): string
    {
        return $this->config['library'] ?? str(config('app.url'))->slug()->toString();
    }
    
    /**
     * Get the library name
     */
    public function getLibraryName(): string
    {
        return config('app.name', 'Data House');
    }

    protected function getLibrarySettings(): LibraryConfiguration
    {
        return new LibraryConfiguration(
            database: [
                "index_fields" => $this->config['library-settings']['indexed-fields'] ?? ['resource_id']
            ],
            text: $this->config['library-settings']['text-processing'] ?? [
                "n_context_chunk" => 10,
                "chunk_length" => 490,
                "chunk_overlap" => 10
            ]
        );
    }


    abstract public function syncLibrarySettings();

    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    abstract public function update($models);

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    abstract public function delete($models);

    /**
     * Ask the question to the engine.
     *
     * @param  \App\Copilot\CopilotRequest  $question
     * @return \App\Copilot\CopilotResponse
     */
    abstract public function question(CopilotRequest $question): CopilotResponse;

    abstract public function aggregate(AnswerAggregationCopilotRequest $request): CopilotResponse;

    /**
     * Summarize a text
     * 
     * @param  \App\Copilot\CopilotSummarizeRequest  $request
     * @return \App\Copilot\CopilotResponse
     */
    abstract public function summarize(CopilotSummarizeRequest $request): CopilotResponse;

    /**
     * Direct chat message
     * 
     * @return \App\Copilot\CopilotResponse
     */
    abstract public function chat(string $user, string $prompt, ?string $chatId = null): CopilotResponse;

    /**
     * Add a text classifier
     */
    abstract public function addClassifier(string $classifier, string $url): string;
    
    /**
     * Remove a text classifier
     */
    abstract public function removeClassifier(string $classifier): void;

    /**
     * Classify models using the specified classifier
     * 
     * @param string $classifier
     * @param mixed $model
     * @return \Illuminate\Support\Collection
     */
    abstract public function classify(string $classifier, $model): Collection;

    /**
     * Classify text using the specified classifier
     * 
     * @param string $classifier
     * @param mixed $model
     * @return \Illuminate\Support\Collection
     */
    abstract public function classifyText(string $classifier, string $text): Collection;

    abstract public function extract(string $structuredResponseModel, Document $from, ?array $sections = null, ?string $instructions = null): Extraction;

    /**
     * Refresh the configured prompts in Copilot
     */
    abstract public function refreshPrompts(): string;

}
