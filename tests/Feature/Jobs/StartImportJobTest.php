<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StartImportJob;
use App\Jobs\RetrieveDocumentsToImportJob;
use App\Models\Disk;
use App\Models\Import;
use App\Models\ImportMap;
use App\Models\ImportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StartImportJobTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_import_can_be_started(): void
    {
        Queue::fake();

        $import = Import::factory()
            ->has(ImportMap::factory(), 'maps')
            ->create([
                'status' => ImportStatus::CREATED,
            ]);

        $import->start();

        Queue::assertPushed(StartImportJob::class, function ($job) use ($import) {
            return $job->import->is($import);
        });
        
        $this->assertEquals(ImportStatus::RUNNING, $import->fresh()->status);
        $this->assertEquals(ImportStatus::CREATED, $import->fresh()->maps()->first()->status);
    }

    public function test_completed_maps_are_not_started(): void
    {
        Queue::fake();

        $import = Import::factory()
            ->has(ImportMap::factory()->state([
                'status' => ImportStatus::COMPLETED
            ]), 'maps')
            ->create([
                'status' => ImportStatus::COMPLETED,
            ]);

        $import->start();

        Queue::assertNothingPushed();
        
        $this->assertEquals(ImportStatus::COMPLETED, $import->fresh()->status);
        $this->assertEquals(ImportStatus::COMPLETED, $import->fresh()->maps()->first()->status);
    }
    
    public function test_failed_maps_are_not_started(): void
    {
        Bus::fake();

        $import = Import::factory()
            ->has(ImportMap::factory()->state([
                'status' => ImportStatus::FAILED
            ]), 'maps')
            ->create([
                'status' => ImportStatus::CREATED,
            ]);

        
        $map = $import->maps->first();

        (new StartImportJob($import))->handle();

        Bus::assertNothingDispatched();
        
        $this->assertEquals(ImportStatus::CREATED, $import->fresh()->status);
        $this->assertEquals(ImportStatus::FAILED, $import->fresh()->maps()->first()->status);
    }


    public function test_import_start_job_triggers_import_maps()
    {
        Storage::fake(Disk::IMPORTS->value);

        Queue::fake();

        $import = Import::factory()
            ->has(ImportMap::factory(), 'maps')
            ->create([
                'status' => ImportStatus::CREATED,
            ]);

        $map = $import->maps->first();

        (new StartImportJob($import))->handle();

        $this->assertEquals(ImportStatus::RUNNING, $map->fresh()->status);
        $this->assertNotNull($map->fresh()->last_session_started_at);

        Queue::assertPushed(RetrieveDocumentsToImportJob::class, function($job) use ($map) {
            return $job->importMap->is($map);
        });
    }
}
