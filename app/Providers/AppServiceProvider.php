<?php

namespace App\Providers;

use App\Models\Document;
use App\Pipelines\Pipeline;
use Illuminate\Support\ServiceProvider;
use App\Jobs\Pipeline\Document\ExtractDocumentProperties;
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
        Pipeline::define(Document::class, PipelineTrigger::MODEL_CREATED, [
            ExtractDocumentProperties::class,
            // RecognizeLanguage
            // GenerateThumbnail
            // ConvertToPdfForPreview // only for docx and pptx
            // MakeSearchableUsingFullTextSearch
        ]);
    }
}
