<?php
use App\Http\Controllers\Site\LibraryDocumentController;
use App\Http\Controllers\Admin\LibraryDocumentController as AdminLibraryDocumentController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureSingleSession;
use Illuminate\Support\Facades\Route;

Route::get('/biblioteca/provas', [LibraryDocumentController::class, 'index'])->name('library.exams.index');
Route::get('/biblioteca/provas/{document}/previa', [LibraryDocumentController::class, 'preview'])->name('library.exams.preview');
Route::get('/biblioteca/provas/{document}/baixar', [LibraryDocumentController::class, 'download'])->middleware('auth')->name('library.exams.download');
Route::middleware(['auth', EnsureSingleSession::class, EnsureAdmin::class])->prefix('admin/biblioteca/provas')->name('admin.library.exams.')->group(function () {
    Route::get('/', [AdminLibraryDocumentController::class, 'index'])->name('index');
    Route::post('/', [AdminLibraryDocumentController::class, 'store'])->name('store');
    Route::patch('/{document}', [AdminLibraryDocumentController::class, 'update'])->name('update');
    Route::delete('/{document}', [AdminLibraryDocumentController::class, 'destroy'])->name('destroy');
});
