<?php

namespace App\HelpAndSupport;

use App\Copilot\Engines\NullEngine;
use App\Copilot\Engines\OaksEngine;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Manager;

class Support
{
    /**
     * Determine if Support services are enabled.
     */
    public static function enabled(): bool
    {
        return 
            static::hasHelpPages() ||
            static::hasTicketing();
    }
    
    /**
     * Determine if Support services are disabled.
     */
    public static function disabled(): bool
    {
        return ! static::enabled();
    }

    /**
     * Determine if help pages are available.
     */
    public static function hasHelpPages(): bool
    {
        return !is_null(config('support.help', null));
    }

    /**
     * Determine if a ticketing mechanism is available.
     */
    public static function hasTicketing(): bool
    {
        return !is_null(config('support.email', null));
    }


    public static function buildSupportTicketLink(): string
    {
        if(!static::hasTicketing()){
            return '#';
        }

        $user = auth()->user()?->getKey();

        $currentUrl = url()->current();

        $params = Arr::query([
            'subject' => 'Support request for ' . config('app.name'),
            'body' => <<<"HTML"
            
            
            ------
            Do not write below this line

            {$user} - {$currentUrl}
            HTML,
        ]);

        return 'mailto:'.config('support.email').'?'.$params;
    }
    
    public static function supportEmail(): ?string
    {
        if(!static::hasTicketing()){
            return null;
        }

        return config('support.email');
    }
    
    public static function buildHelpPageLink(): string
    {
        if(!static::hasHelpPages()){
            return '#';
        }

        return config('support.help');
    }

}