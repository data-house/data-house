<?php

namespace Tests\Feature;

use App\Jobs\StartImportJob;
use App\Models\Import;
use App\Models\ImportDocument;
use App\Models\ImportMap;
use App\Models\ImportStatus;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RetryImportMapCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_retry_not_possible_for_created_maps(): void
    {
        Queue::fake();

        $import = Import::factory()
            ->state(['status' => ImportStatus::CREATED])
            ->has(ImportMap::factory(), 'maps')
            ->create();

        $map = $import->maps()->first();

        $this->artisan('import:retry-map', [
                'map' => $map->ulid,
            ])
            ->expectsOutputToContain('Import map is required to be completed or failed. Found [CREATED]')
            ->assertExitCode(2);

        Queue::assertNothingPushed();
    }
    
    public function test_retry_not_possible_for_completed_maps_without_force_option(): void
    {
        Queue::fake();

        $import = Import::factory()
            ->state(['status' => ImportStatus::COMPLETED])
            ->has(ImportMap::factory()->state(['status' => ImportStatus::COMPLETED]), 'maps')
            ->create();

        $map = $import->maps()->first();

        $this->artisan('import:retry-map', [
                'map' => $map->ulid,
            ])
            ->expectsOutputToContain('Import map completed. Use --force to retry a completed import map.')
            ->assertExitCode(2);

        Queue::assertNothingPushed();
    }

    public function test_retry_possible_for_failed_maps(): void
    {
        Queue::fake();

        $import = Import::factory()
            ->state(['status' => ImportStatus::FAILED])
            ->has(ImportMap::factory()->state(['status' => ImportStatus::FAILED]), 'maps')
            ->create();

        $map = $import->maps()->first();

        $this->artisan('import:retry-map', [
                'map' => $map->ulid,
            ])
            ->assertSuccessful();

        Queue::assertPushed(StartImportJob::class, function($job) use ($import){
            return $job->import->is($import);
        });

        $this->assertEquals(ImportStatus::RUNNING, $import->fresh()->status);

        $this->assertEquals(ImportStatus::CREATED, $import->maps()->first()->status);
    }

    public function test_retry_possible_using_map_id(): void
    {
        Queue::fake();

        $import = Import::factory()
            ->state(['status' => ImportStatus::FAILED])
            ->has(ImportMap::factory()->state(['status' => ImportStatus::FAILED]), 'maps')
            ->create();

        $map = $import->maps()->first();

        $this->artisan('import:retry-map', [
                'map' => $map->getKey(),
            ])
            ->assertSuccessful();

        Queue::assertPushed(StartImportJob::class, function($job) use ($import){
            return $job->import->is($import);
        });

        $this->assertEquals(ImportStatus::RUNNING, $import->fresh()->status);

        $this->assertEquals(ImportStatus::CREATED, $import->maps()->first()->status);
    }

    public function test_retry_possible_for_completed_maps(): void
    {
        Queue::fake();

        $import = Import::factory()
            ->state(['status' => ImportStatus::COMPLETED])
            ->has(ImportMap::factory()->state(['status' => ImportStatus::COMPLETED]), 'maps')
            ->create();

        $map = $import->maps()->first();

        $this->artisan('import:retry-map', [
                'map' => $map->ulid,
                '--force' => true,
            ])
            ->assertSuccessful();

        Queue::assertPushed(StartImportJob::class, function($job) use ($import){
            return $job->import->is($import);
        });

        $this->assertEquals(ImportStatus::RUNNING, $import->fresh()->status);

        $this->assertEquals(ImportStatus::CREATED, $import->maps()->first()->status);
    }
}
