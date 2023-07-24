<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
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

        $response->assertForbidden();
    }

    public function test_questions_listed(): void
    {
        $questions = Question::factory()->count(2)->create();

        $user = User::factory()->manager()->create();

        $response = $this->actingAs($user)->get(route('questions.index'));

        $response->assertSuccessful();

        $response->assertViewHas('questions', $questions);
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
