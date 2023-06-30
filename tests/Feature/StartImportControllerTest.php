<?php

namespace Tests\Feature;

use App\Jobs\StartImportJob;
use App\Models\Import;
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
            ->has(Import::factory())
            ->create();

        $import = Import::first();

        $response = $this
            ->actingAs($user)
            ->post('/imports-start/', [
                'import' => $import->getKey(),
            ]);

        $response->assertRedirectToRoute('imports.show', $import);

        Queue::assertPushed(StartImportJob::class, function($job) use ($import){
            return $job->import->is($import);
        });

        $this->assertEquals(ImportStatus::RUNNING, $import->fresh()->status);
    }
}
