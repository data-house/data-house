<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Validation\Rules\Password;
use SensitiveParameter;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', Password::defaults(), 'confirmed'];
    }


    /**
     * Verify that the user is not reusing the current password or any of
     * the previously used ones. The historical passwords stored
     * and verified depends on the historical_password_amount
     * auth configuration value
     */
    protected function wasPasswordAlreadyUsed(User $user, #[SensitiveParameter] mixed $value): bool
    {
        $hasher = app()->make('hash');

        if($hasher->check($value, $user->getAuthPassword())){
            return true;
        }

        if(!$this->isHistoricalPasswordTrackingEnabled()){
            return false;
        }

        $historicalAmount = abs((int) config('auth.password_validation.historical_password_amount'));

        return $user
            ->passwords()
            ->latest()
            ->limit($historicalAmount)
            ->pluck('password')
            ->filter(fn($pass) => $hasher->check($value, $pass))
            ->isNotEmpty();
    }

    protected function isHistoricalPasswordTrackingEnabled(): bool
    {
        $historicalAmount = config('auth.password_validation.historical_password_amount');

        if(blank($historicalAmount)){
            return false;
        }

        return (int) $historicalAmount > 0;
    }
}
