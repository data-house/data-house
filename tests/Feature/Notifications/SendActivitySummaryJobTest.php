<?php

namespace Tests\Feature\Notifications;

use App\Jobs\Notifications\SendActivitySummary;
use App\Models\Document;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Notifications\ActivitySummaryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendActivitySummaryJobTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_notification_not_sent_when_no_activity_in_the_period(): void
    {
        Notification::fake();

        $user = User::factory()->withPersonalTeam()->create();

        (new SendActivitySummary($user))->handle();

        Notification::assertNothingSentTo($user);
    }

    
    public function test_last_week_activity_notified(): void
    {
        Notification::fake();

        $user = User::factory()->withPersonalTeam()->create();
        
        $team = Team::factory()->create(['personal_team' => false]);
        
        $protectedDocument = Document::factory()->visibleByAnyUser()->recycle($team)->create([
            'created_at' => now()->subDays(5),
        ]);
        
        $teamDocumentNotVisibleByUser = Document::factory()->visibleByTeamMembers()->recycle($team)->create([
            'created_at' => now()->subDays(3),
        ]);

        (new SendActivitySummary($user))->handle();

        Notification::assertSentTo($user, ActivitySummaryNotification::class, function(ActivitySummaryNotification $notification, array $channels) use ($protectedDocument) {
            return $notification->total_documents_added === 1 && 
                   $notification->documents->first()->is($protectedDocument);
        });
    }
    
    public function test_last_week_activity_includes_projects(): void
    {
        Notification::fake();

        $user = User::factory()->withPersonalTeam()->create();
        
        $team = Team::factory()->create(['personal_team' => false]);

        $team->users()->attach(
            $user, ['role' => 'guest']
        );

        $user->current_team_id = $team->getKey();
        $user->save();
        
        $teamDocument = Document::factory()
            ->visibleByTeamMembers()
            ->recycle($team)
            ->for(Project::factory()->create())
            ->create([
                'created_at' => now()->subDays(3),
            ]);

        (new SendActivitySummary($user->fresh()))->handle();

        Notification::assertSentTo($user, ActivitySummaryNotification::class, function(ActivitySummaryNotification $notification, array $channels) use ($teamDocument) {
            return $notification->total_documents_added === 1 && 
                   $notification->documents->first()->is($teamDocument) &&
                   $notification->projects->first()->is($teamDocument->project);
        });
    }
}
