<?php

namespace Tests\Feature\Livewire\Notifications;

use App\Livewire\Notifications\NotificationBell;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use \Illuminate\Support\Str;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_no_notifications()
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user);
        
        Livewire::test(NotificationBell::class)
            ->assertStatus(200)
            ->assertSet('hasUnreadNotifications', false)
            ->assertSet('unreadNotificationsCount', 0)
            ->assertSee('wire:poll')
            ;
    }
    
    public function test_unread_notifications()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->create();

        $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'notification',
            'data' => 'Text',
        ]);

        $this->actingAs($user);
        
        Livewire::test(NotificationBell::class)
            ->assertStatus(200)
            ->assertSet('hasUnreadNotifications', true)
            ->assertSet('unreadNotificationsCount', '1')
            ->assertSee('wire:poll')
            ;
    }
}
