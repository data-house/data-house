<?php

namespace App\Providers;

use App\Jobs\Pipeline\Document\ConvertToPdf;
use App\Models\Document;
use App\Pipelines\Pipeline;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use App\Jobs\Pipeline\Document\ExtractDocumentProperties;
use App\Jobs\Pipeline\Document\GenerateThumbnail;
use App\Jobs\Pipeline\Document\LinkDocumentWithAProject;
use App\Jobs\Pipeline\Document\MakeDocumentQuestionable;
use App\Jobs\Pipeline\Document\MakeDocumentSearchable;
use App\Jobs\Pipeline\Document\RecognizeLanguage;
use App\Models\Flag;
use App\Models\Role;
use App\Models\User;
use App\Pipelines\PipelineTrigger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
            GenerateThumbnail::class,
            MakeDocumentSearchable::class,
            MakeDocumentQuestionable::class,
        ]);
        
        Pipeline::define(Document::class, PipelineTrigger::MODEL_SAVED, [
            MakeDocumentSearchable::class,
        ]);
        
        Pipeline::define(Document::class, PipelineTrigger::MANUAL, [
            LinkDocumentWithAProject::class,
            RecognizeLanguage::class,
            GenerateThumbnail::class,
        ]);

        $this->configureGates();

        $this->configureFeatureFlags();

        $this->configureHelpers();


        Request::macro('isSearch', function(){
            return $this->has('s') && !is_null($this->input('s'));
        });
    }

    protected function configureGates()
    {
        Gate::define('admin-area', function (User $user) {
            return $user->hasRole(Role::ADMIN->value);
        });

        Gate::define('viewPulse', function (User $user) {
            return $user->hasRole(Role::ADMIN->value);
        });
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
            $user->hasRole(Role::ADMIN->value) => false,
            $user->hasRole(Role::MANAGER->value) => false,
            default => false,
        });
        
        Feature::define(Flag::DOCUMENT_VISIBILITY_EDIT->value, fn (User $user) => match (true) {
            $user->hasRole(Role::ADMIN->value) => false,
            default => false,
        });
        
        Feature::define(Flag::DOCUMENT_FILTERS_SOURCE->value, fn (User $user) => match (true) {
            $user->hasRole(Role::ADMIN->value) => false,
            default => false,
        });
        
        Feature::define(Flag::DOCUMENT_FILTERS_TYPE->value, fn (User $user) => match (true) {
            default => false,
        });

        Feature::define(Flag::PROJECT_FILTERS_TYPE->value, fn (User $user) => match (true) {
            $user->hasRole(Role::ADMIN->value) => true,
            default => false,
        });
        
        Feature::define(Flag::DASHBOARD->value, fn (User $user) => match (true) {
            $user->hasRole(Role::ADMIN->value) => false,
            default => false,
        });
        
        Feature::define(Flag::PROJECT_FUNDING->value, fn (User $user) => match (true) {
            default => false,
        });
        
        Feature::define(Flag::COLLECTIONS->value, fn (User $user) => match (true) {
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
