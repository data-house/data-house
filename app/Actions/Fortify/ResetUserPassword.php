<?php

namespace App\Actions\Fortify;

use App\Events\Auth\PasswordChanged;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->after(function($validator) use ($user, $input){
            if($this->wasPasswordAlreadyUsed($user, $input['password'])){
                $validator->errors()->add(
                    'password', __('You cannot reuse a previously used password.')
                );
            }
        })->validate();
       
        DB::transaction(function() use ($user, $input){
            if($this->isHistoricalPasswordTrackingEnabled()){
                $user->passwords()->create(['password' => $user->password]);
            }
            
            $user->forceFill([
                'password' => Hash::make($input['password']),
                'password_updated_at' => now(),
                ])->save();
        });
                
        event(new PasswordChanged($user));
    }
}
