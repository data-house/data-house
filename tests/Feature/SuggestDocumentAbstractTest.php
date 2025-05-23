<?php

namespace Tests\Feature;

use App\Actions\SuggestDocumentAbstract;
use App\Copilot\CopilotResponse;
use App\Copilot\CopilotSummarizeRequest;
use App\Copilot\Facades\Copilot;
use App\Models\Document;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\Facades\Pdf;
use App\PdfProcessing\Support\Testing\Fakes\FakeDocumentContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Tests\TestCase;

class SuggestDocumentAbstractTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_range_with_last_page_below_start_page(): void
    {
        $copilot = Copilot::fake();

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("Content of the document")
        ]);

        $document = Document::factory()->createQuietly([
            'properties' => [
                'pages' => 20,
            ]
        ]);

        $action = new SuggestDocumentAbstract();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End page must be greater or equal to start page [10]. Given [5].');

        $abstract = $action($document, LanguageAlpha2::English, [10, 5]);

        $pdfDriver->assertNoParsingRequests();

        $copilot->assertNoInteractions();
    }
    
    public function test_total_pages_metadata_required(): void
    {
        $copilot = Copilot::fake();

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("Content of the document")
        ]);

        $document = Document::factory()->createQuietly();

        $action = new SuggestDocumentAbstract();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not determine the number of pages in the document');

        $abstract = $action($document, LanguageAlpha2::English, [1, 5]);

        $pdfDriver->assertNoParsingRequests();

        $copilot->assertNoInteractions();
    }
    
    public function test_ending_range_must_be_within_document_pages(): void
    {
        $copilot = Copilot::fake();

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("Content of the document")
        ]);

        $document = Document::factory()->createQuietly([
            'properties' => [
                'pages' => 20,
            ]
        ]);

        $action = new SuggestDocumentAbstract();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The ending page [21] is outside of the document [1, 20]');

        $abstract = $action($document, LanguageAlpha2::English, [18, 21]);

        $copilot->assertNoInteractions();
    }

    public function test_abstract_suggested(): void
    {
        $copilot = Copilot::fake()
            ->withSummary(new CopilotResponse("Summary."));

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("Content of the document")
        ]);

        Storage::disk('local')->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->createQuietly([
            'properties' => [
                'pages' => 10,
            ]
        ]);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5000/library/library-id/summary' => Http::response([
                "id" => $document->getCopilotKey(),
                "lang" => "en",
                "text" => "Summary."
            ], 200),
        ]);

        $action = new SuggestDocumentAbstract();

        $abstract = $action($document, LanguageAlpha2::English, [1, 10]);

        $this->assertNotNull($abstract);
        $this->assertEquals("Summary.", $abstract);

        $pdfDriver->assertCount(1);

        $copilot->assertSummaryFor(new CopilotSummarizeRequest($document->getCopilotKey(), 'Content of the document', LanguageAlpha2::English));
    }
    
    public function test_abstract_for_report_suggested_in_english(): void
    {
        $copilot = Copilot::fake()
            ->withSummary(new CopilotResponse("Summary."));

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            FakeDocumentContent::fromPages([
                1 => "-",
                2 => "-",
                3 => "-",
                4 => "SUMMARY Content of the document",
                5 => "ZUSAMMENFASSUNG (and other content)",
                6 => "-",
            ])
        ]);

        Storage::disk('local')->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->createQuietly([
            'title' => 'test_Evaluierungsbericht_.pdf',
            'properties' => [
                'pages' => 10,
            ]
        ]);

        $action = new SuggestDocumentAbstract();

        $abstract = $action($document);

        $this->assertNotNull($abstract);
        $this->assertEquals("Summary.", $abstract);

        $pdfDriver->assertCount(1);

        $copilot->assertSummaryFor(new CopilotSummarizeRequest($document->getCopilotKey(), '-' . PHP_EOL . '-' . PHP_EOL . '-' . PHP_EOL . 'SUMMARY Content of the document' . PHP_EOL . 'ZUSAMMENFASSUNG (and other content)' . PHP_EOL . '-', LanguageAlpha2::English));
    }

    public function test_abstract_for_report_suggested_in_german(): void
    {
        $copilot = Copilot::fake()
            ->withSummary(new CopilotResponse("Summary."));

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            FakeDocumentContent::fromPages([
                1 => "-",
                2 => "-",
                3 => "-",
                4 => "ZUSAMMENFASSUNG Content of the document",
                5 => "SUMMARY",
                6 => "-",
            ])
        ]);

        Storage::disk('local')->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->createQuietly([
            'title' => 'test_Evaluierungsbericht_.pdf',
            'properties' => [
                'pages' => 10,
            ]
        ]);

        $action = new SuggestDocumentAbstract();

        $abstract = $action($document, LanguageAlpha2::German);

        $this->assertNotNull($abstract);
        $this->assertEquals("Summary.", $abstract);

        $pdfDriver->assertCount(1);

        $copilot->assertSummaryFor(new CopilotSummarizeRequest($document->getCopilotKey(), '-' . PHP_EOL . '-' . PHP_EOL . '-' . PHP_EOL . 'ZUSAMMENFASSUNG Content of the document' . PHP_EOL . 'SUMMARY' . PHP_EOL . '-', LanguageAlpha2::German));
    }

    
    public function test_page_range_respected(): void
    {
        $copilot = Copilot::fake()
            ->withSummary(new CopilotResponse("Summary."));

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            FakeDocumentContent::fromPages([
                1 => "-",
                2 => "-",
                3 => "-",
                4 => "SUMMARY Content of the document",
                5 => "ZUSAMMENFASSUNG (and other content)",
                6 => "-",
            ])
        ]);

        Storage::disk('local')->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->createQuietly([
            'title' => 'test_Evaluierungsbericht_.pdf',
            'properties' => [
                'pages' => 10,
            ]
        ]);

        $action = new SuggestDocumentAbstract();

        $abstract = $action($document, LanguageAlpha2::English, [4,4]);

        $this->assertNotNull($abstract);
        $this->assertEquals("Summary.", $abstract);

        $pdfDriver->assertCount(1);

        $copilot->assertSummaryFor(new CopilotSummarizeRequest($document->getCopilotKey(), 'SUMMARY Content of the document', LanguageAlpha2::English));
    }
}
