<?php

namespace Tests\Feature;

use App\Events\Auth\PasswordChanged;
use App\Livewire\UpdatePasswordForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        Event::fake();

        config(['auth.password_validation.historical_password_amount' => 2]);

        $lastPasswordUpdate = now()->subMinute();

        $this->actingAs($user = User::factory()->create(['password_updated_at' => $lastPasswordUpdate]));

        $p = Str::password();

        Livewire::test(UpdatePasswordForm::class)
                ->set('state', [
                    'current_password' => 'password',
                    'password' => $p,
                    'password_confirmation' => $p,
                ])
                ->call('updatePassword');

        $this->assertTrue(Hash::check($p, $user->fresh()->password));
        
        $this->assertNotEquals($lastPasswordUpdate->toDateTimeString(), $user->fresh()->password_updated_at->toDateTimeString());
        
        $this->assertTrue(Hash::check('password', $user->fresh()->passwords->first()->password));

        Event::assertDispatched(PasswordChanged::class, function($evt) use ($user) {
            return $user->is($evt->user);
        });
    }

    public function test_current_password_must_be_correct(): void
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(UpdatePasswordForm::class)
                ->set('state', [
                    'current_password' => 'wrong-password',
                    'password' => 'new-password',
                    'password_confirmation' => 'new-password',
                ])
                ->call('updatePassword')
                ->assertHasErrors(['current_password']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_new_passwords_must_match(): void
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(UpdatePasswordForm::class)
                ->set('state', [
                    'current_password' => 'password',
                    'password' => 'new-password',
                    'password_confirmation' => 'wrong-password',
                ])
                ->call('updatePassword')
                ->assertHasErrors(['password']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_new_password_must_differ_from_current(): void
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(UpdatePasswordForm::class)
                ->set('state', [
                    'current_password' => 'password',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ])
                ->call('updatePassword')
                ->assertHasErrors(['password']);
    }
}
