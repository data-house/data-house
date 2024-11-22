<?php

namespace Tests\Feature\Copilot;

use App\Copilot\Engines\cloudEngine;
use App\Jobs\AskQuestionJob;
use App\Models\Disk;
use App\Models\Document;
use App\Models\Question;
use App\Models\QuestionRelation;
use App\Models\User;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\Facades\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Http\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionableTraitTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_can_be_made_synchronously_questionable(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

        Http::preventStrayRequests();

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("This is the header 1 This is a test PDF to be used as input in unit tests This is a heading 1 This is a paragraph below heading 1")
        ]);

        
        $document = Document::factory()->createQuietly([
            'disk_path' => 'test.pdf',
        ]);

        Http::fake([
            'http://localhost:5000/library/library-id/documents' => Http::response([
                "message" => "Document {$document->getCopilotKey()} added to the library library-id."
            ], 201),
        ]);

        Queue::fake();

        Storage::fake(Disk::DOCUMENTS->value);

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        
        $document->questionable();

        Http::assertSent(function (Request $request) use ($document) {
            return $request->url() == 'http://localhost:5000/library/library-id/documents' &&
                   $request['id'] === $document->getCopilotKey() && 
                   $request['lang'] === 'en';
        });

        $pdfDriver->assertCount(1);

    }

    public function test_model_can_be_removed_synchronously_from_questionable(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

        Http::preventStrayRequests();

        $pdfDriver = Pdf::fake('parse', []);
        
        $document = Document::factory()->createQuietly([
            'disk_path' => 'test.pdf',
        ]);

        Http::fake([
            'http://localhost:5000/library/*' => Http::response([
                "message" => "Document `{$document->getCopilotKey()}` removed from the library `library-id`."
            ], 200),
        ]);

        Queue::fake();

        Storage::fake(Disk::DOCUMENTS->value);

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        
        $document->unquestionable();

        Http::assertSent(function (Request $request) use ($document) {
            return $request->url() == 'http://localhost:5000/library/library-id/documents/' . $document->getCopilotKey() &&
                $request->method() === 'DELETE';
        });

        $pdfDriver->assertNoParsingRequests();

    }

    public function test_driver_instance_returned()
    {

        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

        $document = Document::factory()->createQuietly();

        $driver = $document->questionableUsing();

        $this->assertInstanceOf(cloudEngine::class, $driver);
    }

    public function test_should_be_questionable()
    {

        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

        $document = Document::factory()->createQuietly();

        $this->assertTrue($document->shouldBeQuestionable());
    }

    public function test_model_can_be_questioned(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

        Http::preventStrayRequests();

        Queue::fake();
        
        $document = Document::factory()->createQuietly();

        /**
         * @var \App\Models\Question
         */
        $answer = null;

        $questionUuid = null;

        $expectedQuestionHash = hash('sha512', 'Do you really reply to my question?-' . $document->getCopilotKey());

        Str::freezeUuids(function($uuid) use ($document, &$answer, &$questionUuid){

            $answer = $document->question('Do you really reply to my question?');

            $questionUuid = $uuid;
        });


        Queue::assertPushed(AskQuestionJob::class, function($job) use ($answer) {
            return $job->question->is($answer);
        });

        Http::assertNothingSent();

        $this->assertNotNull($questionUuid);

        $this->assertInstanceOf(Question::class, $answer);

        $savedQuestion = Question::whereUuid($questionUuid)->first();

        $this->assertNotNull($savedQuestion);

        $this->assertNull($savedQuestion->user);
        $this->assertNull($savedQuestion->language);
        $this->assertNull($savedQuestion->execution_time);
        $this->assertNull($savedQuestion->answer);

        $this->assertTrue($savedQuestion->questionable->is($document));

        $this->assertEquals($expectedQuestionHash, $savedQuestion->hash);
        $this->assertEquals('Do you really reply to my question?', $savedQuestion->question);

    }

    public function test_same_question_can_be_asked_again(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

        Http::preventStrayRequests();

        Queue::fake();
        
        $document = Document::factory()->createQuietly();

        $user = User::factory()->manager()->withPersonalTeam()->create();


        $existingQuestion = Question::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->create([
                'questionable_id' => $document->getKey(),
                'question' => 'Do you really reply to my question?'
            ]);


        /**
         * @var \App\Models\Question
         */
        $answer = null;

        $questionUuid = null;

        $expectedQuestionHash = hash('sha512', 'Do you really reply to my question?-' . $document->getCopilotKey());

        $this->actingAs($user);

        Str::freezeUuids(function($uuid) use ($document, &$answer, &$questionUuid){

            $answer = $document->question('Do you really reply to my question?');

            $questionUuid = $uuid;
        });

        Queue::assertPushed(AskQuestionJob::class, function($job) use ($answer) {
            return $job->question->is($answer);
        });

        Http::assertNothingSent();

        $this->assertNotNull($questionUuid);

        $this->assertInstanceOf(Question::class, $answer);

        $savedQuestion = Question::whereUuid($questionUuid)->first();

        $this->assertNotNull($savedQuestion);

        $this->assertTrue($savedQuestion->user->is($user));
        $this->assertTrue($savedQuestion->team->is($user->currentTeam));
        $this->assertNull($savedQuestion->language);
        $this->assertNull($savedQuestion->execution_time);
        $this->assertNull($savedQuestion->answer);

        $this->assertTrue($savedQuestion->questionable->is($document));

        $this->assertEquals($expectedQuestionHash, $savedQuestion->hash);
        $this->assertEquals('Do you really reply to my question?', $savedQuestion->question);

        $this->assertTrue($savedQuestion->related()->wherePivot('type', QuestionRelation::RETRY)->first()->is($existingQuestion));

    }
    
    public function test_ensure_other_users_can_ask_same_question(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

        Http::preventStrayRequests();

        Queue::fake();
        
        $document = Document::factory()->createQuietly();

        $user = User::factory()->manager()->withPersonalTeam()->create();

        Question::factory()
            ->create([
                'questionable_id' => $document->getKey(),
                'question' => 'Do you really reply to my question?'
            ]);


        /**
         * @var \App\Models\Question
         */
        $answer = null;

        $questionUuid = null;

        $expectedQuestionHash = hash('sha512', 'Do you really reply to my question?-' . $document->getCopilotKey());

        $this->actingAs($user);

        Str::freezeUuids(function($uuid) use ($document, &$answer, &$questionUuid){

            $answer = $document->question('Do you really reply to my question?');

            $questionUuid = $uuid;
        });

        Queue::assertPushed(AskQuestionJob::class, function($job) use ($answer) {
            return $job->question->is($answer);
        });

        Http::assertNothingSent();

        $this->assertNotNull($questionUuid);

        $this->assertInstanceOf(Question::class, $answer);

        $savedQuestion = Question::whereUuid($questionUuid)->first();

        $this->assertNotNull($savedQuestion);

        $this->assertTrue($savedQuestion->user->is($user));
        $this->assertTrue($savedQuestion->team->is($user->currentTeam));
        $this->assertNull($savedQuestion->language);
        $this->assertNull($savedQuestion->execution_time);
        $this->assertNull($savedQuestion->answer);

        $this->assertTrue($savedQuestion->questionable->is($document));

        $this->assertEquals($expectedQuestionHash, $savedQuestion->hash);
        $this->assertEquals('Do you really reply to my question?', $savedQuestion->question);

        $this->assertNull($savedQuestion->related()->wherePivot('type', QuestionRelation::RETRY)->first());

    }
}
