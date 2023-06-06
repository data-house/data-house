<?php

namespace Tests\Feature\Pipelines;

use App\Models\Document;
use App\Pipelines\Models\PipelineRun;
use App\Pipelines\Pipeline;
use App\Pipelines\Queue\PipelineJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DocumentHasPipelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_has_pipeline_runs(): void
    {
        Pipeline::$pipelines = [];

        $step = new class extends PipelineJob {

        };

        Pipeline::define(Document::class, [
                get_class($step),
            ]);

        $document = Document::factory()->hasPipelineRuns(2)->create();

        $this->assertEquals(2, $document->pipelineRuns()->count());
        $this->assertTrue($document->pipelineRuns()->first()->pipeable->is($document));
    }
    
    public function test_document_has_latest_pipeline_run(): void
    {
        Pipeline::$pipelines = [];

        $step = new class extends PipelineJob {

        };

        Pipeline::define(Document::class, [
                get_class($step),
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
}
