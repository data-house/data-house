<?php

namespace Tests\Feature\Pipelines;

use App\Models\Document;
use App\Pipelines\Models\PipelineRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PipelineRunModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_run_has_steps(): void
    {
        $run = PipelineRun::factory()
            ->for(Document::factory(), 'pipeable')
            ->hasSteps(4)
            ->create();

        $this->assertEquals(4, $run->steps()->count());
    }
}
