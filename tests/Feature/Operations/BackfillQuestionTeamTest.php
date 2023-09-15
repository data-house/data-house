<?php

namespace Tests\Feature\Operations;

use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\CollectionType;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Question;
use App\Models\QuestionFeedback;
use App\Models\QuestionRelation;
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
                'team_id' => null,
                'visibility' => Visibility::TEAM,
            ]);

        $this->artisan('operations:process 2023_08_11_091053_backfill_question_team')
            ->assertExitCode(0);

        $updatedQuestion = $question->fresh();

        $this->assertNotNull($updatedQuestion->team_id);

        $this->assertTrue($updatedQuestion->user->is($user));
        $this->assertTrue($updatedQuestion->team->is($user->currentTeam));
    }
    
    public function test_users_without_current_team_are_handled(): void
    {
        $user = User::factory()->manager()->create();

        $question = Question::factory()
            ->recycle($user)
            ->create([
                'team_id' => null,
                'visibility' => Visibility::TEAM,
            ]);

        $this->artisan('operations:process 2023_08_11_091053_backfill_question_team')
            ->assertExitCode(0);

        $updatedQuestion = $question->fresh();

        $this->assertNull($updatedQuestion->team_id);

        $this->assertTrue($updatedQuestion->user->is($user));
        $this->assertNull($updatedQuestion->team);
    }
    
    public function test_user_team_applied_to_multiple_questions(): void
    {
        $user = User::factory()->withPersonalTeam()->withCurrentTeam()->manager()->create();

        $collection = Collection::factory()
            ->for($user)
            ->hasAttached(
                Document::factory()->count(2),
            )
            ->create();

        $documents = $collection->documents;

        $question = Question::factory()
            ->recycle($user)
            ->recycle($collection)
            ->multiple()
            ->create([
                'visibility' => Visibility::TEAM,
                'team_id' => null,
            ]);

        $subQuestions = Question::factory()
            ->count(2)
            ->answered()
            ->sequence(
                [
                    'questionable_id' => $documents->first()->getKey(),
                    'execution_time' => 120,
                    'visibility' => Visibility::SYSTEM,
                    'team_id' => null,
                    'user_id' => null,
                ],
                [
                    'questionable_id' => $documents->last()->getKey(),
                    'execution_time' => 200,
                    'visibility' => Visibility::SYSTEM,
                    'team_id' => null,
                    'user_id' => null,
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


        $subQuestions->map->fresh()->each(function($q) {
            $this->assertNull($q->team_id);
            $this->assertNull($q->user_id);
        });
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
