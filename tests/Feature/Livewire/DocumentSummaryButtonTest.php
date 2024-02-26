<?php

namespace Tests\Feature\Livewire;

use App\Jobs\Pipeline\Document\GenerateDocumentSummary;
use App\Livewire\DocumentSummaryButton;
use App\Models\Document;
use App\Models\DocumentSummary;
use App\Models\User;
use App\Pipelines\Models\PipelineRun;
use App\Pipelines\PipelineState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Tests\TestCase;

class DocumentSummaryButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_without_language_handled()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->create();
            
        Livewire::actingAs($user)->test(DocumentSummaryButton::class, ['document' => $document])
            ->assertStatus(200)
            ->assertDontSee('Generate a summary for the document')
            ->assertSet('documentId', $document->getKey());
    }


    public function test_summary_button_rendered_for_document_without_summary()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->create([
                'languages' => collect(LanguageAlpha2::English)
            ]);

        Livewire::actingAs($user)->test(DocumentSummaryButton::class, ['document' => $document])
            ->assertStatus(200)
            ->assertSee('Generate a summary for the document')
            ->assertSee('A summary is automatically generated in English.')
            ->assertSet('documentId', $document->getKey());
    }

    public function test_summary_button_not_rendered_when_document_has_summary()
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

        Livewire::actingAs($user)->test(DocumentSummaryButton::class, ['document' => $document])
            ->assertStatus(200)
            ->assertDontSee('Generate a summary for the document')
            ->assertSet('generatingSummary', false)
            ->assertSet('hasSummary', true);
    }
    
    public function test_summary_button_rendered_for_non_english_document_without_summary()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->create([
                'languages' => collect(LanguageAlpha2::Spanish_Castilian)
            ]);

        Livewire::actingAs($user)->test(DocumentSummaryButton::class, ['document' => $document])
            ->assertStatus(200)
            ->assertSee('Generate a summary for the document')
            ->assertSee('A summary is automatically generated in Spanish and English.')
            ->assertSet('documentId', $document->getKey());
    }

    public function test_user_can_request_summary_generation()
    {
        Queue::fake();

        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->create([
                'languages' => collect(LanguageAlpha2::English)
            ]);

        Livewire::actingAs($user)->test(DocumentSummaryButton::class, ['document' => $document])
            ->assertStatus(200)
            ->call('generateSummary')
            ->assertSee('Writing a summary for you...')
            ->assertSee('Summary generation in progress.')
            ->assertSet('generatingSummary', true);
        
        $pipelineRun = $document->fresh()->latestPipelineRun;

        $this->assertInstanceOf(PipelineRun::class, $pipelineRun);
        $this->assertEquals(PipelineState::CREATED, $pipelineRun->status);
        $this->assertEquals(GenerateDocumentSummary::class, $pipelineRun->job);

        Queue::assertPushed(GenerateDocumentSummary::class);
    }

    public function test_summary_generation_not_queued_twice()
    {
        Queue::fake([GenerateDocumentSummary::class]);

        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->hasPipelineRuns(1, ['status' => PipelineState::CREATED, 'job' => GenerateDocumentSummary::class])
            ->create([
                'languages' => collect(LanguageAlpha2::English)
            ]);

        Livewire::actingAs($user)->test(DocumentSummaryButton::class, ['document' => $document])
            ->assertStatus(200)
            ->assertSet('generatingSummary', true)
            ->call('generateSummary')
            ->assertSet('generatingSummary', true);
        
        $pipelineRun = $document->fresh()->latestPipelineRun;

        $this->assertInstanceOf(PipelineRun::class, $pipelineRun);
        $this->assertEquals(PipelineState::CREATED, $pipelineRun->status);
        $this->assertEquals(GenerateDocumentSummary::class, $pipelineRun->job);

        Queue::assertNothingPushed();
    }

    public function test_error_reported_when_summary_generation_fails()
    {
        config([
            'support.email' => null,
            'support.help' => null,
        ]);
        
        Queue::fake();

        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->hasPipelineRuns(1, ['status' => PipelineState::FAILED, 'job' => GenerateDocumentSummary::class])
            ->create([
                'languages' => collect(LanguageAlpha2::English)
            ]);

        Livewire::actingAs($user)->test(DocumentSummaryButton::class, ['document' => $document])
            ->assertStatus(200)
            ->assertSet('generatingSummary', false)
            ->assertSee('Generate a summary for the document')
            ->assertSee('A summary could not be generated at the moment. Please try again later.');
    }
    
    public function test_error_reported_with_support_option_when_summary_generation_fails()
    {
        config([
            'support.email' => 'ticket@ticket.localhost',
            'support.help' => null,
        ]);

        Queue::fake();

        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->hasPipelineRuns(1, ['status' => PipelineState::FAILED, 'job' => GenerateDocumentSummary::class])
            ->create([
                'languages' => collect(LanguageAlpha2::English)
            ]);

        Livewire::actingAs($user)->test(DocumentSummaryButton::class, ['document' => $document])
            ->assertStatus(200)
            ->assertSet('generatingSummary', false)
            ->assertSee('Generate a summary for the document')
            ->assertSee('A summary could not be generated at the moment. Please try later')
            ->assertSee('contact the support')
            ->assertSee('ticket@ticket.localhost');
    }
}
