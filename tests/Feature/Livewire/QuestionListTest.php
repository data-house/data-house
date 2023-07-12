<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\QuestionList;
use App\Models\Question;
use App\Models\QuestionStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class QuestionListTest extends TestCase
{
    use RefreshDatabase;

    public function test_answered_question_rendered()
    {
        $question = Question::factory()
            ->answered()
            ->create([
                'question' => 'Do you really reply to my question?',
            ]);

        $user = User::factory()->withPersonalTeam()->manager()->create();

        $component = Livewire::actingAs($user)->test(QuestionList::class, ['document' => $question->questionable]);

        $component->assertStatus(200);

        $component->assertSee('Do you really reply to my question?');
        $component->assertSeeHtml($question->toHtml());
        $component->assertSee('bg-stone-50');
        $component->assertSee('Like');
        $component->assertSee('Dislike');
    }

    public function test_pending_question_not_rendered()
    {
        $question = Question::factory()->create([
            'question' => 'Do you really reply to my question?',
        ]);

        $this->actingAs($question->user);

        $component = Livewire::test(QuestionList::class, ['document' => $question->questionable]);

        $component->assertStatus(200);

        $component->assertDontSee('Do you really reply to my question?');
        $component->assertDontSee('Recognizing the language of the question...');
        $component->assertDontSee('bg-lime-200');
    }

    public function test_answering_question_not_rendered()
    {
        $question = Question::factory()->create([
            'question' => 'Do you really reply to my question?',
            'status' => QuestionStatus::ANSWERING,
            'language' => 'en'
        ]);

        $this->actingAs($question->user);

        $component = Livewire::test(QuestionList::class, ['document' => $question->questionable]);

        $component->assertStatus(200);

        $component->assertDontSee('Do you really reply to my question?');
        $component->assertDontSee('Writing the answer...');
        $component->assertDontSee('bg-lime-200');
    }

    public function test_errored_question_rendered()
    {
        $question = Question::factory()->create([
            'question' => 'Do you really reply to my question?',
            'status' => QuestionStatus::ERROR,
            'language' => 'en'
        ]);

        $this->actingAs($question->user);

        $component = Livewire::test(QuestionList::class, ['document' => $question->questionable]);

        $component->assertStatus(200);

        $component->assertSee('Do you really reply to my question?');
        $component->assertSee('Cannot generate answer due to communication error.');
        $component->assertSee('bg-red-200');
    }

    public function test_cancelled_question_rendered()
    {
        $question = Question::factory()->create([
            'question' => 'Do you really reply to my question?',
            'status' => QuestionStatus::CANCELLED,
            'language' => 'en'
        ]);

        $this->actingAs($question->user);

        $component = Livewire::test(QuestionList::class, ['document' => $question->questionable]);

        $component->assertStatus(200);

        $component->assertSee('Do you really reply to my question?');
        $component->assertSee('Answer creation cancelled.');
    }
}
