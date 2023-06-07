<?php

namespace Tests\Feature\Pipelines\Fixtures;

use App\Pipelines\Queue\PipelineJob;

class FakePipelineJob extends PipelineJob
{
    public function handle(): void
    {
        // this job does nothing
    }
}
