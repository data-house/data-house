<?php

namespace Tests\Feature\Actions\Review;

use App\Actions\Review\RequestQuestionReview;
use App\Data\ReviewSettings;
use App\Data\TeamSettings;
use App\Models\Question;
use App\Models\QuestionReview;
use App\Models\ReviewStatus;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Notifications\QuestionReviewRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Nette\InvalidStateException;
use Tests\TestCase;

class RequestQuestionReviewTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_question_review_can_be_requested(): void
    {
        Notification::fake();
        
        $reviewerTeam = Team::factory()->create([
            'settings' => new TeamSettings(review: new ReviewSettings(questionReview: true)),
        ]);

        $reviewer = User::factory()->manager()->hasAttached($reviewerTeam, ['role' => Role::MANAGER])->create();

        $question = Question::factory()->answered()->create();

        $user = $question->user;

        $action = new RequestQuestionReview();

        $action($question, $reviewerTeam, null, $user);

        $request = QuestionReview::first();

        $this->assertEquals(ReviewStatus::SUBMITTED, $request->status);
        
        $this->assertNull($request->evaluation_result);

        $this->assertTrue($request->question->is($question));
        
        $this->assertTrue($request->requestor->is($user));

        $this->assertTrue($request->assignees()->first()->is($reviewer));

        Notification::assertSentTo($reviewer, QuestionReviewRequested::class, function($notification) use ($request){
            return $notification->review->is($request);
        });
    }
    
    public function test_could_not_request_review_when_team_has_no_eligible_assignee(): void
    {
        Notification::fake();
        
        $reviewerTeam = Team::factory()->create([
            'settings' => new TeamSettings(review: new ReviewSettings(questionReview: true)),
        ]);

        $question = Question::factory()->answered()->create();

        $user = $question->user;

        $action = new RequestQuestionReview();

        $this->expectException(InvalidStateException::class);

        $action($question, $reviewerTeam, null, $user);

        Notification::assertNothingSent();
    }

    
    public function test_could_not_request_review_when_pending_review_is_present(): void
    {
        Notification::fake();
        
        $reviewerTeam = Team::factory()->create([
            'settings' => new TeamSettings(review: new ReviewSettings(questionReview: true)),
        ]);

        $reviewer = User::factory()->manager()->hasAttached($reviewerTeam, ['role' => Role::MANAGER])->create();

        $question = Question::factory()
            ->answered()
            ->has(QuestionReview::factory()->recycle($reviewerTeam)->recycle($reviewer), 'reviews')
            ->create();

        $user = $question->user;
        
        $action = new RequestQuestionReview();
        
        $this->expectException(InvalidStateException::class);

        $this->expectExceptionMessage("A review is still in progress.");

        $action($question, $reviewerTeam, null, $user);

        Notification::assertNothingSent();
    }
}
