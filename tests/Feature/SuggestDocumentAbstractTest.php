<?php

namespace Tests\Feature;

use App\Actions\SuggestDocumentAbstract;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class SuggestDocumentAbstractTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_range_with_last_page_below_start_page(): void
    {
        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Storage::fake('local');

        $document = Document::factory()->create([
            'properties' => [
                'pages' => 20,
            ]
        ]);

        Http::preventStrayRequests();

        $action = new SuggestDocumentAbstract();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End page must be greater or equal to start page [10]. Given [5].');

        $abstract = $action($document, 'en', [10, 5]);
    }
    
    public function test_cannot_summarize_more_than_six_pages(): void
    {
        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Storage::fake('local');

        $document = Document::factory()->create([
            'properties' => [
                'pages' => 20,
            ]
        ]);

        Http::preventStrayRequests();

        $action = new SuggestDocumentAbstract();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The pages to summarize exceed the maximum supported of 6 pages');

        $abstract = $action($document, 'en', [1, 8]);
    }
    
    public function test_total_pages_metadata_required(): void
    {
        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Storage::fake('local');

        $document = Document::factory()->create();

        Http::preventStrayRequests();

        $action = new SuggestDocumentAbstract();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not determine the number of pages in the document');

        $abstract = $action($document, 'en', [1, 5]);
    }
    
    public function test_ending_range_must_be_within_document_pages(): void
    {
        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Storage::fake('local');

        $document = Document::factory()->create([
            'properties' => [
                'pages' => 20,
            ]
        ]);

        Http::preventStrayRequests();

        $action = new SuggestDocumentAbstract();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The ending page [21] is outside of the document [1, 20]');

        $abstract = $action($document, 'en', [18, 21]);
    }

    public function test_abstract_suggested(): void
    {
        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Storage::fake('local');

        Storage::disk('local')->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->create([
            'properties' => [
                'pages' => 10,
            ]
        ]);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:9000/extract-text' => Http::response([
                "content" => [
                    [
                        "metadata" => [
                            "page_number" => 1
                        ],
                        "text" => "Content of the document"
                    ],
                ],
                "status" => "ok"
            ], 200),
            'http://localhost:5000/summarize' => Http::response([
                "doc_id" => $document->ulid,
                "summary" => "Summary."
            ], 200),
        ]);

        $action = new SuggestDocumentAbstract();

        $abstract = $action($document);

        $this->assertNotNull($abstract);
        $this->assertEquals("Summary.", $abstract);

        Http::assertSent(function (Request $request) use ($document) {
            return $request->url() == 'http://localhost:5000/summarize' &&
                   $request->method() === 'POST' &&
                   $request['doc_id'] == $document->ulid &&
                   $request['text'] == 'Content of the document' &&
                   $request['lang'] == 'en';
        });
    }
    
    public function test_abstract_for_report_suggested_in_english(): void
    {
        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Storage::fake('local');

        Storage::disk('local')->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->create([
            'title' => 'test_Evaluierungsbericht_.pdf',
            'properties' => [
                'pages' => 10,
            ]
        ]);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:9000/extract-text' => Http::response([
                "content" => [
                    [
                        "metadata" => [
                            "page_number" => 1
                        ],
                        "text" => "-"
                    ],
                    [
                        "metadata" => [
                            "page_number" => 2
                        ],
                        "text" => "-"
                    ],
                    [
                        "metadata" => [
                            "page_number" => 3
                        ],
                        "text" => "-"
                    ],
                    [
                        "metadata" => [
                            "page_number" => 4
                        ],
                        "text" => "SUMMARY Content of the document"
                    ],
                    [
                        "metadata" => [
                            "page_number" => 5
                        ],
                        "text" => "ZUSAMMENFASSUNG"
                    ],
                    [
                        "metadata" => [
                            "page_number" => 6
                        ],
                        "text" => "-"
                    ],
                ],
                "status" => "ok"
            ], 200),
            'http://localhost:5000/summarize' => Http::response([
                "doc_id" => $document->ulid,
                "summary" => "Summary."
            ], 200),
        ]);

        $action = new SuggestDocumentAbstract();

        $abstract = $action($document);

        $this->assertNotNull($abstract);
        $this->assertEquals("Summary.", $abstract);

        Http::assertSent(function (Request $request) use ($document) {
            return $request->url() == 'http://localhost:5000/summarize' &&
                   $request->method() === 'POST' &&
                   $request['doc_id'] == $document->ulid &&
                   $request['text'] == 'SUMMARY Content of the document' &&
                   $request['lang'] == 'en';
        });
    }

    public function test_abstract_for_report_suggested_in_german(): void
    {
        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Storage::fake('local');

        Storage::disk('local')->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->create([
            'title' => 'test_Evaluierungsbericht_.pdf',
            'properties' => [
                'pages' => 10,
            ]
        ]);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:9000/extract-text' => Http::response([
                "content" => [
                    [
                        "metadata" => [
                            "page_number" => 1
                        ],
                        "text" => "-"
                    ],
                    [
                        "metadata" => [
                            "page_number" => 2
                        ],
                        "text" => "-"
                    ],
                    [
                        "metadata" => [
                            "page_number" => 3
                        ],
                        "text" => "-"
                    ],
                    [
                        "metadata" => [
                            "page_number" => 4
                        ],
                        "text" => "ZUSAMMENFASSUNG Content of the document"
                    ],
                    [
                        "metadata" => [
                            "page_number" => 5
                        ],
                        "text" => "SUMMARY"
                    ],
                    [
                        "metadata" => [
                            "page_number" => 6
                        ],
                        "text" => "-"
                    ],
                ],
                "status" => "ok"
            ], 200),
            'http://localhost:5000/summarize' => Http::response([
                "doc_id" => $document->ulid,
                "summary" => "Summary."
            ], 200),
        ]);

        $action = new SuggestDocumentAbstract();

        $abstract = $action($document, 'de');

        $this->assertNotNull($abstract);
        $this->assertEquals("Summary.", $abstract);

        Http::assertSent(function (Request $request) use ($document) {
            return $request->url() == 'http://localhost:5000/summarize' &&
                   $request->method() === 'POST' &&
                   $request['doc_id'] == $document->ulid &&
                   $request['text'] == 'ZUSAMMENFASSUNG Content of the document' &&
                   $request['lang'] == 'de';
        });
    }
}
