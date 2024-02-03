<?php

namespace App\Providers;

use App\Jobs\Pipeline\Document\ConvertToPdf;
use App\Models\Document;
use App\Pipelines\Pipeline;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use App\Jobs\Pipeline\Document\ExtractDocumentProperties;
use App\Jobs\Pipeline\Document\LinkDocumentWithAProject;
use App\Jobs\Pipeline\Document\MakeDocumentQuestionable;
use App\Jobs\Pipeline\Document\MakeDocumentSearchable;
use App\Jobs\Pipeline\Document\RecognizeLanguage;
use App\Models\Flag;
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
            LinkDocumentWithAProject::class,
            ConvertToPdf::class,
            RecognizeLanguage::class,
            MakeDocumentSearchable::class,
            MakeDocumentQuestionable::class,
            // GenerateThumbnail
        ]);
        
        Pipeline::define(Document::class, PipelineTrigger::MODEL_SAVED, [
            MakeDocumentSearchable::class,
        ]);

        $this->configureFeatureFlags();

        $this->configureHelpers();
    }

    /**
     * Configure the feature flags.
     * This serves to set the default value of a feature flag.
     * 
     * https://laravel.com/docs/10.x/pennant#defining-features
     */
    protected function configureFeatureFlags()
    {
        Feature::define(Flag::AI_QUESTION_WHOLE_LIBRARY->value, fn (User $user) => match (true) {
            $user->hasRole(Role::ADMIN->value) => true,
            $user->hasRole(Role::MANAGER->value) => true,
            default => false,
        });
        
        Feature::define(Flag::DOCUMENT_VISIBILITY_EDIT->value, fn (User $user) => match (true) {
            $user->hasRole(Role::ADMIN->value) => true,
            default => false,
        });
        
        Feature::define(Flag::DOCUMENT_FILTERS_SOURCE->value, fn (User $user) => match (true) {
            $user->hasRole(Role::ADMIN->value) => true,
            default => false,
        });
        
        Feature::define(Flag::DASHBOARD->value, fn (User $user) => match (true) {
            $user->hasRole(Role::ADMIN->value) => true,
            default => false,
        });
        
        Feature::define(Flag::PROJECT_FUNDING->value, fn (User $user) => match (true) {
            default => false,
        });
    }

    protected function configureHelpers()
    {
        Str::macro('domain', function($value){

            if(!Str::isUrl($value)){
                return $value;
            }

            return parse_url($value, PHP_URL_HOST);
        });
    }
}
