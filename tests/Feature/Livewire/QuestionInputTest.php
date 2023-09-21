<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CurrentQuestion;
use App\Http\Livewire\QuestionInput;
use App\Models\Document;
use App\Models\Question;
use App\Models\QuestionStatus;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Support\Str;

class QuestionInputTest extends TestCase
{
    use RefreshDatabase;

    public function test_question_input_rendered()
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(QuestionInput::class, ['document' => $document]);

        $component->assertStatus(200);

        $component->assertSee('Enter your question.');
        $component->assertSee('Answer generation is powered by OpenAI');
        $component->assertDontSee(':wire:poll.visible=":wire:poll.visible"');
        $component->assertDontSee('wire:poll.visible="wire:poll.visible"');
        $component->assertSet('question', null);
        $component->assertSet('length', 0);
        $component->assertSet('exceededMaximumLength', false);
        $component->assertSet('askingQuestion', false);
        $component->assertSet('dailyQuestionLimit', 100);
    }

    public function test_question_maximum_length()
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::factory()->create();

        $this->actingAs($user);

        $questionToAsk = Str::random(201);

        $component = Livewire::test(QuestionInput::class, ['document' => $document])
            ->set('question', $questionToAsk);

        $component->assertStatus(200);

        $component->assertSee('Enter your question.');
        $component->assertSee('text-red-600 font-bold');
        $component->assertSee("201 / 200");
        $component->assertDontSee($questionToAsk);
        $component->assertSet('question', $questionToAsk);
        $component->assertSet('length', 201);
        $component->assertSet('exceededMaximumLength', true);
        $component->assertSet('askingQuestion', false);
    }
    
    public function test_question_limit_visible()
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::factory()->create();

        $this->actingAs($user);

        $questionToAsk = Str::random(201);

        $component = Livewire::test(QuestionInput::class, ['document' => $document])
            ->set('question', $questionToAsk);

        $component->assertStatus(200);

        $component->assertSee('100 questions left for today');
    }
    
    public function test_ask_question()
    {
        config([
            'copilot.driver' => 'null',
            'copilot.queue' => false,
        ]);


        $team = Team::factory()
            ->state(['name' => 'Team','personal_team' => false])
            ->create();

        $user = User::factory()->recycle($team)->withPersonalTeam()->withCurrentTeam()->create();

        $document = Document::factory()->create();

        $this->actingAs($user);

        $questionToAsk = 'Question to ask';

        $component = Livewire::test(QuestionInput::class, ['document' => $document])
            ->set('question', $questionToAsk)
            ->call('makeQuestion');

        $component->assertStatus(200);

        $component->assertSee('Enter your question.');
        $component->assertDontSee($questionToAsk);
        $component->assertSet('question', $questionToAsk);
        $component->assertSet('exceededMaximumLength', false);
        
        
        $question = Question::first();

        $this->assertNotNull($question);
        $this->assertTrue($question->user->is($user));
        $this->assertTrue($question->team->is($user->currentTeam));
        $this->assertTrue($question->questionable->is($document));
        
        $component->assertEmitted('copilot_asking', $question->uuid);
    }
    
    public function test_ask_question_is_rate_limited()
    {
        config([
            'copilot.driver' => 'null',
            'copilot.queue' => false,
            'copilot.limits.questions_per_user_per_day' => 0,
        ]);

        $team = Team::factory()
            ->state(['name' => 'Team','personal_team' => false])
            ->create();

        $user = User::factory()->recycle($team)->withPersonalTeam()->withCurrentTeam()->create();

        $document = Document::factory()->create();

        $this->actingAs($user);

        $questionToAsk = 'Question to ask';

        $component = Livewire::test(QuestionInput::class, ['document' => $document])
            ->set('question', $questionToAsk)
            ->call('makeQuestion');

        $component->assertStatus(200);

        $component->assertSee('You reached your daily limit of 0 questions/day.');
        $component->assertSee('0 questions left for today');
        
        $question = Question::first();

        $this->assertNull($question);
    }

}
