<?php

namespace Tests\Feature;

use App\Models\Password;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Fortify\Features;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');

            return;
        }

        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');

            return;
        }

        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');

            return;
        }

        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class, function (object $notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');

            return;
        }

        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {

            $p = Str::password(16);

            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => $p,
                'password_confirmation' => $p,
            ]);

            $response->assertSessionHasNoErrors();

            $this->assertEquals($user->password, $user->passwords()->first()->password);

            return true;
        });
    }

    public function test_password_is_validates_using_sensible_defaults(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');

            return;
        }

        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password-length',
                'password_confirmation' => 'password-length',
            ]);

            $response->assertSessionHasErrors([
                'password' => 'The password field must contain at least one uppercase and one lowercase letter.',
                'password' => 'The password field must contain at least one number.',
            ]);

            return true;
        });
    }

    public function test_password_cannot_be_equal_to_current(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');

            return;
        }

        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response->assertSessionHasErrors(['password' => 'You cannot reuse a previously used password.']);

            return true;
        });
    }

    public function test_password_cannot_be_equal_to_older(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');

            return;
        }

        config(['auth.password_validation.historical_password_amount' => 5]);

        Notification::fake();

        $p = Str::password(16);

        $user = User::factory()
            ->has(Password::factory()->state(['password' => Hash::make($p)]))
            ->create(['password' => Hash::make('password2')]);

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user, $p) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => $p,
                'password_confirmation' => $p,
            ]);

            $response->assertSessionHasErrors(['password' => 'You cannot reuse a previously used password.']);

            return true;
        });
    }

    public function test_older_password_can_be_reused(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');

            return;
        }

        config(['auth.password_validation.historical_password_amount' => 0]);

        Notification::fake();

        $p = Str::password(16);

        $user = User::factory()
            ->has(Password::factory()->state(['password' => Hash::make($p)]))
            ->create(['password' => Hash::make('password2')]);

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user, $p) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => $p,
                'password_confirmation' => $p,
            ]);

            $response->assertSessionHasNoErrors();

            return true;
        });
    }
}
