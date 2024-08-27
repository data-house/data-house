<?php

namespace Tests\Feature\Copilot\Engines;

use App\Copilot\AnswerAggregationCopilotRequest;
use App\Copilot\CopilotManager;
use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
use App\Copilot\CopilotSummarizeRequest;
use App\Models\Disk;
use App\Models\Document;
use App\PdfProcessing\DocumentContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Tests\TestCase;

class CloudEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_configuration_created(): void
    {
        config([
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
                'library-settings' => [
                    'indexed-fields' => [
                        'resource_id',
                    ],
                    'text-processing' => [
                        'n_context_chunk' => 1,
                        'chunk_length' => 100,
                        'chunk_overlap' => 2,
                    ]
                ],
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5000/libraries/library-id' => Http::response([
                "message" => "Library not found."
            ], 404),
            'http://localhost:5000/libraries' => Http::response([
                "message" => "ok"
            ], 201),
        ]);
        
        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');

        $engine->syncLibrarySettings();

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://localhost:5000/libraries' &&
                   $request['id'] == 'library-id' &&
                   $request['config']['database']['index_fields'][0] == 'resource_id' &&
                   $request['config']['text']['n_context_chunk'] == 1;
        });
    }
    
    public function test_library_configuration_updated(): void
    {
        config([
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
                'library-settings' => [
                    'indexed-fields' => [
                        'resource_id',
                    ],
                    'text-processing' => [
                        'n_context_chunk' => 1,
                        'chunk_length' => 100,
                        'chunk_overlap' => 2,
                    ]
                ],
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5000/libraries/library-id' => Http::response([
                    "id" => "library-id",
                    "name" => "Test",
                    "config" => [
                      "database" => [
                        "index_fields" => [
                          "resource_id"
                        ]
                      ],
                      "text" => [
                        "n_context_chunk" => 10,
                        "chunk_length" => 490,
                        "chunk_overlap" => 10
                      ],
                      "enable_guardian" => false
                    ]
            ], 200),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');

        $engine->syncLibrarySettings();

        Http::assertSent(function (Request $request) {
            if($request->method() == 'GET'){
                return true;
            }

            return $request->method() == 'PUT' && $request->url() == 'http://localhost:5000/libraries/library-id' &&
                   $request['database']['index_fields'][0] == 'resource_id' &&
                   $request['text']['n_context_chunk'] == 1;
        });
    }
    
    public function test_document_can_be_added(): void
    {

        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Queue::fake();

        Storage::fake(Disk::DOCUMENTS->value);

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->create([
            'disk_path' => 'test.pdf',
        ]);

        Http::preventStrayRequests();

        $textContent = new DocumentContent("This is the header 1 This is a test PDF to be used as input in unit tests This is a heading 1 This is a paragraph below heading 1");

        Http::fake([
            'http://localhost:9000/extract-text' => Http::response($textContent->asStructured(), 200),
            'http://localhost:5000/library/library-id/documents' => Http::response([
                "message" => "Document {$document->getCopilotKey()} added to the library library-id."
            ], 201),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');

        $engine->update(Document::all());

        Http::assertSent(function (Request $request) use ($document, $textContent) {
            return $request->url() == 'http://localhost:5000/library/library-id/documents' &&
                   $request['id'] == $document->getCopilotKey() &&
                   $request['lang'] == 'en';
        });

    }
    
    public function test_document_can_be_removed_from_copilot(): void
    {
        config([
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Queue::fake();

        $document = Document::factory()->create([
            'disk_path' => 'test.pdf',
        ]);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5000/library/*' => Http::response([
                "message" => "Document `{$document->getCopilotKey()}` removed from the library `library-id`."
            ], 200),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');

        $engine->delete(Document::all());

        Http::assertSent(function (Request $request) use ($document) {
            return $request->url() == 'http://localhost:5000/library/library-id/documents/' . $document->getCopilotKey() &&
                   $request->method() === 'DELETE';
        });

    }

    public function test_single_question(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();
        
        $id = Str::uuid();
        
        Http::fake([
            'http://localhost:5000/library/library-id/documents/1/questions' => Http::response([
                "id" => $id,
                "lang" => "en",
                "text" => "Yes, I can provide information and answer questions related to renewable energy and sustainable development based on the context information provided.",
                "refs" => [
                    [
                        "id" => "1",
                        "page_number" => 2,
                        "score" => 0.47,
                        "bounding_box" => null,
                    ],
                    [
                        "id" => "1",
                        "page_number" => 4,
                    ]
                ],
            ], 200),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');

        $expectedQuestionHash = hash('sha512', 'Question text-1');
        
        $request = new CopilotRequest($id, 'Question text', ["1"], 'en');

        $response = $engine->question($request);

        $this->assertFalse($request->multipleQuestionRequest());

        $this->assertEquals($expectedQuestionHash, $request->hash());

        $this->assertInstanceOf(CopilotResponse::class, $response);

        $this->assertEquals([
            "text" => "Yes, I can provide information and answer questions related to renewable energy and sustainable development based on the context information provided.",
            "references" => [
                [
                    "id" => "1",
                    "page_number" => 2,
                    "score" => 0.47,
                    "bounding_box" => null,
                ],
                [
                    "id" => "1",
                    "page_number" => 4,
                ]
            ],
        ], $response->jsonSerialize());

        Http::assertSent(function (Request $request) use ($id) {
            return $request->url() == 'http://localhost:5000/library/library-id/documents/1/questions' &&
                   $request->method() === 'POST' &&
                   $request['id'] == $id &&
                   $request['text'] == 'Question text' &&
                   $request['lang'] == 'en';
        });

    }

    public function test_multiple_question_decomposition(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        $id = Str::uuid();

        Http::fake([
            'http://localhost:5000/library/library-id/questions/transform' => Http::response([
                "id" => $id,
                "lang" => "en",
                "text" => "Transformed Question",
            ], 200),
        ]);
        

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');

        $expectedQuestionHash = hash('sha512', 'Question text-4-12');
        
        $request = new CopilotRequest($id, 'Question text', ["4","12"], 'en');

        $response = $engine->question($request);

        $this->assertTrue($request->multipleQuestionRequest());

        $this->assertEquals($expectedQuestionHash, $request->hash());

        $this->assertInstanceOf(CopilotResponse::class, $response);

        $this->assertEquals([
            "text" => "Transformed Question",
            "references" => [],
        ], $response->jsonSerialize());

        Http::assertSent(function (Request $request) use ($id) {
            return $request->url() == 'http://localhost:5000/library/library-id/questions/transform' &&
                   $request->method() === 'POST' &&
                   filled($request['question'] ?? []) &&
                   filled($request['transformation'] ?? []) &&
                   $request['transformation']['args'][0] == 'Question text' &&
                   $request['transformation']['id'] == '0' &&
                   $request['question']['id'] == $id &&
                   $request['question']['lang'] == 'en' &&
                   $request['question']['text'] == 'Question text'
                   ;
        });
    }

    public function test_multiple_question_aggregation(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        $id = Str::uuid();

        Http::fake([
            'http://localhost:5000/library/library-id/questions/aggregate' => Http::response([
                "id" => $id,
                "lang" => 'en',
                "text" => "Aggregated answer.",
                "refs" => [
                    [
                        "id" => 1,
                        "page_number" => 2,
                    ]
                ],
            ], 200),
        ]);
        

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');
        
        $request = new AnswerAggregationCopilotRequest($id, 'Question text', [
            [
                "text" => "First answer.",
                "id" => "q1",
                "lang" => "en",
                "refs" => [
                    [
                        "id" => 1,
                        "page_number" => 2,
                    ],
                    [
                        "id" => 1,
                        "page_number" => 4,
                    ]
                ]
            ],
            [
                "text" => "Second answer.",
                "id" => "q2",
                "lang" => "en",
                "refs" => [
                    [
                        "id" => 1,
                        "page_number" => 2,
                    ],
                    [
                        "id" => 1,
                        "page_number" => 4,
                    ]
                ]
            ],
        ], 'en');

        $response = $engine->aggregate($request);

        $this->assertInstanceOf(CopilotResponse::class, $response);

        $this->assertEquals([
            "text" => "Aggregated answer.",
            "references" => [
                [
                    "id" => 1,
                    "page_number" => 2,
                ]
            ],
        ], $response->jsonSerialize());

        Http::assertSent(function (Request $request) use ($id) {
            return $request->url() == 'http://localhost:5000/library/library-id/questions/aggregate' &&
                   $request->method() === 'POST' &&
                   filled($request['question'] ?? []) &&
                   filled($request['transformation'] ?? []) &&
                   filled($request['answers'] ?? []) &&
                   $request['transformation']['args'][0] == 'Question text' &&
                   $request['transformation']['id'] == '0' &&
                   is_null($request['transformation']['append']) &&
                   $request['question']['id'] == $id &&
                   $request['question']['lang'] == 'en' &&
                   $request['question']['text'] == 'Question text'
                   ;
        });
    }

    public function test_multiple_question_aggregation_with_append(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        $id = Str::uuid();

        Http::fake([
            'http://localhost:5000/library/library-id/questions/aggregate' => Http::response([
                "id" => $id,
                "lang" => 'en',
                "text" => "Aggregated answer.",
                "refs" => [
                    [
                        "id" => 1,
                        "page_number" => 2,
                    ]
                ],
            ], 200),
        ]);
        

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');
        
        $request = new AnswerAggregationCopilotRequest($id, 'Question text', [
            [
                "text" => "First answer.",
                "id" => "q1",
                "lang" => "en",
                "refs" => [
                    [
                        "id" => 1,
                        "page_number" => 2,
                    ],
                    [
                        "id" => 1,
                        "page_number" => 4,
                    ]
                ]
            ],
            [
                "text" => "Second answer.",
                "id" => "q2",
                "lang" => "en",
                "refs" => [
                    [
                        "id" => 1,
                        "page_number" => 2,
                    ],
                    [
                        "id" => 1,
                        "page_number" => 4,
                    ]
                ]
            ],
        ], 'en', '0', [['id' => 'q1', 'text' => 'Append']]);

        $response = $engine->aggregate($request);

        $this->assertInstanceOf(CopilotResponse::class, $response);

        $this->assertEquals([
            "text" => "Aggregated answer.",
            "references" => [
                [
                    "id" => 1,
                    "page_number" => 2,
                ]
            ],
        ], $response->jsonSerialize());

        Http::assertSent(function (Request $request) use ($id) {
            return $request->url() == 'http://localhost:5000/library/library-id/questions/aggregate' &&
                   $request->method() === 'POST' &&
                   filled($request['question'] ?? []) &&
                   filled($request['transformation'] ?? []) &&
                   filled($request['answers'] ?? []) &&
                   $request['transformation']['args'][0] == 'Question text' &&
                   $request['transformation']['id'] == '0' &&
                   is_array($request['transformation']['append']) &&
                   $request['transformation']['append'][0]['id'] === 'q1' &&
                   $request['transformation']['append'][0]['text'] === 'Append' &&
                   $request['question']['id'] == $id &&
                   $request['question']['lang'] == 'en' &&
                   $request['question']['text'] == 'Question text'
                   ;
        });
    }

    public function test_english_summary(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        $id = Str::uuid();

        Http::fake([
            'http://localhost:5000/library/library-id/summary' => Http::response([
                "id" => $id,
                "lang" => "en",
                "text" => "Summary."
            ], 200),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');
        
        $request = new CopilotSummarizeRequest($id, 'The text to summarize', LanguageAlpha2::English);

        $response = $engine->summarize($request);

        $this->assertInstanceOf(CopilotResponse::class, $response);

        $this->assertEquals([
            "text" => "Summary.",
            "references" => [],
        ], $response->jsonSerialize());

        Http::assertSent(function (Request $request) use ($id) {
            return $request->url() == 'http://localhost:5000/library/library-id/summary' &&
                   $request->method() === 'POST' &&
                   $request['id'] == $id &&
                   $request['text'] == 'The text to summarize' &&
                   $request['lang'] == 'en';
        });
    }

    public function test_cannot_summarize_text_in_french(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        $id = Str::uuid();

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');
        
        $request = new CopilotSummarizeRequest($id, 'The text to summarize', LanguageAlpha2::French);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('French language not supported. Automated summaries are supported only for text in English, German, Spanish_Castilian.');

        $response = $engine->summarize($request);
    }


}
