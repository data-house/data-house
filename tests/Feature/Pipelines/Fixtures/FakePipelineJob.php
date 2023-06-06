<?php

namespace Tests\Feature\Pipelines\Fixtures;

use App\Jobs\Pipeline\Document\ExtractDocumentProperties;
use App\Models\Document;
use App\Pipelines\Models\PipelineRun;
use App\Pipelines\Models\PipelineStepRun;
use App\Pipelines\Pipeline;
use App\Pipelines\Queue\PipelineJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FakePipelineJob extends PipelineJob
{
    public function handle(): void
    {
        // this job does nothing
    }
}
