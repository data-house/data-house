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
        $this->assertEquals(ImportStatus::RUNNING, $import->fresh()->maps()->first()->status);
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


        Queue::assertPushed(RetrieveDocumentsToImportJob::class, function($job) use ($map) {
            return $job->importMap->is($map);
        });
    }
}
