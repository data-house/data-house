<?php

namespace App\Copilot;

use App\Copilot\Engines\NullEngine;
use App\Copilot\Engines\OaksEngine;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Manager;

class CopilotManager extends Manager
{

    /**
     * Create an instance of the specified driver.
     *
     * @return \App\Copilot\Engines\OaksEngine
     */
    protected function createOaksDriver()
    {
        return new OaksEngine($this->getConfig('oaks'));
    }

    /**
     * Create a null engine instance.
     *
     * @return \App\Copilot\Engines\NullEngine
     */
    public function createNullDriver()
    {
        return new NullEngine;
    }
    

    /**
     * Get the copilot engine configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->config["copilot.engines.{$name}"] ?: [];
    }

    /**
     * Forget all of the resolved driver instances.
     *
     * @return $this
     */
    public function forgetDrivers()
    {
        $this->drivers = [];

        return $this;
    }

    /**
     * Set the container instance used by the manager.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the default Copilot driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        if (is_null($driver = config('copilot.driver'))) {
            return 'null';
        }

        return $driver;
    }


    /**
     * Retrieve the current daily questions limit for the user
     */
    public static function questionLimitFor(User|null $user): int
    {
        if(is_null($user)){
            return max(0, config('copilot.limits.questions_per_user_per_day'));
        }

        return max(0, RateLimiter::remaining('questions:'.$user->getKey(), config('copilot.limits.questions_per_user_per_day')));
    }


    /**
     * Track user question against daily limit
     */
    public static function trackQuestionHitFor(User|null $user): void
    {
        if(is_null($user)){
            return ;
        }

        // The rate limiter will auto-expire and the end of the calendar day UTC timezone
        // This is opposed to the Laravel daily rate limit that consider to reset
        // the limiter after 24 hours from the first hit

        RateLimiter::hit(
            key: 'questions:'.$user?->getKey(),
            decaySeconds: $secondsUntilEndOfCalendarDay = today()->endOfDay()->diffInSeconds()
        );
    }

    /**
     * Check if a given using still has questions left
     */
    public static function hasRemainingQuestions(User|null $user): bool
    {
        
        if(is_null($user)){
            return true;
        }

        if(config('copilot.limits.questions_per_user_per_day') <= 0){
            return false;
        }

        return ! RateLimiter::tooManyAttempts('questions:'.$user->getKey(), config('copilot.limits.questions_per_user_per_day'));
    }
    
    /**
     * Retrieve the current summaries limit
     */
    public static function summaryLimitFor(User|null $user): int
    {
        if(is_null($user)){
            return 0;
        }

        if(is_null($user->current_team_id)){
            return 0;
        }

        return max(0, RateLimiter::remaining('summary:team:'.$user->current_team_id, config('copilot.limits.summaries_per_team')));
    }


    /**
     * Track user question against daily limit
     */
    public static function trackSummaryHitFor(User|null $user): void
    {
        if(is_null($user)){
            return ;
        }

        if(is_null($user->current_team_id)){
            return ;
        }

        // The rate limiter will auto-expire and the end of the calendar day UTC timezone
        // This is opposed to the Laravel daily rate limit that consider to reset
        // the limiter after 24 hours from the first hit

        RateLimiter::hit(
            key: 'summary:team:'.$user->current_team_id,
            decaySeconds: $secondsUntilEndOfCalendarDay = today()->endOfDay()->diffInSeconds()
        );
    }

    /**
     * Check if a given using still has questions left
     */
    public static function hasRemainingSummaries(User|null $user): bool
    {
        
        if(is_null($user)){
            return false;
        }
        
        if(is_null($user->current_team_id)){
            return false;
        }

        if(config('copilot.limits.summaries_per_team') <= 0){
            return false;
        }

        return ! RateLimiter::tooManyAttempts('summary:team:'.$user->current_team_id, config('copilot.limits.summaries_per_team'));
    }
}