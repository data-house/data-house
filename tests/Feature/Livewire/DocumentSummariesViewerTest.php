<?php

namespace Tests\Feature\Livewire;

use App\Livewire\DocumentSummariesViewer;
use App\Models\Document;
use App\Models\DocumentSummary;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Tests\TestCase;

class DocumentSummariesViewerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_single_summary_rendered()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->has(
                DocumentSummary::factory()->count(2)->sequence(
                    [
                        'all_document' => true,
                        'text' => 'Existing summary',
                        'language' => LanguageAlpha2::English,
                        'created_at' => now()->subMinute(),
                    ],
                    [
                        'all_document' => false,
                        'text' => 'Section summary',
                        'language' => LanguageAlpha2::English,
                    ]
                )
            , 'summaries')
            ->createQuietly([
                'languages' => collect(LanguageAlpha2::English)
            ]);

        Livewire::test(DocumentSummariesViewer::class, ['document' => $document])
            ->assertStatus(200)
            ->assertSee('Existing summary')
            ->assertDontSee('Section summary');
    }
    
    public function test_latest_summary_rendered_for_each_language()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->has(DocumentSummary::factory()
                ->count(4)
                ->state(new Sequence(
                    [
                        'all_document' => true,
                        'text' => 'First summary',
                        'language' => LanguageAlpha2::English,
                        'created_at' => now()->subDays(5),
                    ],
                    [
                        'all_document' => true,
                        'text' => 'Second summary',
                        'language' => LanguageAlpha2::English,
                    ],
                    [
                        'all_document' => true,
                        'text' => 'Spanish summary',
                        'language' => LanguageAlpha2::Spanish_Castilian,
                    ],
                    [
                        'all_document' => false,
                        'text' => 'Partial summary',
                        'language' => LanguageAlpha2::Spanish_Castilian,
                    ]
                ))
                , 'summaries')
            ->create([
                'languages' => collect(LanguageAlpha2::English)
            ]);

        Livewire::test(DocumentSummariesViewer::class, ['document' => $document])
            ->assertStatus(200)
            ->assertSee('Second summary')
            ->assertSee('Spanish summary');
    }
}
