<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\QuestionFeedback;
use App\Http\Livewire\QuestionList;
use App\Models\FeedbackReason;
use App\Models\FeedbackVote;
use App\Models\Question;
use App\Models\QuestionFeedback as ModelsQuestionFeedback;
use App\Models\QuestionStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class QuestionFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_feedback_widget_rendered()
    {
        $question = Question::factory()
            ->answered()
            ->create([
                'question' => 'Do you really reply to my question?',
            ]);

        $user = User::factory()->withPersonalTeam()->manager()->create();

        $component = Livewire::actingAs($user)->test(QuestionFeedback::class, ['question' => $question]);

        $component->assertStatus(200);

        $component->assertSee('Like');
        $component->assertSee('Dislike');
    }
    
    public function test_feedback_widget_not_rendered()
    {
        $question = Question::factory()
            ->answered()
            ->create([
                'question' => 'Do you really reply to my question?',
            ]);

        $user = User::factory()->guest()->create();

        $component = Livewire::actingAs($user)->test(QuestionFeedback::class, ['question' => $question]);

        $component->assertStatus(200);

        $component->assertDontSee('Like');
        $component->assertDontSee('Dislike');
    }

    public function test_positive_feedback_recorded()
    {
        $question = Question::factory()
            ->answered()
            ->create([
                'question' => 'Do you really reply to my question?',
            ]);

        $user = User::factory()->withPersonalTeam()->manager()->create();

        $component = Livewire::actingAs($user)
            ->test(QuestionFeedback::class, ['question' => $question])
            ->call('like');

        $component->assertStatus(200);

        $component->assertEmitted('saved');

        $component->assertSeeInOrder(['Like', 1]);

        $feedback = ModelsQuestionFeedback::first();

        $component->assertSet('feedback', function($actual) use ($feedback) {
            return $actual instanceof ModelsQuestionFeedback && $actual->is($feedback);
        });

        $this->assertTrue($feedback->question->is($question));
        $this->assertTrue($feedback->user->is($user));
        $this->assertEquals(FeedbackVote::LIKE, $feedback->vote);
        $this->assertEquals(1, $feedback->points);
        $this->assertNull($feedback->reason);
        $this->assertNull($feedback->note);
    }

    public function test_negative_feedback_recorded()
    {
        $question = Question::factory()
            ->answered()
            ->create([
                'question' => 'Do you really reply to my question?',
            ]);

        $user = User::factory()->withPersonalTeam()->manager()->create();

        $component = Livewire::actingAs($user)
            ->test(QuestionFeedback::class, ['question' => $question])
            ->call('dislike');

        $component->assertStatus(200);

        $component->assertEmitted('saved');

        $component->assertSet('showingDislikeModal', true);

        $component->assertSeeInOrder(['Dislike', 1]);

        $feedback = ModelsQuestionFeedback::first();

        $component->assertSet('feedback', function($actual) use ($feedback) {
            return $actual instanceof ModelsQuestionFeedback && $actual->is($feedback);
        });

        $this->assertTrue($feedback->question->is($question));
        $this->assertTrue($feedback->user->is($user));
        $this->assertEquals(FeedbackVote::DISLIKE, $feedback->vote);
        $this->assertEquals(-1, $feedback->points);
        $this->assertNull($feedback->reason);
        $this->assertNull($feedback->note);
    }

    public function test_negative_reason_recorded()
    {
        $question = Question::factory()
            ->answered()
            ->create([
                'question' => 'Do you really reply to my question?',
            ]);

        $user = User::factory()->withPersonalTeam()->manager()->create();

        $component = Livewire::actingAs($user)
            ->test(QuestionFeedback::class, ['question' => $question])
            ->call('dislike')
            ->set('feedback.reason', FeedbackReason::WRONG_ANSWER->value)
            ->set('feedback.note', 'A note')
            ->call('saveDislikeComment');

        $component->assertStatus(200);

        $component->assertSet('showingDislikeModal', false);
        
        $component->assertSet('feedback', null);
        $component->assertSeeInOrder(['Dislike', 1]);

        $feedback = ModelsQuestionFeedback::first();

        $this->assertTrue($feedback->question->is($question));
        $this->assertTrue($feedback->user->is($user));
        $this->assertEquals(FeedbackVote::DISLIKE, $feedback->vote);
        $this->assertEquals(-1, $feedback->points);
        $this->assertEquals(FeedbackReason::WRONG_ANSWER, $feedback->reason);
        $this->assertEquals('A note', $feedback->note);
    }

    public function test_validation_handled_when_providing_feedback_reason()
    {
        $question = Question::factory()
            ->answered()
            ->create([
                'question' => 'Do you really reply to my question?',
            ]);

        $user = User::factory()->withPersonalTeam()->manager()->create();

        $component = Livewire::actingAs($user)
            ->test(QuestionFeedback::class, ['question' => $question])
            ->call('dislike')
            ->set('feedback.note', 'A note')
            ->call('saveDislikeComment');

        $component->assertStatus(200);

        $component->assertSet('showingDislikeModal', true);
        
        $feedback = ModelsQuestionFeedback::first();

        $component->assertSet('feedback', function($actual) use ($feedback) {
            return $actual instanceof ModelsQuestionFeedback && $actual->is($feedback);
        });

        $component->assertHasErrors(['feedback.reason']);
    }

}
