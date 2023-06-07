<?php

namespace Tests\Unit\Pipelines;

use App\Models\Document;
use App\Models\User;
use App\Pipelines\Pipeline;
use App\Pipelines\PipelineStepConfiguration;
use App\Pipelines\PipelineTrigger;
use App\Pipelines\Queue\PipelineJob;
use PHPUnit\Framework\TestCase;
use Tests\Feature\Pipelines\Fixtures\FakePipelineJob;

class PipelineTest extends TestCase
{
    
    public function test_pipes_can_be_registered(): void
    {
        Pipeline::$pipelines = [];

        Pipeline::define(Document::class, PipelineTrigger::ALWAYS, [
                FakePipelineJob::class,
            ])
            ->description('Document Pipeline')
            ->name('A human name for this pipeline');

        $this->assertTrue(Pipeline::hasPipelines());

        $this->assertTrue(Pipeline::hasPipelines('Document'));

        $pipe = Pipeline::get('Document', PipelineTrigger::ALWAYS);

        $this->assertEquals('Document Pipeline', $pipe->description);
        $this->assertEquals(PipelineTrigger::ALWAYS, $pipe->trigger);
        $this->assertEquals('A human name for this pipeline', $pipe->name);
        $this->assertContainsOnlyInstancesOf(PipelineStepConfiguration::class, $pipe->steps);
    }
    
    public function test_pipes_can_be_json_serialized(): void
    {
        Pipeline::$pipelines = [];


        $pipe = Pipeline::define('test',  PipelineTrigger::MODEL_CREATED, [
            FakePipelineJob::class,
            ])
            ->name('Test')
            ->description('Test pipeline description');

        $serialized = $pipe->jsonSerialize();

        $this->assertArrayHasKey('key', $serialized);
        $this->assertArrayHasKey('name', $serialized);
        $this->assertArrayHasKey('trigger', $serialized);
        $this->assertArrayHasKey('description', $serialized);
        $this->assertArrayHasKey('steps', $serialized);
    }
}
