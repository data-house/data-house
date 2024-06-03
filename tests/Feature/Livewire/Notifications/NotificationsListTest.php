<?php

namespace Tests\Feature\Livewire\Notifications;

use App\Livewire\Notifications\NotificationsList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Support\Str;

class NotificationsListTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_authentication_required()
    {      
        Livewire::test(NotificationsList::class)
            ->assertStatus(401)
            ;
    }

    public function test_no_notifications()
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user);
        
        Livewire::test(NotificationsList::class)
            ->assertStatus(200)
            ->assertSet('notifications', new DatabaseNotificationCollection())
            ;
    }
    
    public function test_unread_notifications()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->create();

        $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'notification.password-changed',
            'data' => [],
        ]);

        $this->actingAs($user);
        
        Livewire::test(NotificationsList::class)
            ->assertStatus(200)
            ->assertSet('notifications', $user->notifications)
            ->assertSee('font-bold')
            ->assertSee('Mark read')
            ;
    }
    
    public function test_read_notifications()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->create();

        $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'notification.password-changed',
            'data' => [],
            'read_at' => now(),
        ]);

        $this->actingAs($user);
        
        Livewire::test(NotificationsList::class)
            ->assertStatus(200)
            ->assertSet('notifications', $user->notifications)
            ->assertDontSee('font-bold')
            ->assertSee('Mark unread')
            ;
    }

    public function test_mark_all_read()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->create();

        $notification = $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'notification.password-changed',
            'data' => [],
        ]);

        $this->actingAs($user);
        
        Livewire::test(NotificationsList::class)
            ->assertStatus(200)
            ->assertSet('notifications', $user->notifications)
            ->call('markAllAsRead')
            ->assertDispatched('notifications')
            ;

        $this->assertTrue($notification->fresh()->read());
    }

    public function test_mark_read()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->create();

        $notification = $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'notification.password-changed',
            'data' => [],
        ]);

        $this->actingAs($user);
        
        Livewire::test(NotificationsList::class)
            ->assertStatus(200)
            ->assertSet('notifications', $user->notifications)
            ->call('markRead', $notification->id)
            ->assertDispatched('notifications')
            ;

        $this->assertTrue($notification->fresh()->read());
    }

    public function test_mark_unread()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->create();

        $notification = $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'notification.password-changed',
            'data' => [],
            'read_at' => now(),
        ]);

        $this->actingAs($user);
        
        Livewire::test(NotificationsList::class)
            ->assertStatus(200)
            ->assertSet('notifications', $user->notifications)
            ->call('markUnread', $notification->id)
            ->assertDispatched('notifications')
            ;

        $this->assertTrue($notification->fresh()->unread());
    }

    public function test_cannot_mark_read_others_notifications()
    {
        $currentUser = User::factory()
            ->withPersonalTeam()
            ->create();

        $user = User::factory()
            ->withPersonalTeam()
            ->create();

        $notification = $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'notification.password-changed',
            'data' => [],
        ]);

        $this->actingAs($currentUser);
        
        Livewire::test(NotificationsList::class)
            ->assertStatus(200)
            ->call('markRead', $notification->id)
            ->assertDispatched('notifications')
            ;

        $this->assertFalse($notification->fresh()->read());
    }

    public function test_cannot_mark_unread_others_notifications()
    {
        $currentUser = User::factory()
            ->withPersonalTeam()
            ->create();

        $user = User::factory()
            ->withPersonalTeam()
            ->create();

        $notification = $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'notification.password-changed',
            'data' => [],
            'read_at' => now(),
        ]);

        $this->actingAs($currentUser);
        
        Livewire::test(NotificationsList::class)
            ->assertStatus(200)
            ->call('markUnread', $notification->id)
            ->assertDispatched('notifications')
            ;

        $this->assertFalse($notification->fresh()->unread());
    }
}
