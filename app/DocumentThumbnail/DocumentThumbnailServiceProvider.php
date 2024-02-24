<?php

namespace App\DocumentThumbnail;

use Illuminate\Support\ServiceProvider;

class DocumentThumbnailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/thumbnail.php', 'thumbnail'
        );

        $this->app->singleton(DocumentThumbnailManager::class, function ($app) {
            return new DocumentThumbnailManager($app);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/thumbnail.php' => config_path('thumbnail.php'),
            ]);
        }        
    }
}
