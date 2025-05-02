<?php

namespace App\Copilot\Support\Testing\Fakes;

use App\Copilot\AnswerAggregationCopilotRequest;
use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
use App\Copilot\CopilotSummarizeRequest;
use App\Copilot\Questionable;
use App\Copilot\Engines\Engine;
use Illuminate\Support\Collection;
use Illuminate\Support\Testing\Fakes\Fake;
use OneOffTech\LibrarianClient\Dto\Document;
use PHPUnit\Framework\Assert as PHPUnit;

class FakeEngine extends Engine implements Fake
{

    protected Collection $copilotRequests;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->copilotRequests = collect();
    }


    public function syncLibrarySettings()
    {
        $this->copilotRequests->push(['method' => 'syncLibrarySettings', 'library' => $this->getLibrary(), 'carrying' => $this->getLibrarySettings()]);
    }

    public function assertLibraryConfigured()
    {
        PHPUnit::assertSame(
            1, $this->getInteractions('syncLibrarySettings')->count(),
            "Library settings not synchronized."
        );
    }
    
    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function update($models)
    {
        $objects = $models->map(function ($model) {

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

            return new Document(
                id: $model->getCopilotKey(),
                language: $questionableData['lang'],
                data: $questionableData['data']->toArray()
            );
        })->filter()->values();

        if ($objects->isEmpty()) {
            return;
        }

        $this->copilotRequests->push(['method' => 'update', 'library' => $this->getLibrary(), 'carrying' => $models, 'questionable' => $objects]);


    }

    /**
     * Assert the number of documents pushed to Copilot
     */
    public function assertDocumentsPushed(int $expectedCount)
    {
        $actualCount = $this->getInteractions('update')->sum(fn($e) => $e['carrying']->count());

        PHPUnit::assertSame(
            $expectedCount, $actualCount,
            "Expected {$expectedCount} documents pushed, found {$actualCount}."
        );
    }

    /**
     * Assert the specific document was pushed to Copilot
     */
    public function assertDocumentPushed(string $document_id)
    {
        $actual = $this->getInteractions('update')
            ->map(fn($e) => $e['carrying'])
            ->flatten(1)
            ->where(fn($e) => $e->getCopilotKey() === $document_id);

        PHPUnit::assertTrue(
            $actual->isNotEmpty(),
            "Expected document {$document_id} not pushed."
        );
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function delete($models)
    {
        $this->copilotRequests->push(['method' => 'delete', 'library' => $this->getLibrary(), 'carrying' => $models]);
    }

    /**
     * Assert the number of documents removed from Copilot
     */
    public function assertDocumentsRemoved(int $expectedCount)
    {
        $actualCount = $this->getInteractions('delete')->sum(fn($e) => $e['carrying']->count());

        PHPUnit::assertSame(
            $expectedCount, $actualCount,
            "Expected {$expectedCount} documents deleted, found {$actualCount}."
        );
    }

    /**
     * Assert the specific document was removed from Copilot
     */
    public function assertDocumentRemoved(string $document_id)
    {
        $actual = $this->getInteractions('delete')
            ->map(fn($e) => $e['carrying'])
            ->flatten(1)
            ->where(fn($e) => $e->getCopilotKey() === $document_id);

        PHPUnit::assertTrue(
            $actual->isNotEmpty(),
            "Expected document {$document_id} not removed."
        );
    }

    public function question(CopilotRequest $question): CopilotResponse
    {
        $this->copilotRequests->push(['method' => 'question', 'library' => $this->getLibrary(), 'carrying' => $question]);

        // TODO: figure out how to generate a response to use for testing

        return new CopilotResponse('');
    }

    /**
     * Assert the number of questions asked to Copilot
     */
    public function assertQuestionsAsked(int $expectedCount)
    {
        $actualCount = $this->getInteractions('question')->count();

        PHPUnit::assertSame(
            $expectedCount, $actualCount,
            "Expected {$expectedCount} questions, found {$actualCount}."
        );
    }

    public function aggregate(AnswerAggregationCopilotRequest $request): CopilotResponse
    {
        $this->copilotRequests->push(['method' => 'aggregate', 'library' => $this->getLibrary(), 'carrying' => $request]);

        // TODO: figure out how to generate a response to use for testing

        return new CopilotResponse('');
    }

    /**
     * Assert the number of questions asked to Copilot
     */
    public function assertAggregationssAsked(int $expectedCount)
    {
        $actualCount = $this->getInteractions('aggregate')->count();

        PHPUnit::assertSame(
            $expectedCount, $actualCount,
            "Expected {$expectedCount} aggregations, found {$actualCount}."
        );
    }
    
    public function summarize(CopilotSummarizeRequest $request): CopilotResponse
    {
        $this->copilotRequests->push(['method' => 'summarize', 'library' => $this->getLibrary(), 'carrying' => $request]);

        // TODO: figure out how to generate a response to use for testing

        return new CopilotResponse('');
    }

    public function addClassifier(string $classifier, string $url): string
    {
        $this->copilotRequests->push(['method' => 'addClassifier', 'library' => $this->getLibrary(), 'carrying' => func_get_args()]);

        return $classifier;
    }
    
    public function removeClassifier(string $classifier): void
    {
        $this->copilotRequests->push(['method' => 'removeClassifier', 'library' => $this->getLibrary(), 'carrying' => func_get_args()]);
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
        $this->copilotRequests->push(['method' => 'refreshPrompts', 'library' => $this->getLibrary(), 'carrying' => null]);

        return 'ok';
    }

    public function assertPromptsRefreshed()
    {
        PHPUnit::assertSame(
            1, $this->getInteractions('refreshPrompts')->count(),
            "Prompts not refreshed."
        );
    }





    
    /**
     * Get all interactions regarding a specific method
     */
    protected function getInteractions(string $method): Collection
    {
        return $this->copilotRequests->where('method', $method);
    }

    /**
     * Assert the library identified is the same as the engine retrieves from the configuration.
     *
     * @return void
     */
    public function assertLibraryIs(string $library)
    {
        PHPUnit::assertSame(
            $library, $this->getLibrary(),
            "Expected {$library} as identified, but found {$this->getLibrary()} instead."
        );
    }


    /**
     * Assert the total count of copilot interactions performed.
     *
     * @param  int  $expectedCount
     * @return void
     */
    public function assertCount($expectedCount)
    {
        $actualCount = $this->copilotRequests->count();

        PHPUnit::assertSame(
            $expectedCount, $actualCount,
            "Expected {$expectedCount} Copilot interactions, but found {$actualCount} instead."
        );
    }
    
    /**
     * Assert the no copilot interactions were performed.
     *
     * @return void
     */
    public function assertNoCopilotInteractions()
    {
        $this->assertCount(0);
    }

}