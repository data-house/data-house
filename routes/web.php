<?php

use App\Http\Controllers\Admin\AdminProjectController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\InstanceOverviewController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CreateMultipleQuestionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\DocumentLibraryController;
use App\Http\Controllers\DocumentPreviewController;
use App\Http\Controllers\DocumentThumbnailController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ImportMapsController;
use App\Http\Controllers\InternalDocumentDownloadController;
use App\Http\Controllers\PdfViewerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionReviewController;
use App\Http\Controllers\ReviewFeedbackController;
use App\Http\Controllers\StarController;
use App\Http\Controllers\StartImportController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\VocabularyConceptController;
use App\Http\Controllers\VocabularyController;
use App\Http\Middleware\ValidateSignature;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', '/dashboard');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    
    Route::get('/avatar/{avatar}.png', AvatarController::class)
        ->where('avatar', '[a-zA-Z0-9]{1}')
        ->name('avatar');
    
    Route::get('/documents/{document}/download/{filename?}', DocumentDownloadController::class)->name('documents.download');
    
    Route::get('/documents/{document}/thumbnail', DocumentThumbnailController::class)->name('documents.thumbnail');
    
    Route::get('/pdf-viewer', PdfViewerController::class)->name('pdf.viewer');

    Route::resource('/documents', DocumentController::class)->except('index');
    
    Route::get('/library', DocumentLibraryController::class)->name('documents.library');
    
    Route::resource('/imports', ImportController::class);
    
    Route::resource('imports.mappings', ImportMapsController::class)->shallow()->except(['index', 'destroy']);
    
    Route::post('/imports-start', StartImportController::class)->name('imports.start');
    
    Route::resource('questions', QuestionController::class)->only(['index', 'show']);
    
    Route::resource('question-reviews', QuestionReviewController::class)->only(['index', 'show', 'update']);
    
    Route::resource('question-reviews.review-feedbacks', ReviewFeedbackController::class)->shallow()->only(['store', 'destroy']);
    
    Route::post('multiple-question', CreateMultipleQuestionController::class)->name('multiple-questions.store');
    
    Route::resource('collections', CollectionController::class)->except(['index', 'destroy']);
    
    Route::resource('projects', ProjectController::class)->only(['index', 'show']);

    Route::put('user-preferences', UserPreferenceController::class)->name('user-preferences.update');
    Route::get('user-preferences', UserPreferenceController::class)->name('user-preferences');

    Route::resource('stars', StarController::class)->only(['index']);

    Route::resource('vocabularies', VocabularyController::class)->only(['index', 'show']);
    Route::resource('vocabulary-concepts', VocabularyConceptController::class)->only(['show']);

    Route::resource('catalogs', CatalogController::class)->only(['index', 'show']);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'can:admin-area'
])
->name('admin.')
->prefix('admin')
->group(function (): void {
    Route::get('/', InstanceOverviewController::class)->name('dashboard');
    
    Route::resource('/users', AdminUserController::class)->only(['index']);
    
    Route::resource('/projects', AdminProjectController::class)->only(['index']);
    
});


Route::get('/documents/{document}/internal-download', InternalDocumentDownloadController::class)
    ->middleware(ValidateSignature::relative())
    ->name('documents.download.internal');
