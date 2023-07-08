<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CurrentQuestion;
use App\Models\Question;
use App\Models\QuestionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CurrentQuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_question_rendered()
    {
        $question = Question::factory()->create([
            'question' => 'Do you really reply to my question?',
        ]);

        $this->actingAs($question->user);

        $component = Livewire::test(CurrentQuestion::class, ['document' => $question->questionable]);

        $component->assertStatus(200);

        $component->assertSee('Do you really reply to my question?');
        $component->assertSee('Recognizing the language of the question...');
        $component->assertSee('bg-lime-200');
    }

    public function test_answering_question_rendered()
    {
        $question = Question::factory()->create([
            'question' => 'Do you really reply to my question?',
            'status' => QuestionStatus::ANSWERING,
            'language' => 'en'
        ]);

        $this->actingAs($question->user);

        $component = Livewire::test(CurrentQuestion::class, ['document' => $question->questionable]);

        $component->assertStatus(200);

        $component->assertSee('Do you really reply to my question?');
        $component->assertSee('Writing the answer...');
        $component->assertSee('bg-lime-200');
    }

    public function test_errored_question_not_rendered()
    {
        $question = Question::factory()->create([
            'question' => 'Do you really reply to my question?',
            'status' => QuestionStatus::ERROR,
            'language' => 'en'
        ]);

        $this->actingAs($question->user);

        $component = Livewire::test(CurrentQuestion::class, ['document' => $question->questionable]);

        $component->assertStatus(200);

        $component->assertDontSee('Do you really reply to my question?');
        $component->assertDontSee('Cannot generate answer due to communication error.');
        $component->assertDontSee('bg-lime-200');
    }

    public function test_cancelled_question_not_rendered()
    {
        $question = Question::factory()->create([
            'question' => 'Do you really reply to my question?',
            'status' => QuestionStatus::CANCELLED,
            'language' => 'en'
        ]);

        $this->actingAs($question->user);

        $component = Livewire::test(CurrentQuestion::class, ['document' => $question->questionable]);

        $component->assertStatus(200);

        $component->assertDontSee('Do you really reply to my question?');
        $component->assertDontSee('Answer creation cancelled.');
        $component->assertDontSee('bg-lime-200');
    }
}
