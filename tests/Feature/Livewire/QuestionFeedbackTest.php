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

        $component->assertSee('Good');
        $component->assertSee('Improvable');
        $component->assertSee('Poor');
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

        $component->assertDontSee('Good');
        $component->assertDontSee('Improvable');
        $component->assertDontSee('Poor');
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
            ->call('recordPositiveFeedback');

        $component->assertStatus(200);

        $component->assertEmitted('saved');

        $component->assertSeeInOrder(['Good', 1]);

        $feedback = ModelsQuestionFeedback::first();

        $component->assertSet('feedback', function($actual) use ($feedback) {
            return $actual instanceof ModelsQuestionFeedback && $actual->is($feedback);
        });

        $this->assertTrue($feedback->question->is($question));
        $this->assertTrue($feedback->user->is($user));
        $this->assertEquals(FeedbackVote::LIKE, $feedback->vote);
        $this->assertEquals(2, $feedback->points);
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
            ->call('recordNegativeFeedback');

        $component->assertStatus(200);

        $component->assertEmitted('saved');

        $component->assertSet('showingCommentModal', true);

        $component->assertSeeInOrder(['Poor', 1]);

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
            ->call('recordNegativeFeedback')
            ->set('feedback.reason', FeedbackReason::WRONG_ANSWER->value)
            ->set('feedback.note', 'A note')
            ->call('saveComment');

        $component->assertStatus(200);

        $component->assertSet('showingCommentModal', false);
        
        $component->assertSet('feedback', null);
        $component->assertSeeInOrder(['Poor', 1]);

        $feedback = ModelsQuestionFeedback::first();

        $this->assertTrue($feedback->question->is($question));
        $this->assertTrue($feedback->user->is($user));
        $this->assertEquals(FeedbackVote::DISLIKE, $feedback->vote);
        $this->assertEquals(-1, $feedback->points);
        $this->assertEquals(FeedbackReason::WRONG_ANSWER, $feedback->reason);
        $this->assertEquals('A note', $feedback->note);
    }

    public function test_neutral_reason_recorded()
    {
        $question = Question::factory()
            ->answered()
            ->create([
                'question' => 'Do you really reply to my question?',
            ]);

        $user = User::factory()->withPersonalTeam()->manager()->create();

        $component = Livewire::actingAs($user)
            ->test(QuestionFeedback::class, ['question' => $question])
            ->call('recordNeutralFeedback')
            ->set('feedback.reason', FeedbackReason::WRONG_ANSWER->value)
            ->set('feedback.note', 'A note')
            ->call('saveComment');

        $component->assertStatus(200);

        $component->assertSet('showingCommentModal', false);
        
        $component->assertSet('feedback', null);
        $component->assertSeeInOrder(['Improvable', 1]);

        $feedback = ModelsQuestionFeedback::first();

        $this->assertTrue($feedback->question->is($question));
        $this->assertTrue($feedback->user->is($user));
        $this->assertEquals(FeedbackVote::IMPROVABLE, $feedback->vote);
        $this->assertEquals(1, $feedback->points);
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
            ->call('recordNegativeFeedback')
            ->set('feedback.note', 'A note')
            ->call('saveComment');

        $component->assertStatus(200);

        $component->assertSet('showingCommentModal', true);
        
        $feedback = ModelsQuestionFeedback::first();

        $component->assertSet('feedback', function($actual) use ($feedback) {
            return $actual instanceof ModelsQuestionFeedback && $actual->is($feedback);
        });

        $component->assertHasErrors(['feedback.reason']);
    }

}
