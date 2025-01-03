<?php

namespace Tests\Feature\Notifications;

use App\Jobs\Notifications\SendActivitySummaries;
use App\Jobs\Notifications\SendActivitySummary;
use App\Models\NotificationFrequency;
use App\Models\User;
use App\Models\WeekDays;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendActivitySummariesJobTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_activity_summary_queued_for_user_with_default_settings(): void
    {
        Queue::fake();

        $user = User::factory()->withPersonalTeam()->create([
            'notification_settings' => [
                'enableMailNotifications' => true,
                'snooze' => false,
                'notifyActivity' => true,
            ]
        ]);

        $this->travelTo(Carbon::parse('2024-05-27 15:00'), function(): void{
            app()->make(SendActivitySummaries::class)->handle();
        });

        Queue::assertPushed(SendActivitySummary::class, function($job) use ($user) {
            return $job->user->is($user);
        });

    }
    
    public function test_activity_summary_queued_daily_for_user(): void
    {
        Queue::fake();

        $user = User::factory()->withPersonalTeam()->create([
            'notification_settings' => [
                'enableMailNotifications' => true,
                'snooze' => false,
                'notifyActivity' => true,
                'activitySummary' => [
                    'enable' => true,
                    'frequency' => NotificationFrequency::DAILY,
                    'day' => WeekDays::MONDAY,
                    'time' => '15:00',
                    'timezone' => null,
                ],
            ],
        ]);
        
        $this->travelTo(now()->setHour(15)->setMinute(15), function(): void{
            app()->make(SendActivitySummaries::class)->handle();
        });

        Queue::assertPushed(SendActivitySummary::class, function($job) use ($user) {
            return $job->user->is($user);
        });
    }
    
    public function test_activity_summary_not_queued_daily_for_user(): void
    {
        Queue::fake();

        $user = User::factory()->withPersonalTeam()->create([
            'notification_settings' => [
                'enableMailNotifications' => true,
                'snooze' => false,
                'notifyActivity' => true,
                'activitySummary' => [
                    'enable' => true,
                    'frequency' => NotificationFrequency::DAILY,
                    'day' => WeekDays::MONDAY,
                    'time' => '15:00',
                    'timezone' => null,
                ],
            ],
        ]);
        
        $this->travelTo(now()->setHour(14)->setMinute(15), function(): void{
            app()->make(SendActivitySummaries::class)->handle();
        });

        Queue::assertNothingPushed();
    }
    
    public function test_activity_summary_queued_monthly_for_user(): void
    {
        Queue::fake();

        $user = User::factory()->withPersonalTeam()->create([
            'notification_settings' => [
                'enableMailNotifications' => true,
                'snooze' => false,
                'notifyActivity' => true,
                'activitySummary' => [
                    'enable' => true,
                    'frequency' => NotificationFrequency::MONTHLY,
                    'day' => WeekDays::MONDAY,
                    'time' => '15:00',
                    'timezone' => null,
                ],
            ],
        ]);
        
        $this->travelTo(Carbon::parse('2024-05-06 15:00'), function(): void{
            app()->make(SendActivitySummaries::class)->handle();
        });

        Queue::assertPushed(SendActivitySummary::class, function($job) use ($user) {
            return $job->user->is($user);
        });
    }
    
    public function test_activity_summary_not_queued_for_user(): void
    {
        Queue::fake();

        $user = User::factory()->withPersonalTeam()->create([
            'notification_settings' => [
                'enableMailNotifications' => true,
                'snooze' => false,
                'notifyActivity' => true,
                'activitySummary' => [
                    'enable' => false,
                ]
            ]
        ]);
        
        app()->make(SendActivitySummaries::class)->handle();

        Queue::assertNothingPushed();

    }

    public function test_activity_summary_not_queued_monthly_for_user(): void
    {
        Queue::fake();

        $user = User::factory()->withPersonalTeam()->create([
            'notification_settings' => [
                'enableMailNotifications' => true,
                'snooze' => false,
                'notifyActivity' => true,
                'activitySummary' => [
                    'enable' => true,
                    'frequency' => NotificationFrequency::MONTHLY,
                    'day' => WeekDays::FRIDAY,
                    'time' => '15:00',
                    'timezone' => null,
                ],
            ],
        ]);
        
        $this->travelTo(Carbon::parse('2024-05-06 15:00'), function(): void{
            app()->make(SendActivitySummaries::class)->handle();
        });

        Queue::assertNothingPushed();
    }

    public function test_activity_summary_not_queued_weekly_for_user(): void
    {
        Queue::fake();

        $user = User::factory()->withPersonalTeam()->create([
            'notification_settings' => [
                'enableMailNotifications' => true,
                'snooze' => false,
                'notifyActivity' => true,
                'activitySummary' => [
                    'enable' => true,
                    'frequency' => NotificationFrequency::WEEKLY,
                    'day' => WeekDays::FRIDAY,
                    'time' => '15:00',
                    'timezone' => null,
                ],
            ],
        ]);
        
        $this->travelTo(Carbon::parse('2024-05-06 15:00'), function(): void{
            app()->make(SendActivitySummaries::class)->handle();
        });

        Queue::assertNothingPushed();
    }
}
