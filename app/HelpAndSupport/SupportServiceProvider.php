<?php

namespace App\HelpAndSupport;

use App\Copilot\Console\FlushCommand;
use App\Copilot\Console\ImportCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class SupportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/support.php', 'support'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerBladeDirectives();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/support.php' => config_path('support.php'),
            ]);
        }
    }

    protected function registerBladeDirectives(): void
    {
        Blade::if('support', function () {
            return Support::enabled();
        });
    }
}
