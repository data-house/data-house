<?php

namespace App\Copilot\Engines;

use App\Copilot\AnswerAggregationCopilotRequest;
use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
use App\Copilot\CopilotSummarizeRequest;
use Illuminate\Support\Collection;

class NullEngine extends Engine
{
    public function syncLibrarySettings()
    {

    }
    
    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function update($models)
    {
        //
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function delete($models)
    {
        //
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

    public function aggregate(AnswerAggregationCopilotRequest $request): CopilotResponse
    {
        return new CopilotResponse('');
    }
    
    public function summarize(CopilotSummarizeRequest $request): CopilotResponse
    {
        return new CopilotResponse('');
    }

    public function addClassifier(string $classifier, string $url): string
    {
        return 'classifier_id';
    }
    
    public function removeClassifier(string $classifier): void
    {

    }

    public function classify(string $classifier, $model): Collection
    {
        return collect();
    }

    public function classifyText(string $classifier, string $text, string $lang = 'en'): Collection
    {
        return collect();
    }

    public function refreshPrompts(): string
    {
        return 'ok';
    }
    
}
