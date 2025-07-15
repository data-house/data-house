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
use App\PdfProcessing\Facades\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use OneOffTech\LibrarianClient\Requests\Document\CreateDocumentRequest;
use OneOffTech\LibrarianClient\Requests\Document\DeleteDocumentRequest;
use OneOffTech\LibrarianClient\Requests\Document\QuestionDocumentRequest;
use OneOffTech\LibrarianClient\Requests\Library\CreateLibraryRequest;
use OneOffTech\LibrarianClient\Requests\Library\GetLibraryRequest;
use OneOffTech\LibrarianClient\Requests\Library\UpdateLibraryRequest;
use OneOffTech\LibrarianClient\Requests\Prompt\UpdatePromptsRequest;
use OneOffTech\LibrarianClient\Requests\Question\AggregateQuestionsRequest;
use OneOffTech\LibrarianClient\Requests\Question\TransformQuestionRequest;
use OneOffTech\LibrarianClient\Requests\Summary\GenerateSummaryRequest;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request as SaloonRequest;
use Tests\TestCase;

class CloudEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_configuration_created(): void
    {
        config([
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'key' => Str::random(),
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

        $mockClient = MockClient::global([
            GetLibraryRequest::class => MockResponse::make('{"message": "not found"}', 404),
            CreateLibraryRequest::class => MockResponse::make('{"message": "ok"}'),
        ]);
        
        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');

        $engine->syncLibrarySettings();

        $mockClient->assertSent(GetLibraryRequest::class);
        $mockClient->assertSent(CreateLibraryRequest::class);

        $mockClient->assertSent(function (SaloonRequest $request) {
            $body = $request->body()->all();

            return $request->resolveEndpoint() === '/libraries/' &&
                $body['id'] == 'library-id' &&
                $body['config']->database['index_fields'][0] == 'resource_id' &&
                $body['config']->text['n_context_chunk'] == 1;
        });
    }
    
    public function test_library_configuration_updated(): void
    {
        config([
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'key' => Str::random(),
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

        $mockClient = MockClient::global([
            GetLibraryRequest::class => MockResponse::make([
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
            UpdateLibraryRequest::class => MockResponse::make('{"message": "ok"}'),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');

        $engine->syncLibrarySettings();

        $mockClient->assertSent(GetLibraryRequest::class);
        $mockClient->assertSent(UpdateLibraryRequest::class);

        $mockClient->assertSent(function (SaloonRequest $request) {
            $body = $request->body()->all();

            return $request->resolveEndpoint() === '/libraries/library-id' &&
                $body['database']['index_fields'][0] == 'resource_id' &&
                $body['text']['n_context_chunk'] == 1;
        });
    }
    
    public function test_document_can_be_added(): void
    {
        config([
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'key' => Str::random(),
                'library' => 'library-id'
            ],
        ]);

        Queue::fake();

        Storage::fake(Disk::DOCUMENTS->value);

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->createQuietly([
            'disk_path' => 'test.pdf',
        ]);

        $mockClient = MockClient::global([
            CreateDocumentRequest::class => MockResponse::make(['message' => 'ok'], 201),
        ]);

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("This is the header 1 This is a test PDF to be used as input in unit tests This is a heading 1 This is a paragraph below heading 1")
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');

        $engine->update(Document::all());

        $pdfDriver->assertCount(1);

        $mockClient->assertSent(CreateDocumentRequest::class);

        $mockClient->assertSent(function (SaloonRequest $request) use ($document) {
            $body = $request->body()->all();

            return $request->resolveEndpoint() === '/library/library-id/documents' &&
                $body['id'] === $document->getCopilotKey() &&
                $body['lang'] === 'en';
        });

    }
    
    public function test_document_can_be_removed_from_copilot(): void
    {
        config([
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'key' => Str::random(),
                'library' => 'library-id'
            ],
        ]);

        Queue::fake();

        $document = Document::factory()->create([
            'disk_path' => 'test.pdf',
        ]);

        $mockClient = MockClient::global([
            DeleteDocumentRequest::class => MockResponse::make(['message' => 'ok']),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');

        $engine->delete(Document::all());

        $mockClient->assertSent(function (SaloonRequest $request) use ($document) {
            return $request->resolveEndpoint() === '/library/library-id/documents/' . $document->getCopilotKey();
        });

    }

    public function test_engine_executes_single_question(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'key' => Str::random(),
                'library' => 'library-id'
            ],
        ]);

        Queue::fake();

        $id = Str::uuid();

        $mockClient = MockClient::global([
            QuestionDocumentRequest::class => MockResponse::make([
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
            ]),
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

        $mockClient->assertSent(QuestionDocumentRequest::class);

        $mockClient->assertSent(function (SaloonRequest $request) use ($id) {
            $body = $request->body()->all();

            return $request->resolveEndpoint() === '/library/library-id/documents/1/questions' && 
                $body['id'] == $id &&
                $body['text'] == 'Question text' &&
                $body['lang'] == 'en';
        });

    }

    public function test_engine_transform_multiple_question(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'key' => Str::random(),
                'library' => 'library-id'
            ],
        ]);

        Queue::fake();

        $id = Str::uuid();

        $mockClient = MockClient::global([
            TransformQuestionRequest::class => MockResponse::make([
                "id" => $id,
                "lang" => "en",
                "text" => "Transformed Question",
            ]),
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

        $mockClient->assertSent(TransformQuestionRequest::class);

        $mockClient->assertSent(function (SaloonRequest $request) use ($id) {
            $body = $request->body()->all();

            return $request->resolveEndpoint() === '/library/library-id/questions/transform' &&
                $body['question']['id'] === (string) $id &&
                $body['question']['lang'] === 'en' &&
                $body['question']['text'] === 'Question text' &&
                $body['transformation']['id'] === '0' &&
                $body['transformation']['args'][0] === 'Question text';
        });
    }

    public function test_engine_aggregates_multiple_answers_to_a_question(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'key' => Str::random(),
                'library' => 'library-id'
            ],
        ]);

        Queue::fake();

        $id = Str::uuid();

        $mockClient = MockClient::global([
            AggregateQuestionsRequest::class => MockResponse::make([
                "id" => $id,
                "lang" => 'en',
                "text" => "Aggregated answer.",
                "refs" => [
                    [
                        "id" => 1,
                        "page_number" => 2,
                    ]
                ],
            ]),
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

        $mockClient->assertSent(AggregateQuestionsRequest::class);

        $mockClient->assertSent(function (SaloonRequest $request) use ($id) {
            $body = $request->body()->all();

            return $request->resolveEndpoint() === '/library/library-id/questions/aggregate' &&
                filled($body['question'] ?? []) &&
                filled($body['transformation'] ?? []) &&
                filled($body['answers'] ?? []) &&
                $body['transformation']['args'][0] == 'Question text' &&
                $body['transformation']['id'] == '0' &&
                empty($body['transformation']['append']) &&
                $body['question']['id'] == (string) $id &&
                $body['question']['lang'] == 'en' &&
                $body['question']['text'] == 'Question text';
        });
    }

    public function test_multiple_question_aggregation_with_append(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'key' => Str::random(),
                'library' => 'library-id'
            ],
        ]);

        Queue::fake();

        $id = Str::uuid();

        $mockClient = MockClient::global([
            AggregateQuestionsRequest::class => MockResponse::make([
                "id" => $id,
                "lang" => 'en',
                "text" => "Aggregated answer.",
                "refs" => [
                    [
                        "id" => 1,
                        "page_number" => 2,
                    ]
                ],
            ]),
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

        $mockClient->assertSent(AggregateQuestionsRequest::class);

        $mockClient->assertSent(function (SaloonRequest $request) use ($id) {
            $body = $request->body()->all();

            return $request->resolveEndpoint() === '/library/library-id/questions/aggregate' &&
                filled($body['question'] ?? []) &&
                filled($body['transformation'] ?? []) &&
                filled($body['answers'] ?? []) &&
                $body['transformation']['args'][0] == 'Question text' &&
                $body['transformation']['id'] == '0' &&
                is_array($body['transformation']['append']) &&
                $body['transformation']['append'][0]['id'] === 'q1' &&
                $body['transformation']['append'][0]['text'] === 'Append' &&
                $body['question']['id'] == (string) $id &&
                $body['question']['lang'] == 'en' &&
                $body['question']['text'] == 'Question text';
        });
    }

    public function test_english_summary(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'key' => Str::random(),
                'library' => 'library-id',
            ],
        ]);

        Queue::fake();

        $id = Str::uuid();

        $mockClient = MockClient::global([
            GenerateSummaryRequest::class => MockResponse::make([
                "id" => $id,
                "lang" => "en",
                "text" => "Summary."
            ]),
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

        $mockClient->assertSent(GenerateSummaryRequest::class);

        $mockClient->assertSent(function (SaloonRequest $request) use ($id) {
            $body = $request->body()->all();

            return $request->resolveEndpoint() === '/library/library-id/summary' &&
                $body['text']['id'] == $id &&
                $body['text']['text'] == 'The text to summarize' &&
                $body['text']['lang'] == 'en';
        });
    }

    public function test_cannot_summarize_text_in_french(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'key' => Str::random(),
            ],
        ]);

        Queue::fake();

        $mockClient = MockClient::global([
            GenerateSummaryRequest::class => MockResponse::make([], 404),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');
        
        $request = new CopilotSummarizeRequest(Str::uuid(), 'The text to summarize', LanguageAlpha2::French);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('French language not supported. Automated summaries are supported only for text in English, German, Spanish_Castilian.');

        $response = $engine->summarize($request);

        $mockClient->assertNotSent(GenerateSummaryRequest::class);
    }

    public function test_refresh_prompts(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'key' => Str::random(),
            ],
        ]);

        $mockClient = MockClient::global([
            UpdatePromptsRequest::class => MockResponse::make(['message' => 'ok']),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('cloud');

        $engine->refreshPrompts();

        $mockClient->assertSent(UpdatePromptsRequest::class);

    }


}
