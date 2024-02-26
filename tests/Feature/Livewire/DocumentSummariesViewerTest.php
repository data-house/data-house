<?php

namespace Tests\Feature\Livewire;

use App\Livewire\DocumentSummariesViewer;
use App\Models\Document;
use App\Models\DocumentSummary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Tests\TestCase;

class DocumentSummariesViewerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_renders_successfully()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->has(DocumentSummary::factory()->state([
                'text' => 'Existing summary',
            ]), 'summaries')
            ->create([
                'languages' => collect(LanguageAlpha2::English)
            ]);

        Livewire::test(DocumentSummariesViewer::class, ['document' => $document])
            ->assertStatus(200)
            ->assertSee('Existing summary');
    }
}
