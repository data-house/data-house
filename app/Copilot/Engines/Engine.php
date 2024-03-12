<?php

namespace App\Copilot\Engines;

use App\Copilot\AnswerAggregationCopilotRequest;
use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
use App\Copilot\CopilotSummarizeRequest;
use Carbon\Carbon;

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
    protected function getLibrary(): string
    {
        return str(config('app.url', 3))->slug()->toString();
    }

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
    
}
