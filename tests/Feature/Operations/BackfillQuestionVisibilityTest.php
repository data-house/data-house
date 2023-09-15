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

class BackfillQuestionVisibilityTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_default_visibility_applied(): void
    {
        $question = Question::factory()->create([
            'visibility' => null,
        ]);

        $this->artisan('operations:process 2023_08_11_091052_backfill_question_visibility')
            ->assertExitCode(0);

        $updatedQuestion = $question->fresh();

        $this->assertNotNull($updatedQuestion->visibility);

        $this->assertEquals(Visibility::TEAM, $updatedQuestion->visibility);
    }
    
    public function test_already_configured_visibility_not_updated(): void
    {
        $question = Question::factory()->create([
            'visibility' => Visibility::PUBLIC,
        ]);

        $this->artisan('operations:process 2023_08_11_091052_backfill_question_visibility')
            ->assertExitCode(0);

        $updatedQuestion = $question->fresh();

        $this->assertEquals(Visibility::PUBLIC, $updatedQuestion->visibility);
        $this->assertEquals($question->updated_at, $updatedQuestion->updated_at);
    }
}
