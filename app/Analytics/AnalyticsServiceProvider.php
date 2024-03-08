<?php

namespace App\Analytics;

use App\Analytics\Facades\Analytics;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Stringable;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/analytics.php', 'analytics'
        );

        $this->app->singleton(AnalyticsManager::class, function ($app) {
            return new AnalyticsManager($app);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'analytics');
        
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/analytics.php' => config_path('analytics.php'),
            ]);

            $this->publishes([
                __DIR__.'/resources/views' => resource_path('views/vendor/analytics'),
            ]);
        }
    }
}
