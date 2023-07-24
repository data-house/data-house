<?php

namespace Tests\Feature\Copilot\Engines;

use App\Copilot\AnswerAggregationCopilotRequest;
use App\Copilot\CopilotManager;
use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
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
use Tests\TestCase;

class OaksEngineTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_document_can_be_added(): void
    {

        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.oaks' => [
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
            'http://localhost:5000/documents' => Http::response([
                "id" => $document->getCopilotKey(),
                "status" => "ok"
            ], 200),
        ]);

        

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');

        $engine->update(Document::all());

        Http::assertSent(function (Request $request) use ($document, $textContent) {
            return $request->url() == 'http://localhost:5000/documents' &&
                   $request['id'] == $document->getCopilotKey() &&
                   $request['key_name'] == $document->getCopilotKeyName() &&
                   $request['content'] == $textContent;
        });

    }
    
    public function test_document_can_be_removed_from_copilot(): void
    {

        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.oaks' => [
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
            'copilot.engines.oaks' => [
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
            'copilot.engines.oaks' => [
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
            'copilot.engines.oaks' => [
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
}
