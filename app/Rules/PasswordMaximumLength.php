<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use SensitiveParameter;

class PasswordMaximumLength implements ValidationRule
{
    /**
     * Verify that the password length is not exceeding the limit of the bcrypt algorithm
     * 
     * https://github.com/laravel/framework/pull/54509
     * https://github.com/OWASP/CheatSheetSeries/issues/1532#issuecomment-2459819663
     * https://securinglaravel.com/security-tip-limiting-bcrypt-passwords-to-72-bytes/
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, #[SensitiveParameter] mixed $value, Closure $fail): void
    {
        if (config('hashing.driver') === 'bcrypt' && strlen($value) > (int)config('hashing.bcrypt.limit')) {
            $fail('The :attribute must not exceed 72 bytes.');
        }
    }
}
