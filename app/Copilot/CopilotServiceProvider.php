<?php

namespace App\Copilot;

use App\Copilot\Console\FlushCommand;
use App\Copilot\Console\ImportCommand;
use Illuminate\Support\ServiceProvider;

class CopilotServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/copilot.php', 'copilot'
        );

        $this->app->singleton(CopilotManager::class, function ($app) {
            return new CopilotManager($app);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            
            $this->commands([
                FlushCommand::class,
                ImportCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/config/copilot.php' => config_path('copilot.php'),
            ]);
        }
    }
}
