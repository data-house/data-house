<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\DocumentLibraryController;
use App\Http\Controllers\DocumentPreviewController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ImportMapsController;
use App\Http\Controllers\InternalDocumentDownloadController;
use App\Http\Controllers\PdfViewerController;
use App\Http\Controllers\StartImportController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    Route::get('/documents/{document}/download', DocumentDownloadController::class)->name('documents.download');
    
    Route::get('/pdf-viewer', PdfViewerController::class)->name('pdf.viewer');

    Route::resource('/documents', DocumentController::class)->except('index');
    
    Route::get('/library', DocumentLibraryController::class)->name('documents.library');
    
    Route::resource('/imports', ImportController::class);
    
    Route::resource('imports.mappings', ImportMapsController::class)->shallow()->except(['index', 'show', 'edit', 'update', 'destroy']);
    
    Route::post('/imports-start', StartImportController::class)->name('imports.start');
});


Route::get('/documents/{document}/internal-download', InternalDocumentDownloadController::class)
    ->middleware(ValidateSignature::relative())
    ->name('documents.download.internal');
