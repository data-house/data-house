<?php

namespace App\Actions\Fortify;

use App\Events\Auth\PasswordChanged;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ], [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->after(function($validator) use ($user, $input){
            if($this->wasPasswordAlreadyUsed($user, $input['password'])){
                $validator->errors()->add(
                    'password', __('You cannot reuse a previously used password.')
                );
            }
        })->validateWithBag('updatePassword');

        DB::transaction(function() use ($user, $input){
            $user->passwords()->create(['password' => $user->password]);
            
            $user->forceFill([
                'password' => Hash::make($input['password']),
                'password_updated_at' => now(),
            ])->save();
        });

        event(new PasswordChanged($user));
    }
}
