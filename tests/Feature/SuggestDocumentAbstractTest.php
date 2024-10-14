<?php

namespace Tests\Feature;

use App\Actions\SuggestDocumentAbstract;
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
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("Content of the document")
        ]);

        $document = Document::factory()->createQuietly([
            'properties' => [
                'pages' => 20,
            ]
        ]);

        Http::preventStrayRequests();

        $action = new SuggestDocumentAbstract();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End page must be greater or equal to start page [10]. Given [5].');

        $abstract = $action($document, LanguageAlpha2::English, [10, 5]);

        $pdfDriver->assertNoParsingRequests();
    }
    
    public function test_total_pages_metadata_required(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("Content of the document")
        ]);

        $document = Document::factory()->createQuietly();

        Http::preventStrayRequests();

        $action = new SuggestDocumentAbstract();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not determine the number of pages in the document');

        $abstract = $action($document, LanguageAlpha2::English, [1, 5]);

        $pdfDriver->assertNoParsingRequests();
    }
    
    public function test_ending_range_must_be_within_document_pages(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("Content of the document")
        ]);

        $document = Document::factory()->createQuietly([
            'properties' => [
                'pages' => 20,
            ]
        ]);

        Http::preventStrayRequests();

        $action = new SuggestDocumentAbstract();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The ending page [21] is outside of the document [1, 20]');

        $abstract = $action($document, LanguageAlpha2::English, [18, 21]);
    }

    public function test_abstract_suggested(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

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

        Http::assertSent(function (Request $request) use ($document) {
            return $request->url() == 'http://localhost:5000/library/library-id/summary' &&
                   $request->method() === 'POST' &&
                   $request['id'] == $document->getCopilotKey() &&
                   $request['text'] == 'Content of the document' &&
                   $request['lang'] == 'en';
        });

        $pdfDriver->assertCount(1);
    }
    
    public function test_abstract_for_report_suggested_in_english(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

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

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5000/library/library-id/summary' => Http::response([
                "id" => $document->getCopilotKey(),
                "lang" => "en",
                "text" => "Summary."
            ], 200),
        ]);

        $action = new SuggestDocumentAbstract();

        $abstract = $action($document);

        $this->assertNotNull($abstract);
        $this->assertEquals("Summary.", $abstract);

        Http::assertSent(function (Request $request) use ($document) {
            return $request->url() == 'http://localhost:5000/library/library-id/summary' &&
                   $request->method() === 'POST' &&
                   $request['id'] == $document->getCopilotKey() &&
                   $request['text'] == '-' . PHP_EOL . '-' . PHP_EOL . '-' . PHP_EOL . 'SUMMARY Content of the document' . PHP_EOL . 'ZUSAMMENFASSUNG (and other content)' . PHP_EOL . '-' &&
                   $request['lang'] == 'en';
        });

        $pdfDriver->assertCount(1);
    }

    public function test_abstract_for_report_suggested_in_german(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

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

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5000/library/library-id/summary' => Http::response([
                "id" => $document->getCopilotKey(),
                "lang" => "en",
                "text" => "Summary."
            ], 200),
        ]);

        $action = new SuggestDocumentAbstract();

        $abstract = $action($document, LanguageAlpha2::German);

        $this->assertNotNull($abstract);
        $this->assertEquals("Summary.", $abstract);

        Http::assertSent(function (Request $request) use ($document) {
            return $request->url() == 'http://localhost:5000/library/library-id/summary' &&
                   $request->method() === 'POST' &&
                   $request['id'] == $document->getCopilotKey() &&
                   $request['text'] == '-' . PHP_EOL . '-' . PHP_EOL . '-' . PHP_EOL . 'ZUSAMMENFASSUNG Content of the document' . PHP_EOL . 'SUMMARY' . PHP_EOL . '-' &&
                   $request['lang'] == 'de';
        });

        $pdfDriver->assertCount(1);
    }

    
    public function test_page_range_respected(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

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

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5000/library/library-id/summary' => Http::response([
                "id" => $document->getCopilotKey(),
                "lang" => "en",
                "text" => "Summary."
            ], 200),
        ]);

        $action = new SuggestDocumentAbstract();

        $abstract = $action($document, LanguageAlpha2::English, [4,4]);

        $this->assertNotNull($abstract);
        $this->assertEquals("Summary.", $abstract);

        Http::assertSent(function (Request $request) use ($document) {
            return $request->url() == 'http://localhost:5000/library/library-id/summary' &&
                   $request->method() === 'POST' &&
                   $request['id'] == $document->getCopilotKey() &&
                   $request['text'] == 'SUMMARY Content of the document' &&
                   $request['lang'] == 'en';
        });

        $pdfDriver->assertCount(1);
    }
}
