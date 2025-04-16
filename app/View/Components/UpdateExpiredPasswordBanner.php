<?php

namespace App\View\Components;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class UpdateExpiredPasswordBanner extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function user()
    {
        return Auth::user();
    }

    public function lastPasswordUpdate(): Carbon
    {
        return $this->user()->last_password_update ?? $this->user()->actions()->forEvent('password-changed')->latest()->first()?->created_at ?? $this->user()->created_at;
    }

    public function passwordExpiresOn(): ?Carbon
    {
        if(blank($days = config('auth.password_validation.expire_after_days'))){
            return null;
        }

        return $this->lastPasswordUpdate()->clone()->addDays((int) $days);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $expiration = $this->passwordExpiresOn();

        return view('components.update-expired-password-banner', [
            'passwordExpiresOn' => $expiration,
            'expired' => $expiration?->lte(now()) ?? false,
        ]);
    }
}
