<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\DocumentLibraryController;
use App\Http\Controllers\DocumentPreviewController;
use App\Http\Controllers\PdfViewerController;
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



});
