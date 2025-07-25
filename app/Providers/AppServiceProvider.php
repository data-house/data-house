<?php

namespace App\Providers;

use App\Console\Commands\Catalog\DeleteCatalogCommand;
use App\Data\Catalog\Flows\StructuredExtractionConfigurationData;
use App\Http\Requests\RetrievalRequest;
use App\Jobs\Pipeline\Document\AttachDocumentToLibraryCollection;
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
use App\Livewire\UpdatePasswordForm;
use App\Models\Flag;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Pipelines\PipelineTrigger;
use App\Rules\PasswordMaximumLength;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Password;
use Laravel\Pennant\Feature;
use App\Rules\PasswordDoesNotContainEmail;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Livewire\Livewire;
use Spatie\LaravelData\Support\DataConfig;

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
            LinkDocumentWithAProject::class,
            AttachDocumentToLibraryCollection::class,
            ConvertToPdf::class,
            ExtractDocumentProperties::class,
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

        $this->app->bind(RetrievalRequest::class, function ($app) {
            return RetrievalRequest::fromRequest($app['request']);
        });

        Livewire::component('profile.update-password-form', UpdatePasswordForm::class);

        Password::defaults(function () {
            // Set default rules for password validation
            return Password::min(config('auth.password_validation.minimum_length', 12))
                ->letters()
                ->numbers()
                ->symbols()
                ->mixedCase()
                ->rules(new PasswordMaximumLength, new PasswordDoesNotContainEmail(auth()->user()->email ?? request()->input('email')));
        });

        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Stone,
            'info' => Color::Blue,
            'primary' => Color::Lime,
            'success' => Color::Green,
            'warning' => Color::Amber,
        ]);

        DeleteCatalogCommand::prohibit($this->app->isProduction());

        app(DataConfig::class)->enforceMorphMap([
            'extract' => StructuredExtractionConfigurationData::class,
        ]);
    }

    protected function configureGates()
    {
        Gate::define('admin-area', function (User $user) {
            return $user->hasRole(Role::ADMIN->value);
        });

        Gate::define('viewPulse', function (User $user) {
            return $user->hasRole(Role::ADMIN->value);
        });
        
        Gate::define('addProjectMember', function (User $user) {
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
        
        Feature::define(Flag::AI_QUESTION->value, fn (User $user) => match (true) {
            $user->hasRole(Role::ADMIN->value) => true,
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
        
        Feature::define(Flag::COLLECTIONS_TOPIC_GROUP->value, fn (User $user) => match (true) {
            default => false,
        });
        
        Feature::define(Flag::VOCABULARY->value, fn (User $user) => match (true) {
            $user->hasRole(Role::ADMIN->value) => false,
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
