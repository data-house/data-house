<?php

namespace App\Providers;

use App\Jobs\Pipeline\Document\ConvertToPdf;
use App\Models\Document;
use App\Pipelines\Pipeline;
use Illuminate\Support\ServiceProvider;
use App\Jobs\Pipeline\Document\ExtractDocumentProperties;
use App\Jobs\Pipeline\Document\MakeDocumentQuestionable;
use App\Jobs\Pipeline\Document\MakeDocumentSearchable;
use App\Models\Role;
use App\Models\User;
use App\Pipelines\PipelineTrigger;
use Laravel\Pennant\Feature;

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

        // Define pipelines for Documents
        Pipeline::define(Document::class, PipelineTrigger::MODEL_CREATED, [
            ExtractDocumentProperties::class,
            ConvertToPdf::class,
            MakeDocumentSearchable::class,
            MakeDocumentQuestionable::class,
            // RecognizeLanguage
            // GenerateThumbnail
        ]);
        
        Pipeline::define(Document::class, PipelineTrigger::MODEL_SAVED, [
            MakeDocumentSearchable::class,
        ]);

        $this->configureFeatureFlags();
    }

    /**
     * Configure the feature flags.
     * This serves to set the default value of a feature flag.
     * 
     * https://laravel.com/docs/10.x/pennant#defining-features
     */
    protected function configureFeatureFlags()
    {
        Feature::define('ai.question-whole-library', fn (User $user) => match (true) {
            $user->hasRole(Role::ADMIN->value) => true,
            $user->hasRole(Role::MANAGER->value) => true,
            default => false,
        });
    }
}
