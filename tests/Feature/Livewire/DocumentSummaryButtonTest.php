<?php

namespace Tests\Feature\Livewire;

use App\Livewire\DocumentSummaryButton;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentSummaryButtonTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(DocumentSummaryButton::class)
            ->assertStatus(200);
    }
}
