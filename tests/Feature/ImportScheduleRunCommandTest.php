<?php

namespace Tests\Feature;

use App\Jobs\StartImportJob;
use App\Models\Import;
use App\Models\ImportMap;
use App\Models\ImportSchedule;
use App\Models\ImportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ImportScheduleRunCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_one_shot_import_not_scheduled(): void
    {
        Queue::fake();

        $import = Import::factory()
            ->state(['status' => ImportStatus::CREATED])
            ->has(ImportMap::factory()->scheduled(ImportSchedule::NOT_SCHEDULED), 'maps')
            ->create();

        $map = $import->maps()->first();

        $this->artisan('import:schedule-run')
            ->expectsOutputToContain('No import maps to run.')
            ->assertExitCode(0);

        Queue::assertNothingPushed();
    }

    public function test_no_import_due_to_schedule(): void
    {
        Queue::fake();

        $import = Import::factory()
            ->state(['status' => ImportStatus::CREATED])
            ->has(ImportMap::factory()->scheduled(ImportSchedule::EVERY_TWO_HOURS), 'maps')
            ->create();

        $map = $import->maps()->first();

        $this->travelTo('2024-03-05 17:00', function() use ($map): void{

            $this->artisan('import:schedule-run')
                ->expectsOutputToContain('No import maps to run.')
                ->assertExitCode(0);
        });


        Queue::assertNothingPushed();
    }

    public function test_created_import_queued(): void
    {
        Queue::fake();

        $import = Import::factory()
            ->state(['status' => ImportStatus::CREATED])
            ->has(ImportMap::factory()->scheduled(ImportSchedule::EVERY_TWO_HOURS), 'maps')
            ->create();

        $map = $import->maps()->first();

        $this->travelTo('2024-03-05 18:00', function() use ($map): void{

            

            $this->artisan('import:schedule-run')
                ->expectsOutputToContain('1 import map to run')
                ->assertExitCode(0);
        });

        Queue::assertPushed(StartImportJob::class, function($job) use ($import){
            return $job->import->is($import);
        });

        $this->assertEquals(ImportStatus::RUNNING, $import->fresh()->status);

        $this->assertEquals(ImportStatus::CREATED, $import->maps()->first()->status);
    }

    public function test_completed_import_queued(): void
    {
        Queue::fake();

        $import = Import::factory()
            ->state(['status' => ImportStatus::COMPLETED])
            ->has(ImportMap::factory(['status' => ImportStatus::COMPLETED])->scheduled(ImportSchedule::EVERY_TWO_HOURS), 'maps')
            ->create();

        $map = $import->maps()->first();

        $this->travelTo('2024-03-05 18:00', function() use ($map): void{

            

            $this->artisan('import:schedule-run')
                ->expectsOutputToContain('1 import map to run')
                ->assertExitCode(0);
        });

        Queue::assertPushed(StartImportJob::class, function($job) use ($import){
            return $job->import->is($import);
        });

        $this->assertEquals(ImportStatus::RUNNING, $import->fresh()->status);

        $this->assertEquals(ImportStatus::CREATED, $import->maps()->first()->status);
    }

    public function test_failed_import_queued(): void
    {
        Queue::fake();

        $import = Import::factory()
            ->state(['status' => ImportStatus::FAILED])
            ->has(ImportMap::factory(['status' => ImportStatus::FAILED])->scheduled(ImportSchedule::EVERY_TWO_HOURS), 'maps')
            ->create();

        $map = $import->maps()->first();

        $this->travelTo('2024-03-05 18:00', function() use ($map): void{

            

            $this->artisan('import:schedule-run')
                ->expectsOutputToContain('1 import map to run')
                ->assertExitCode(0);
        });

        Queue::assertPushed(StartImportJob::class, function($job) use ($import){
            return $job->import->is($import);
        });

        $this->assertEquals(ImportStatus::RUNNING, $import->fresh()->status);

        $this->assertEquals(ImportStatus::CREATED, $import->maps()->first()->status);
    }
    
    public function test_currently_running_maps_skipped_if_due(): void
    {
        Queue::fake();

        $import = Import::factory()
            ->state(['status' => ImportStatus::RUNNING])
            ->has(ImportMap::factory(['status' => ImportStatus::RUNNING])->scheduled(ImportSchedule::EVERY_TWO_HOURS), 'maps')
            ->create();

        $map = $import->maps()->first();

        $this->travelTo('2024-03-05 18:00', function() use ($map): void{

            

            $this->artisan('import:schedule-run')
                ->expectsOutputToContain('No import maps to run')
                ->assertExitCode(0);
        });

        Queue::assertNothingPushed();

        $this->assertEquals(ImportStatus::RUNNING, $import->fresh()->status);

        $this->assertEquals(ImportStatus::RUNNING, $import->maps()->first()->status);
    }
    
    public function test_multiple_imports_queued(): void
    {
        Queue::fake();

        $importOne = Import::factory()
            ->state(['status' => ImportStatus::COMPLETED])
            ->has(ImportMap::factory(['status' => ImportStatus::COMPLETED])->scheduled(ImportSchedule::EVERY_TWO_HOURS), 'maps')
            ->create();
        
        $importTwo = Import::factory()
            ->state(['status' => ImportStatus::COMPLETED])
            ->has(ImportMap::factory(['status' => ImportStatus::COMPLETED])->scheduled(ImportSchedule::EVERY_SIX_HOURS), 'maps')
            ->create();

        $this->travelTo('2024-03-05 18:00', function(): void{

            $this->artisan('import:schedule-run')
                ->expectsOutputToContain('2 import map to run')
                ->assertExitCode(0);
        });

        Queue::assertPushed(StartImportJob::class, 2);

        Queue::assertPushed(StartImportJob::class, function($job) use ($importOne){
            return $job->import->is($importOne);
        });

        Queue::assertPushed(StartImportJob::class, function($job) use ($importTwo){
            return $job->import->is($importTwo);
        });
    }
    
}
