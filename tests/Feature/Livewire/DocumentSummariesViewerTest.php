<?php

namespace Tests\Feature\Livewire;

use App\Livewire\DocumentSummariesViewer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentSummariesViewerTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(DocumentSummariesViewer::class)
            ->assertStatus(200);
    }
}
