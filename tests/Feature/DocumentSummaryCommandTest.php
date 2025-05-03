<?php

namespace Tests\Feature;

use App\Copilot\CopilotResponse;
use App\Copilot\CopilotSummarizeRequest;
use App\Copilot\Facades\Copilot;
use App\Models\Document;
use App\Models\DocumentSummary;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\Facades\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Tests\TestCase;

class DocumentSummaryCommandTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_command_generates_abstract_for_document(): void
    {
        $copilot = Copilot::fake()
            ->withSummary(new CopilotResponse("Summary."));

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("Content of the document")
        ]);

        Storage::fake('local');

        $document = Document::factory()->createQuietly([
            'properties' => [
                'pages' => 20,
            ],
            'description' => null,
        ]);

        $this->artisan('document:summary', [
            'documents' => [$document->ulid],
        ])
        ->assertSuccessful();

        $updatedDocument = $document->fresh();

        $summary = $updatedDocument->summaries()->first();

        $this->assertInstanceOf(DocumentSummary::class, $summary);
        $this->assertEquals('Summary.', $summary->text);
        $this->assertEquals(LanguageAlpha2::English, $summary->language);
        $this->assertTrue($summary->ai_generated);
        $this->assertNull($updatedDocument->description);

        $pdfDriver->assertCount(1);

        $copilot->assertSummariesGenerated(1);

        $copilot->assertSummaryFor(new CopilotSummarizeRequest($document->ulid, 'Content of the document', LanguageAlpha2::English));
    }
    
    
    public function test_abstract_generated_in_requested_language(): void
    {
        $copilot = Copilot::fake()
            ->withSummary(new CopilotResponse("Summary."));

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("Content of the document")
        ]);

        $document = Document::factory()->createQuietly([
            'properties' => [
                'pages' => 20,
            ],
            'description' => null,
        ]);

        $this->artisan('document:summary', [
            'documents' => [$document->ulid],
            '--language' => 'de',
        ])
        ->assertSuccessful();

        $updatedDocument = $document->fresh();

        $summary = $updatedDocument->summaries()->first();

        $this->assertInstanceOf(DocumentSummary::class, $summary);
        $this->assertEquals('Summary.', $summary->text);
        $this->assertEquals(LanguageAlpha2::German, $summary->language);
        $this->assertTrue($summary->ai_generated);
        $this->assertNull($updatedDocument->description);

        $pdfDriver->assertCount(1);

        $copilot->assertSummaryFor(new CopilotSummarizeRequest($document->ulid, 'Content of the document', LanguageAlpha2::German));
    }
    
    public function test_abstract_not_overwritten_if_present(): void
    {
        $copilot = Copilot::fake()
            ->withSummary(new CopilotResponse("Summary."));

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("Content of the document")
        ]);

        $document = Document::factory()
            ->has(DocumentSummary::factory()->state([
                'text' => 'Existing summary',
            ]), 'summaries')
            ->createQuietly([
                'properties' => [
                    'pages' => 20,
                ],
            ]);

        $this->artisan('document:summary', [
            'documents' => [$document->ulid],
        ])
        ->assertSuccessful();

        $updatedDocument = $document->fresh();

        $existingSummary = $updatedDocument->summaries()->where('ai_generated', false)->first();

        $this->assertInstanceOf(DocumentSummary::class, $existingSummary);
        $this->assertEquals('Existing summary', $existingSummary->text);
        $this->assertFalse($existingSummary->ai_generated);

        $generatedSummary = $updatedDocument->summaries()->where('ai_generated', true)->first();

        $this->assertInstanceOf(DocumentSummary::class, $generatedSummary);
        $this->assertEquals('Summary.', $generatedSummary->text);
        $this->assertTrue($generatedSummary->ai_generated);
        $this->assertTrue($generatedSummary->all_document);

        $pdfDriver->assertCount(1);
    }
    
    public function test_multiple_ai_generate_abstracts_cohexists(): void
    {
        $copilot = Copilot::fake()
            ->withSummary(new CopilotResponse("Summary."));

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("Content of the document")
        ]);

        $document = Document::factory()
            ->has(DocumentSummary::factory()->state([
                'text' => 'Existing summary',
                'ai_generated' => true,
            ]), 'summaries')
            ->createQuietly([
                'properties' => [
                    'pages' => 20,
                ],
            ]);

        $this->artisan('document:summary', [
            'documents' => [$document->ulid],
        ])
        ->assertSuccessful();

        $updatedDocument = $document->fresh();

        $this->assertEquals(2, $updatedDocument->summaries()->where('ai_generated', true)->count());

        $pdfDriver->assertCount(1);
    }
}
