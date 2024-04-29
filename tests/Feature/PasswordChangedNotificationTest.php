<?php

namespace Tests\Feature;

use App\Events\Auth\PasswordChanged;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class PasswordChangedNotificationTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_password_changed_notification_sent(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        event(new PasswordChanged($user));

        Notification::assertSentTo($user, PasswordChangedNotification::class);

        $activity = Activity::all()->first();

        $this->assertEquals('security', $activity->log_name);
        $this->assertEquals('password-changed', $activity->event);
        $this->assertTrue($activity->subject->is($user));
    }
}
