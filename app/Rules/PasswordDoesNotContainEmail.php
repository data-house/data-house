<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use SensitiveParameter;
use Illuminate\Support\Str;

class PasswordDoesNotContainEmail implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];
 
    // ...
 
    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
 
        return $this;
    }

    /**
     * Verify that the password length does not contain part of users email address
     * 
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, #[SensitiveParameter] mixed $value, Closure $fail): void
    {
        // if fields contain an email field then use this as a source of the email
        // otherwise use the currently authenticated user email address
        $email = $this->data['email'] ?? auth()->user()?->email;

        if (empty($email)) {
            $fail('The :attribute validation could not be performed because the email address is missing.');
            return;
        }

        $segments = preg_split('/[.\-+@]/', $email);

        $mustNotContain = collect([$email])
            ->when($segments !== false, function(Collection $collection) use ($segments){
                return $collection->merge($segments);
            })
            ->toArray();

        if(Str::contains($value, $mustNotContain, true)){
            $fail('The :attribute must not contain your email address or parts of it.');
        }
        
    }

}