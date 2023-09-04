<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class QuestionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_question_listing_requires_login(): void
    {
        $response = $this->get(route('questions.index'));

        $response->assertRedirectToRoute('login');
    }

    public function test_question_listing_requires_permission(): void
    {

        $user = User::factory()->guest()->create();

        $response = $this->actingAs($user)->get(route('questions.index'));

        $response->assertSuccessful();
    }

    public function test_questions_not_viewable_if_user_of_different_team(): void
    {
        $questions = Question::factory()->count(2)->create();

        $user = User::factory()->manager()->create();

        $response = $this->actingAs($user)->get(route('questions.index'));

        $response->assertSuccessful();

        $response->assertViewHas('questions');

        $viewQuestions = $response->viewData('questions');

        $this->assertInstanceOf(LengthAwarePaginator::class, $viewQuestions);

        $this->assertEquals(0, $viewQuestions->total());

        $response->assertViewHas('searchQuery', null);
    }
    
    public function test_list_questions_for_user_in_same_team(): void
    {
        $user = User::factory()
            ->manager()
            ->withPersonalTeam()
            ->withCurrentTeam()
            ->create();

        $team = $user->currentTeam;

        $questions = Question::factory()
            ->recycle($team)
            ->count(2)
            ->create();

        Question::factory()
            ->count(2)
            ->recycle($user)
            ->create();

        $response = $this->actingAs($user)->get(route('questions.index'));

        $response->assertSuccessful();

        $response->assertViewHas('questions');

        $viewQuestions = $response->viewData('questions');

        $this->assertInstanceOf(LengthAwarePaginator::class, $viewQuestions);

        $this->assertEquals(2, $viewQuestions->total());

        $viewQuestions->each(fn ($item, $index) => $this->assertTrue($questions->get($index)->is($item)));

        $response->assertViewHas('searchQuery', null);
    }
    
    public function test_list_questions_with_protected_visibility(): void
    {
        $user = User::factory()
            ->manager()
            ->withPersonalTeam()
            ->withCurrentTeam()
            ->create();

        $questions = Question::factory()
            ->visibility(Visibility::PROTECTED)
            ->count(2)
            ->create();

        Question::factory()
            ->count(2)
            ->recycle($user)
            ->create();

        $response = $this->actingAs($user)->get(route('questions.index'));

        $response->assertSuccessful();

        $response->assertViewHas('questions');

        $viewQuestions = $response->viewData('questions');

        $this->assertInstanceOf(LengthAwarePaginator::class, $viewQuestions);

        $this->assertEquals(2, $viewQuestions->total());

        $viewQuestions->each(fn ($item, $index) => $this->assertTrue($questions->get($index)->is($item)));

        $response->assertViewHas('searchQuery', null);
    }
    
    public function test_list_questions_shows_user_questions_when_teams_are_not_used(): void
    {
        $user = User::factory()
            ->manager()
            ->create();

        $questions = Question::factory()
            ->visibility(Visibility::TEAM)
            ->recycle($user)
            ->count(2)
            ->create();

        Question::factory()
            ->count(2)
            ->create();

        $response = $this->actingAs($user)->get(route('questions.index'));

        $response->assertSuccessful();

        $response->assertViewHas('questions');

        $viewQuestions = $response->viewData('questions');

        $this->assertInstanceOf(LengthAwarePaginator::class, $viewQuestions);

        $this->assertEquals(2, $viewQuestions->total());

        $viewQuestions->each(fn ($item, $index) => $this->assertTrue($questions->get($index)->is($item)));

        $response->assertViewHas('searchQuery', null);
    }

    public function test_question_page_viewable(): void
    {
        $question = Question::factory()->create();

        $user = User::factory()->manager()->create();

        $response = $this->actingAs($user)->get(route('questions.show', $question));

        $response->assertSuccessful();

        $response->assertViewHas('question', $question);
    }

    public function test_multiple_question_page_viewable(): void
    {
        $question = Question::factory()->multiple()->create();

        $user = User::factory()->manager()->create();

        $response = $this->actingAs($user)->get(route('questions.show', $question));

        $response->assertSuccessful();

        $response->assertViewHas('question', $question);
    }


}
