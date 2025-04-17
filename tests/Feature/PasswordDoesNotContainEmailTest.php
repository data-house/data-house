<?php

namespace Tests\Feature;

use App\Models\User;
use App\Rules\PasswordDoesNotContainEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PasswordDoesNotContainEmailTest extends TestCase
{
    use RefreshDatabase;

    public static function generateNotAcceptablePasswords()
    {
        return [
            ['testuser@localhost.local', 'testuser@localhost.local'],
            ['testuser@localhost.local', 'testuser'],
            ['testuser@localhost.local', 'localhost'],
            ['name.something@localhost.local', 'something'],
            ['name.something@localhost.local', 'name'],
            ['name.something+hello@localhost.local', 'hello'],
        ];
    }
    
    #[DataProvider('generateNotAcceptablePasswords')]
    public function test_password_does_not_contain_email_for_authenticated_user(string $email, string $passPrefix)
    {
        $user = User::factory()->create([
            'email' => $email,
        ]);

        $this->actingAs($user);

        $rule = new PasswordDoesNotContainEmail();

        $message = null;

        $rule->validate('password', $passPrefix.Str::password(4), function($error) use (&$message){
            $message = $error;
        });

        $this->assertEquals(
            'The :attribute must not contain your email address or parts of it.',
            $message
        );
        
    }

    #[DataProvider('generateNotAcceptablePasswords')]
    public function test_password_does_not_contain_email_for_guest_user(string $email, string $passPrefix)
    {
        $rule = new PasswordDoesNotContainEmail();
        $rule->setData(['email' => $email]);

        $rule->validate('password', $passPrefix.Str::password(4), function($error) use (&$message){
            $message = $error;
        });

        $this->assertEquals(
            'The :attribute must not contain your email address or parts of it.',
            $message
        );
    }

    public function test_password_validation_fails_when_email_is_missing_for_guest_user()
    {
        $rule = new PasswordDoesNotContainEmail();

        $rule->validate('password', 'guest'.Str::password(21), function($error){
            $this->assertEquals(
                'The :attribute validation could not be performed because the email address is missing.',
                $error
            );
        });
    }

}