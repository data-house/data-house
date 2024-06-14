<?php

namespace App\Copilot;

use App\Copilot\Console\ClassifyModelsCommand;
use App\Copilot\Console\FlushCommand;
use App\Copilot\Console\ImportCommand;
use App\Copilot\Console\RefreshPromptsCommand;
use App\Copilot\Console\RegisterClassifierCommand;
use App\Copilot\Console\RemoveClassifierCommand;
use App\Copilot\Console\SyncLibraryCommand;
use Illuminate\Support\Facades\Blade;
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
        $this->registerBladeDirectives();

        if ($this->app->runningInConsole()) {
            
            $this->commands([
                SyncLibraryCommand::class,
                FlushCommand::class,
                ImportCommand::class,
                ClassifyModelsCommand::class,
                RegisterClassifierCommand::class,
                RemoveClassifierCommand::class,
                RefreshPromptsCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/config/copilot.php' => config_path('copilot.php'),
            ]);
        }
    }

    protected function registerBladeDirectives(): void
    {
        Blade::if('copilot', function () {
            return Copilot::enabled();
        });
        
        Blade::if('question', function () {
            return Copilot::hasQuestionFeatures();
        });
        
        Blade::if('summary', function () {
            return Copilot::hasSummaryFeatures();
        });
        
        Blade::if('tagging', function () {
            return Copilot::hasTaggingFeatures();
        });
    }
}
