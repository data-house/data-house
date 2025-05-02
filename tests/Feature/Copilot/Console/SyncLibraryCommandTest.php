<?php

namespace Tests\Feature\Copilot\Console;

use App\Copilot\Facades\Copilot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SyncLibraryCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_settings_syncronized(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.engines.cloud' => [
                'library' => 'library-id',
            ],
        ]);

        $fake = Copilot::fake();

        $this->artisan('copilot:sync-library-settings')
            ->assertOk()
            ->expectsOutput("Settings for the [library-id] library synced successfully.");

        $fake->assertLibraryIs('library-id');

        $fake->assertLibraryConfigured();
    }
}
