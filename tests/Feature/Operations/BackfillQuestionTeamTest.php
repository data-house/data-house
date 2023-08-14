<?php

namespace Tests\Feature\Operations;

use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\CollectionType;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Question;
use App\Models\QuestionFeedback;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BackfillQuestionTeamTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_team_applied_to_single_questions(): void
    {
        $user = User::factory()->withPersonalTeam()->withCurrentTeam()->manager()->create();

        $question = Question::factory()
            ->recycle($user)
            ->create([
                'visibility' => Visibility::TEAM,
            ]);

        $this->artisan('operations:process 2023_08_11_091053_backfill_question_team')
            ->assertExitCode(0);

        $updatedQuestion = $question->fresh();

        $this->assertNotNull($updatedQuestion->team_id);

        $this->assertTrue($updatedQuestion->user->is($user));
        $this->assertTrue($updatedQuestion->team->is($user->currentTeam));
    }
    
    public function test_user_team_applied_to_multiple_questions(): void
    {
        $user = User::factory()->withPersonalTeam()->withCurrentTeam()->manager()->create();

        $question = Question::factory()
            ->recycle($user)
            ->multiple()
            ->create([
                'visibility' => Visibility::TEAM,
            ]);

            $subQuestions = Question::factory()
            ->count(2)
            ->answered()
            ->sequence(
                [
                    'questionable_id' => $documents->first()->getKey(),
                    'execution_time' => 120,
                ],
                [
                    'questionable_id' => $documents->last()->getKey(),
                    'execution_time' => 200,
                ],
            )
            ->create([
                'language' => 'en',
            ]);

        $subQuestions->each(function($q) use ($question) {
            $question->related()->attach($q->getKey(), ['type' => QuestionRelation::CHILDREN]);
        });

        $this->artisan('operations:process 2023_08_11_091053_backfill_question_team')
            ->assertExitCode(0);

        $updatedQuestion = $question->fresh();

        $this->assertNotNull($updatedQuestion->team_id);

        $this->assertTrue($updatedQuestion->user->is($user));
        $this->assertTrue($updatedQuestion->team->is($user->currentTeam));
    }
    
    public function test_questions_with_team_not_updated(): void
    {
        $user = User::factory()->withPersonalTeam()->withCurrentTeam()->manager()->create();

        $question = Question::factory()
            ->recycle($user)    
            ->create([
                'visibility' => Visibility::TEAM,
                'team_id' => $user->currentTeam->getKey()
            ]);

        $this->artisan('operations:process 2023_08_11_091053_backfill_question_team')
            ->assertExitCode(0);

        $updatedQuestion = $question->fresh();

        $this->assertEquals(Visibility::TEAM, $updatedQuestion->visibility);
        $this->assertEquals($question->updated_at, $updatedQuestion->updated_at);
        $this->assertTrue($updatedQuestion->user->is($user));
        $this->assertTrue($updatedQuestion->team->is($user->currentTeam));
    }
}
