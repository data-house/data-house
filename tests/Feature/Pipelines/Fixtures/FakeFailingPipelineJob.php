<?php

namespace Tests\Feature\Pipelines\Fixtures;

use App\Jobs\Pipeline\Document\ExtractDocumentProperties;
use App\Models\Document;
use App\Pipelines\Models\PipelineRun;
use App\Pipelines\Models\PipelineStepRun;
use App\Pipelines\Pipeline;
use App\Pipelines\Queue\PipelineJob;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FakeFailingPipelineJob extends PipelineJob
{
    public function handle(): void
    {
        throw new Exception('Simulate a failure');
    }
}
