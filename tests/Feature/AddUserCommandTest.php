<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class AddUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register()
    {
        $this->artisan('user:add', [
                '--email' => 'test@local.host'
            ])
            ->expectsQuestion('Select the user role', Role::GUEST->value)
            ->expectsQuestion('Please specify a password (your password must be at least 12 characters long and include a mix of uppercase, lowercase, numbers, and special characters)', Str::password())
            ->assertSuccessful()
            ->expectsOutputToContain('User test@local.host created');

        $user = User::where('email', 'test@local.host')->first();

        $this->assertNotNull($user);
        $this->assertEquals('guest', $user->role->value);
        $this->assertNotNull($user->password_updated_at);
    }

    public function test_new_users_can_register_with_role()
    {
        $this->artisan('user:add', [
                '--email' => 'test@local.host',
                '--role' => 'manager',
            ])
            ->expectsQuestion('Please specify a password (your password must be at least 12 characters long and include a mix of uppercase, lowercase, numbers, and special characters)', Str::password())
            ->assertSuccessful()
            ->expectsOutputToContain('User test@local.host created');

        $user = User::where('email', 'test@local.host')->first();

        $this->assertNotNull($user);
        $this->assertEquals('manager', $user->role->value);
    }

    public function test_user_creation_requires_email()
    {
        $this->artisan('user:add', ['--password' => Str::password()])
            ->expectsQuestion('Please enter the email address for the new user', '')
            ->expectsQuestion('Select the user role', Role::GUEST->value)
            ->assertFailed()
            ->expectsOutputToContain('The email field is required');

    }

    public function test_user_creation_requires_existing_role()
    {

        $this->withoutExceptionHandling();

        $this->artisan('user:add', [
                '--email' => 'test@local.host',
                '--role' => 'unknown',
                '--password' => Str::password()
            ])
            ->assertFailed()
            ->expectsOutputToContain('The selected role is invalid.');

    }

    public function test_user_creation_requires_complex_password()
    {
        $this->withoutExceptionHandling();

        $this->artisan('user:add', [
                '--email' => 'test@local.host',
                '--role' => 'unknown',
                '--password' => 'password'
            ])
            ->assertFailed()
            ->expectsOutputToContain('Validation errors')
            ->expectsOutputToContain("The password field must be at least 12 characters.",)
            ->expectsOutputToContain("The password field must contain at least one uppercase and one lowercase letter.",)
            ->expectsOutputToContain("The password field must contain at least one symbol.",)
            ->expectsOutputToContain("The password field must contain at least one number.");

    }
}
