<?php

namespace Tests\Unit\Pipelines;

use App\Models\Document;
use App\Models\User;
use App\Pipelines\Pipeline;
use App\Pipelines\PipelineStepConfiguration;
use App\Pipelines\Queue\PipelineJob;
use PHPUnit\Framework\TestCase;

class PipelineTest extends TestCase
{
    
    public function test_pipes_can_be_registered(): void
    {
        Pipeline::$pipelines = [];

        $step = new class extends PipelineJob {

        };

        Pipeline::define(Document::class, [
                get_class($step),
            ])
            ->description('Document Pipeline')
            ->name('A human name for this pipeline');

        $this->assertTrue(Pipeline::hasPipelines());

        $this->assertTrue(Pipeline::hasPipelines('Document'));

        $pipe = Pipeline::get('Document');

        $this->assertEquals('Document Pipeline', $pipe->description);
        $this->assertEquals('A human name for this pipeline', $pipe->name);
        $this->assertContainsOnlyInstancesOf(PipelineStepConfiguration::class, $pipe->steps);
    }
    
    public function test_pipes_can_be_json_serialized(): void
    {
        Pipeline::$pipelines = [];

        $step = new class extends PipelineJob {

        };

        $pipe = Pipeline::define('test', [
                get_class($step),
            ])
            ->name('Test')
            ->description('Test pipeline description');

        $serialized = $pipe->jsonSerialize();

        $this->assertArrayHasKey('key', $serialized);
        $this->assertArrayHasKey('name', $serialized);
        $this->assertArrayHasKey('description', $serialized);
        $this->assertArrayHasKey('steps', $serialized);
    }
}
