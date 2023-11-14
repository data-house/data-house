<?php

namespace Tests\Feature\Copilot\Console;

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
        config([
            'copilot.driver' => 'null',
        ]);

        Queue::fake();

        $document = Document::factory()->create();

        Http::preventStrayRequests();

        $this->artisan('copilot:flush', [
                'model' => Document::class
            ])
            ->assertSuccessful()
            ->expectsOutput('Removed [App\Models\Document] models up to ID: '.$document->getKey())
            ->expectsOutput('All [App\Models\Document] records have been removed.');
    }
}
