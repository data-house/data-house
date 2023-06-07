<?php

namespace Tests\Feature\Pipelines;

use App\Jobs\Pipeline\Document\ExtractDocumentProperties;
use App\Models\Document;
use App\Pipelines\Models\PipelineRun;
use App\Pipelines\Pipeline;
use App\Pipelines\PipelineState;
use App\Pipelines\PipelineTrigger;
use App\Pipelines\Queue\PipelineJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Pipelines\Fixtures\FakeFailingPipelineJob;
use Tests\Feature\Pipelines\Fixtures\FakePipelineJob;
use Tests\TestCase;

class PipelineExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipeline_dispatched(): void
    {
        Queue::fake();

        Pipeline::$pipelines = [];

        Pipeline::define(Document::class, PipelineTrigger::ALWAYS, [
            FakePipelineJob::class,
        ]);

        $document = Document::factory()->create();

        $document->dispatchPipeline();

        $run = PipelineRun::first();

        $this->assertTrue($run->pipeable->is($document));

        $this->assertEquals(FakePipelineJob::class, $run->job);

        Queue::assertPushed(FakePipelineJob::class, function($job) use ($document, $run){
            return $job->model->is($document) && $job->run instanceof PipelineRun;
        });
    }

    public function test_pipeline_run_completed(): void
    {
        Pipeline::$pipelines = [];

        Pipeline::define(Document::class, PipelineTrigger::ALWAYS, [
            FakePipelineJob::class,
        ]);

        $document = Document::factory()->create();

        $document->dispatchPipeline();

        $run = PipelineRun::first();

        $this->assertTrue($run->pipeable->is($document));
        $this->assertEquals(PipelineState::COMPLETED, $run->status);
        $this->assertEquals(PipelineTrigger::ALWAYS, $run->trigger);
        $this->assertEquals(FakePipelineJob::class, $run->job);
    }

    public function test_pipeline_run_failed(): void
    {
        Pipeline::$pipelines = [];

        Pipeline::define(Document::class, PipelineTrigger::ALWAYS, [
            FakeFailingPipelineJob::class,
        ]);

        $document = Document::factory()->create();

        $document->dispatchPipeline();

        $run = PipelineRun::first();

        $this->assertTrue($run->pipeable->is($document));
        $this->assertEquals(PipelineState::FAILED, $run->status);
        $this->assertEquals(FakeFailingPipelineJob::class, $run->job);
    }
}
