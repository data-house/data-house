<?php

namespace Tests\Feature\Copilot\Engines;

use App\Copilot\AnswerAggregationCopilotRequest;
use App\Copilot\CopilotManager;
use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
use App\Copilot\CopilotSummarizeRequest;
use App\Models\Disk;
use App\Models\Document;
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

    public function test_library_configuration_synced(): void
    {
        config([
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Queue::fake();

        // Http::preventStrayRequests();

        // Http::fake([
        //     'http://localhost:5000/library/library-id/documents' => Http::response([
        //         // "id" => $document->getCopilotKey(),
        //         "message" => "Document {$document->getCopilotKey()} added to the library library-id."
        //     ], 201),
        // ]);

        

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');

        $engine->update(Document::all());

        Http::assertSent(function (Request $request) use ($document, $textContent) {
            return $request->url() == 'http://localhost:5000/library/library-id/documents' &&
                   $request['id'] == $document->getCopilotKey() &&
                   $request['lang'] == 'en' &&
                   $request['data'] == $textContent;
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

        $textContent = [
            [
                "metadata" => [
                    "page_number" => 1
                ],
                "text" => "This is the header 1 This is a test PDF to be used as input in unit tests This is a heading 1 This is a paragraph below heading 1"
            ],
        ];

        Http::fake([
            'http://localhost:9000/extract-text' => Http::response([
                "content" => $textContent,
                "status" => "ok"
            ], 200),
            'http://localhost:5000/library/library-id/documents' => Http::response([
                // "id" => $document->getCopilotKey(),
                "message" => "Document {$document->getCopilotKey()} added to the library library-id."
            ], 201),
        ]);

        

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');

        $engine->update(Document::all());

        Http::assertSent(function (Request $request) use ($document, $textContent) {
            return $request->url() == 'http://localhost:5000/library/library-id/documents' &&
                   $request['id'] == $document->getCopilotKey() &&
                   $request['lang'] == 'en' &&
                   $request['data'] == $textContent;
        });

    }
    
    public function test_document_can_be_removed_from_copilot(): void
    {

        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Queue::fake();

        Storage::fake(Disk::DOCUMENTS->value);

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->create([
            'disk_path' => 'test.pdf',
        ]);

        Http::preventStrayRequests();

        $textContent = [
            [
                "metadata" => [
                    "page_number" => 1
                ],
                "text" => "This is the header 1 This is a test PDF to be used as input in unit tests This is a heading 1 This is a paragraph below heading 1"
            ],
        ];

        Http::fake([
            'http://localhost:9000/extract-text' => Http::response([
                "content" => $textContent,
                "status" => "ok"
            ], 200),
            'http://localhost:5000/documents/*' => Http::response([
                "id" => $document->getCopilotKey(),
                "status" => "ok"
            ], 200),
        ]);

        

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');

        $engine->delete(Document::all());

        Http::assertSent(function (Request $request) use ($document, $textContent) {
            return $request->url() == 'http://localhost:5000/documents/' . $document->getCopilotKey() &&
                   $request->method() === 'DELETE';
        });

    }

    public function test_single_question(): void
    {
        config([
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        $id = Str::uuid();

        Http::fake([
            'http://localhost:5000/question' => Http::response([
                "q_id" => $id,
                "answer" => [
                    [
                        "text" => "Yes, I can provide information and answer questions related to renewable energy and sustainable development based on the context information provided.",
                        "references" => [
                            [
                                "doc_id" => 1,
                                "page_number" => 2,
                            ],
                            [
                                "doc_id" => 1,
                                "page_number" => 4,
                            ]
                        ],
                    ],
                ]
            ], 200),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');

        $expectedQuestionHash = hash('sha512', 'Question text-1');
        
        $request = new CopilotRequest($id, 'Question text', [1], 'en');

        $response = $engine->question($request);

        $this->assertFalse($request->multipleQuestionRequest());

        $this->assertEquals($expectedQuestionHash, $request->hash());

        $this->assertInstanceOf(CopilotResponse::class, $response);

        $this->assertEquals([
            "text" => "Yes, I can provide information and answer questions related to renewable energy and sustainable development based on the context information provided.",
            "references" => [
                [
                    "doc_id" => 1,
                    "page_number" => 2,
                ],
                [
                    "doc_id" => 1,
                    "page_number" => 4,
                ]
            ],
        ], $response->jsonSerialize());

        Http::assertSent(function (Request $request) use ($id) {
            return $request->url() == 'http://localhost:5000/question' &&
                   $request->method() === 'POST' &&
                   $request['q_id'] == $id &&
                   $request['doc_id'][0] == 1 &&
                   $request['q'] == 'Question text' &&
                   $request['lang'] == 'en';
        });

    }

    public function test_multiple_question_decomposition(): void
    {
        config([
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        $id = Str::uuid();

        Http::fake([
            'http://localhost:5000/transform-question' => Http::response([
                4 => "Question text",
                12 => "Question text",
            ], 200),
        ]);
        

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');

        $expectedQuestionHash = hash('sha512', 'Question text-4-12');
        
        $request = new CopilotRequest($id, 'Question text', [4,12], 'en');

        $response = $engine->question($request);

        $this->assertTrue($request->multipleQuestionRequest());

        $this->assertEquals($expectedQuestionHash, $request->hash());

        $this->assertInstanceOf(CopilotResponse::class, $response);

        $this->assertEquals([
            "text" => "",
            "references" => [
                4 => "Question text",
                12 => "Question text",
            ],
        ], $response->jsonSerialize());

        Http::assertSent(function (Request $request) use ($id) {
            return $request->url() == 'http://localhost:5000/transform-question' &&
                   $request->method() === 'POST' &&
                   $request['q_id'] == $id &&
                   $request['doc_list'][0] == 4 &&
                   $request['doc_list'][1] == 12 &&
                   $request['arguments']['text'] == 'Question text' &&
                   $request['template_id'] == '0' &&
                   $request['lang'] == 'en';
        });
    }

    public function test_multiple_question_aggregation(): void
    {
        config([
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        $id = Str::uuid();

        Http::fake([
            'http://localhost:5000/answer-aggregation' => Http::response([
                "q_id" => $id,
                "answer" => [
                    [
                        "text" => "First answer.",
                        "references" => [
                            [
                                "doc_id" => 1,
                                "page_number" => 2,
                            ]
                        ],
                    ],
                ]
            ], 200),
        ]);
        

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');
        
        $request = new AnswerAggregationCopilotRequest($id, 'Question text', [
            [
                "text" => "First answer.",
                "references" => [
                    [
                        "doc_id" => 1,
                        "page_number" => 2,
                    ],
                    [
                        "doc_id" => 1,
                        "page_number" => 4,
                    ]
                ]
            ],
            [
                "text" => "Second answer.",
                "references" => [
                    [
                        "doc_id" => 1,
                        "page_number" => 2,
                    ],
                    [
                        "doc_id" => 1,
                        "page_number" => 4,
                    ]
                ]
            ],
        ], 'en');

        $response = $engine->aggregate($request);

        $this->assertInstanceOf(CopilotResponse::class, $response);

        $this->assertEquals([
            "text" => "First answer.",
            "references" => [
                [
                    "doc_id" => 1,
                    "page_number" => 2,
                ]
            ],
        ], $response->jsonSerialize());

        Http::assertSent(function (Request $request) use ($id) {
            return $request->url() == 'http://localhost:5000/answer-aggregation' &&
                   $request->method() === 'POST' &&
                   $request['q_id'] == $id &&
                   $request['answers'][0]['text'] == "First answer." &&
                   $request['arguments']['text'] == 'Question text' &&
                   $request['template_id'] == '0' &&
                   $request['lang'] == 'en';
        });
    }

    public function test_english_summary(): void
    {
        config([
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        $id = Str::uuid();

        Http::fake([
            'http://localhost:5000/summarize' => Http::response([
                "doc_id" => $id,
                "summary" => "Summary."
            ], 200),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');
        
        $request = new CopilotSummarizeRequest($id, 'The text to summarize', LanguageAlpha2::English);

        $response = $engine->summarize($request);

        $this->assertInstanceOf(CopilotResponse::class, $response);

        $this->assertEquals([
            "text" => "Summary.",
            "references" => [],
        ], $response->jsonSerialize());

        Http::assertSent(function (Request $request) use ($id) {
            return $request->url() == 'http://localhost:5000/summarize' &&
                   $request->method() === 'POST' &&
                   $request['id'] == $id &&
                   $request['text'] == 'The text to summarize' &&
                   $request['lang'] == 'en';
        });
    }

    public function test_cannot_summarize_text_in_french(): void
    {
        config([
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        $id = Str::uuid();

        Http::fake([
            'http://localhost:5000/summarize' => Http::response([
                "doc_id" => $id,
                "summary" => "Summary."
            ], 200),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');
        
        $request = new CopilotSummarizeRequest($id, 'The text to summarize', LanguageAlpha2::French);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('French language not supported. Automated summaries are supported only for text in English, German, Spanish_Castilian.');

        $response = $engine->summarize($request);
    }


    public function test_tag_list_can_be_added(): void
    {
        config([
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5000/topic' => Http::response([
                "id" => 'test'
            ], 200),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');

        $tags = [[
            "topic_id" => 0,
            "topic_name" => "Tag Name",
            "definitions" => [
                [
                "lang" => "en",
                "description" => "Definition in English"
                ]
            ]
        ]];

        $engine->defineTagList('test', $tags);

        Http::assertSent(function (Request $request) use ($tags) {

            return $request->url() == 'http://localhost:5000/topic' &&
                   $request['topic_list_id'] == 'test' &&
                   $request['library_id'] == 'httplocalhost' &&
                   $request['topics'][0]['topic_id'] == $tags[0]['topic_id'] &&
                   $request['topics'][0]['topic_name'] == $tags[0]['topic_name'];
        });

    }
    
    public function test_tag_list_can_be_removed(): void
    {
        config([
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Queue::fake();

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5000/topic/test' => Http::response([
                "message" => 'Topic list test deleted'
            ], 200),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');

        $engine->removeTagList('test');

        Http::assertSent(function (Request $request) {

            return $request->url() == 'http://localhost:5000/topic/test' &&
                   $request['library_id'] == 'httplocalhost';
        });

    }


    public function test_document_can_be_tagged(): void
    {
        config([
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Queue::fake();

        $document = Document::factory()->create();

        Http::preventStrayRequests();

        $tagResponse = [
            "doc_id" => $document->getCopilotKey(),
            "topics" => [
                [
                    "based_on" => [
                        [
                        "metadata" => [
                            "doc_id" => $document->getCopilotKey(),
                            "doc_lang" => "en",
                            "library_id" => "httplocalhost",
                            "page_number" => 4,
                        ],
                        "score" => 0.5804307,
                        "text" => "Portion of the document text that highlights this tag"
                        ],
                    ],
                    "distance" => 0.5663936,
                    "suggested" => false,
                    "topic_id" => 0,
                    "topic_name" => "Tag name"
                ]
            ]
        ];

        Http::fake([
            'http://localhost:5000/topic/classify' => Http::response($tagResponse, 200),
        ]);

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');

        $suggestedTags = $engine->tag('test', $document);

        $this->assertEquals(1, $suggestedTags->count());

        $this->assertEquals("Tag name", $suggestedTags->first()['topic_name']);

        Http::assertSent(function (Request $request) use ($document) {

            return $request->url() == 'http://localhost:5000/topic/classify' &&
                   $request['topic_list_id'] == 'test' &&
                   $request['library_id'] == 'httplocalhost' &&
                   $request['doc_id'] == $document->getCopilotKey();
        });

    }
}
