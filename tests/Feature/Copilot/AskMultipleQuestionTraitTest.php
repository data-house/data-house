<?php

namespace Tests\Feature\Copilot;

use App\Copilot\CopilotResponse;
use App\Copilot\Engines\OaksEngine;
use App\Jobs\AskMultipleQuestionJob;
use App\Jobs\AskQuestionJob;
use App\Models\Collection;
use App\Models\Disk;
use App\Models\Document;
use App\Models\Question;
use App\Models\QuestionTarget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AskMultipleQuestionTraitTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_can_be_questioned(): void
    {
        config([
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Http::preventStrayRequests();

        Queue::fake();
        
        $user = User::factory()->manager()->create();

        $collection = Collection::factory()
            ->for($user)
            ->hasAttached(
                Document::factory()->count(3),
            )
            ->create();

        $documents = $collection->documents;

        /**
         * @var \App\Models\Question
         */
        $question = null;

        $questionUuid = null;

        $expectedQuestionHash = hash('sha512', 'Do you really reply to my question?-' . $documents->map->getCopilotKey()->join('-'));

        Str::freezeUuids(function($uuid) use ($collection, &$question, &$questionUuid){

            $question = $collection->question('Do you really reply to my question?');

            $questionUuid = $uuid;
        });


        Queue::assertPushed(AskMultipleQuestionJob::class, function($job) use ($question) {
            return $job->question->is($question);
        });

        Http::assertNothingSent();

        $this->assertNotNull($questionUuid);

        $this->assertInstanceOf(Question::class, $question);

        $savedQuestion = Question::whereUuid($questionUuid)->first();

        $this->assertNotNull($savedQuestion);

        $this->assertNull($savedQuestion->user);
        $this->assertNull($savedQuestion->language);
        $this->assertNull($savedQuestion->execution_time);
        $this->assertNull($savedQuestion->answer);
        $this->assertEquals(QuestionTarget::MULTIPLE, $savedQuestion->target);

        $this->assertTrue($savedQuestion->questionable->is($collection));

        $this->assertEquals($expectedQuestionHash, $savedQuestion->hash);
        $this->assertEquals('Do you really reply to my question?', $savedQuestion->question);

    }
}
