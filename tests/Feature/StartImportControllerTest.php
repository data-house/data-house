<?php

namespace Tests\Feature;

use App\Jobs\StartImportJob;
use App\Models\Import;
use App\Models\ImportMap;
use App\Models\ImportStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StartImportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_can_be_started_by_authorized_user(): void
    {
        Queue::fake();

        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->has(Import::factory()
                ->state(['status' => ImportStatus::CREATED])
                ->has(ImportMap::factory(), 'maps'))
            ->create();

        $import = Import::first();

        $response = $this
            ->actingAs($user)
            ->from(route('imports.show', $import))
            ->post('/imports-start/', [
                'import' => $import->getKey(),
            ]);

        $response->assertRedirectToRoute('imports.show', $import);

        Queue::assertPushed(StartImportJob::class, function($job) use ($import){
            return $job->import->is($import);
        });

        $this->assertEquals(ImportStatus::RUNNING, $import->fresh()->status);
    }

    public function test_import_qithout_import_maps_is_not_started(): void
    {
        Queue::fake();

        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->has(Import::factory()->state(['status' => ImportStatus::CREATED]))
            ->create();

        $import = Import::first();

        $response = $this
            ->actingAs($user)
            ->from(route('imports.show', $import))
            ->post('/imports-start/', [
                'import' => $import->getKey(),
            ]);

        $response->assertRedirectToRoute('imports.show', $import);

        Queue::assertNothingPushed();

        $this->assertEquals(ImportStatus::CREATED, $import->fresh()->status);
    }

    public function test_cannot_start_import_created_by_another_user(): void
    {
        Queue::fake();

        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->has(Import::factory())
            ->create();

        $import = Import::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('imports.index'))
            ->post('/imports-start/', [
                'import' => $import->getKey(),
            ]);

        $response->assertRedirectToRoute('imports.index');

        $response->assertSessionHasErrors('import');

        Queue::assertNothingPushed();
    }

    public function test_guest_cannot_start_import(): void
    {
        Queue::fake();

        $user = User::factory()
            ->guest()
            ->has(Import::factory())
            ->create();

        $import = Import::first();

        $response = $this
            ->actingAs($user)
            ->from(route('imports.index'))
            ->post('/imports-start/', [
                'import' => $import->getKey(),
            ]);

        $response->assertForbidden();

        Queue::assertNothingPushed();
    }
}
