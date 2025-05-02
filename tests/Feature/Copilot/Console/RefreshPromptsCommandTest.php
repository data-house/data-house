<?php

namespace Tests\Feature\Copilot\Console;

use App\Copilot\Facades\Copilot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RefreshPromptsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_prompts_refreshed(): void
    {
        $copilot = Copilot::fake();

        $this->artisan('copilot:refresh-prompts')->assertOk()->expectsOutput('ok');

        $copilot->assertPromptsRefreshed();
    }
}
