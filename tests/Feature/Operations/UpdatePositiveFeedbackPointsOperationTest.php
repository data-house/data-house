<?php

namespace Tests\Feature\Operations;

use App\Models\QuestionFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdatePositiveFeedbackPointsOperationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_assigned_points_to_positive_votes_are_updated(): void
    {
        $feedback = QuestionFeedback::factory()->create([
            'points' => 1,
        ]);

        $this->artisan('operations:process 2023_07_18_173651_update_positive_feedback_points_operation')
            ->assertExitCode(0);

        $this->assertEquals(2, $feedback->fresh()->points);
    }
    
    public function test_correctly_assigned_points_are_not_changed(): void
    {
        $feedback = QuestionFeedback::factory()->create([
            'points' => 2,
        ]);

        $this->artisan('operations:process 2023_07_18_173651_update_positive_feedback_points_operation')
            ->assertExitCode(0);

        $this->assertEquals(2, $feedback->fresh()->points);
        $this->assertEquals($feedback->updated_at, $feedback->fresh()->updated_at);
    }
}
