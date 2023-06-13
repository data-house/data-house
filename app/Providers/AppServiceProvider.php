<?php

namespace App\Providers;

use App\Jobs\Pipeline\Document\ConvertToPdf;
use App\Models\Document;
use App\Pipelines\Pipeline;
use Illuminate\Support\ServiceProvider;
use App\Jobs\Pipeline\Document\ExtractDocumentProperties;
use App\Jobs\Pipeline\Document\MakeDocumentSearchable;
use App\Pipelines\PipelineTrigger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Disable model syncing for Documents as we are handling it in the pipeline
        Document::disableSearchSyncing();

        // Created executes also saved as saved is an event after creation

        // Define pipelines for Documents
        Pipeline::define(Document::class, PipelineTrigger::MODEL_CREATED, [
            ExtractDocumentProperties::class,
            ConvertToPdf::class,
            // RecognizeLanguage
            // GenerateThumbnail
        ]);
        
        Pipeline::define(Document::class, PipelineTrigger::MODEL_SAVED, [
            MakeDocumentSearchable::class,
        ]);

        
    }
}
