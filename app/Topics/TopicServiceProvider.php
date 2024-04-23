<?php

namespace App\Topics;

use Illuminate\Support\ServiceProvider;

class TopicServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/topics.php', 'topics'
        );

        $this->app->singleton(TopicManager::class, function ($app) {
            return new TopicManager($app);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/topics.php' => config_path('topics.php'),
            ]);
        }        
    }
}
