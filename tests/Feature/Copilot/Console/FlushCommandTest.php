<?php

namespace Tests\Feature\Copilot\Console;

use App\Copilot\Facades\Copilot;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FlushCommandTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_questionable_models_can_be_flushed(): void
    {
        $copilot = Copilot::fake();

        Queue::fake();

        $document = Document::factory()->create();

        $this->artisan('copilot:flush', [
                'model' => Document::class
            ])
            ->assertSuccessful()
            ->expectsOutput('All [App\Models\Document] records have been removed.');
        
        $copilot->assertDocumentsRemoved(1);
    }
}
