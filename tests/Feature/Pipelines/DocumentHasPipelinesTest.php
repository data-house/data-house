<?php

namespace Tests\Feature\Pipelines;

use App\Models\Document;
use App\Pipelines\Models\PipelineRun;
use App\Pipelines\Pipeline;
use App\Pipelines\PipelineState;
use App\Pipelines\PipelineTrigger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Pipelines\Fixtures\FakePipelineJob;
use Tests\TestCase;

class DocumentHasPipelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_has_pipeline_runs(): void
    {
        Pipeline::$pipelines = [];

        Pipeline::define(Document::class, PipelineTrigger::ALWAYS, [
            FakePipelineJob::class,
        ]);

        $document = Document::factory()->hasPipelineRuns(2)->create();

        $this->assertEquals(2, $document->pipelineRuns()->count());
        $this->assertTrue($document->pipelineRuns()->first()->pipeable->is($document));
    }
    
    public function test_document_has_latest_pipeline_run(): void
    {
        Pipeline::$pipelines = [];

        Pipeline::define(Document::class, PipelineTrigger::ALWAYS, [
            FakePipelineJob::class,
        ]);

        PipelineRun::factory()
            ->count(2)
            ->sequence(
                ['created_at' => now()->subHours(4)],
                ['created_at' => now()->subHours(2)],
            )
            ->for(Document::factory(), 'pipeable')
            ->create();

        $document = Document::first();

        $this->assertTrue($document->latestPipelineRun->is(PipelineRun::query()->latest()->first()));
    }
    
    public function test_document_has_active_pipelines(): void
    {
        Queue::fake();

        Pipeline::$pipelines = [];

        Pipeline::define(Document::class, PipelineTrigger::ALWAYS, [
            FakePipelineJob::class,
        ]);

        PipelineRun::factory()
            ->count(2)
            ->sequence(
                ['created_at' => now()->subHours(4), 'status' => PipelineState::COMPLETED],
                ['created_at' => now()->subHours(2), 'status' => PipelineState::QUEUED],
            )
            ->for(Document::factory(), 'pipeable')
            ->create();

        $document = Document::first();

        $this->assertTrue($document->hasActivePipelines());
    }
}
