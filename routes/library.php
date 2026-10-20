<?php
use App\Http\Controllers\Site\LibraryDocumentController;
use App\Http\Controllers\Admin\LibraryDocumentController as AdminLibraryDocumentController;
use App\Http\Controllers\Site\CommentedMaterialController;
use App\Http\Controllers\Admin\CommentedMaterialController as AdminCommentedMaterialController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureSingleSession;
use Illuminate\Support\Facades\Route;

Route::get('/biblioteca/provas', [LibraryDocumentController::class, 'index'])->name('library.exams.index');
Route::get('/biblioteca/provas/{document}/previa', [LibraryDocumentController::class, 'preview'])->name('library.exams.preview');
Route::get('/biblioteca/provas/{document}/baixar', [LibraryDocumentController::class, 'download'])->middleware('auth')->name('library.exams.download');
Route::get('/biblioteca/provas/{document}/{slug}', [LibraryDocumentController::class, 'show'])->name('library.exams.show');
Route::middleware(['auth', EnsureSingleSession::class, EnsureAdmin::class])->prefix('admin/biblioteca/provas')->name('admin.library.exams.')->group(function () {
    Route::get('/', [AdminLibraryDocumentController::class, 'index'])->name('index');
    Route::post('/', [AdminLibraryDocumentController::class, 'store'])->name('store');
    Route::get('/{document}/editar', [AdminLibraryDocumentController::class, 'edit'])->name('edit');
    Route::patch('/{document}', [AdminLibraryDocumentController::class, 'update'])->name('update');
    Route::delete('/{document}', [AdminLibraryDocumentController::class, 'destroy'])->name('destroy');
});

Route::get('/biblioteca/materiais', [CommentedMaterialController::class, 'index'])->name('library.materials.index');
Route::get('/biblioteca/materiais/{document}/previa', [CommentedMaterialController::class, 'preview'])->name('library.materials.preview');
Route::get('/biblioteca/materiais/{document}/baixar', [CommentedMaterialController::class, 'download'])->middleware('auth')->name('library.materials.download');
Route::get('/biblioteca/materiais/{document}/{slug}', [CommentedMaterialController::class, 'show'])->name('library.materials.show');
Route::middleware(['auth', EnsureSingleSession::class, EnsureAdmin::class])->prefix('admin/biblioteca/materiais')->name('admin.library.materials.')->group(function () {
    Route::get('/', [AdminCommentedMaterialController::class, 'index'])->name('index');
    Route::post('/', [AdminCommentedMaterialController::class, 'store'])->name('store');
    Route::get('/{document}/editar', [AdminCommentedMaterialController::class, 'edit'])->name('edit');
    Route::patch('/{document}', [AdminCommentedMaterialController::class, 'update'])->name('update');
    Route::patch('/{document}/despublicar', [AdminCommentedMaterialController::class, 'unpublish'])->name('unpublish');
});

// Materiais vinculados ao curso, com autorização por documento.
Route::get('/aluno/cursos/{course}/materiais', [\App\Http\Controllers\Student\CourseLibraryController::class, 'index'])
    ->middleware(['auth', EnsureSingleSession::class])->name('student.courses.materials');
