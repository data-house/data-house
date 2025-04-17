<?php

namespace Tests\Feature;

use App\Rules\PasswordMaximumLength;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordMaximumLengthTest extends TestCase
{
    
    public function test_exceeding_password_length_dont_pass(): void
    {
        $rule = new PasswordMaximumLength();

        $rule->validate('password', Str::password(73), function($error){
            $this->assertEquals(
                'The :attribute must not exceed 72 bytes.',
                $error
            );
        });
    }
    
    public function test_exceeding_password_length_dont_pass_with_configured_limit(): void
    {
        config(['hashing.bcrypt.limit' => 20]);

        $rule = new PasswordMaximumLength();

        $rule->validate('password', Str::password(21), function($error){
            $this->assertEquals(
                'The :attribute must not exceed 72 bytes.',
                $error
            );
        });
    }
    
    public function test_password_length_validates(): void
    {
        $rule = new PasswordMaximumLength();

        $rule->validate('password', Str::password(70), function($error){
            $this->fail("Fail validation callback called with {$error}");
        });

        $this->assertTrue(true);
    }
    
    public function test_password_length_validated_only_when_bcrypt_selected(): void
    {
        config(['hashing.driver' => 'argon2id']);

        $rule = new PasswordMaximumLength();

        $rule->validate('password', Str::password(80), function($error){
            $this->fail("Fail validation callback called with {$error}");
        });

        $this->assertTrue(true);
    }
}
