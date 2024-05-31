<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\RespectNotificationPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RespectNotificationPreferenceTest extends TestCase
{
    use RefreshDatabase;

    use RespectNotificationPreference;

    public function test_empty_delivery_channel_when_activity_notifications_disabled(): void
    {
        $user = User::factory()->withPersonalTeam()->create([
            'notification_settings' => [
                'enableMailNotifications' => true,
                'snooze' => false,
                'notifyActivity' => false,
            ]
        ]);

        $channels = $this->via($user);

        $this->assertEmpty($channels);
    }
    
    public function test_database_only_channel_when_email_notifications_disabled(): void
    {
        $user = User::factory()->withPersonalTeam()->create([
            'notification_settings' => [
                'enableMailNotifications' => false,
                'snooze' => false,
                'notifyActivity' => true,
            ]
        ]);

        $channels = $this->via($user);

        $this->assertEquals(['database'], $channels);
    }
    
    public function test_database_only_channel_when_notifications_snoozed(): void
    {
        $user = User::factory()->withPersonalTeam()->create([
            'notification_settings' => [
                'enableMailNotifications' => true,
                'snooze' => true,
                'notifyActivity' => true,
            ]
        ]);

        $channels = $this->via($user);

        $this->assertEquals(['database'], $channels);
    }
    
    public function test_all_channels_active(): void
    {
        $user = User::factory()->withPersonalTeam()->create([
            'notification_settings' => [
                'enableMailNotifications' => true,
                'snooze' => false,
                'notifyActivity' => true,
            ]
        ]);

        $channels = $this->via($user);

        $this->assertEquals(['mail', 'database'], $channels);
    }
}
