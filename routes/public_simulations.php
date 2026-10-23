<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Site\PublicSimulationController as PublicController;
use App\Http\Controllers\Admin\PublicSimulationController as AdminController;
use App\Http\Middleware\EnsureSingleSession;
use App\Http\Middleware\EnsureAdmin;

Route::get('/simulados-gratis', [PublicController::class, 'index'])->name('public-simulations.index');
Route::get('/simulados-gratis/{slug}', [PublicController::class, 'landing'])->name('public-simulations.landing');
Route::post('/simulados-gratis/{slug}/comecar', [PublicController::class, 'start'])->middleware('throttle:15,1')->name('public-simulations.start');
Route::get('/simulados-gratis/{slug}/resolver', [PublicController::class, 'take'])->name('public-simulations.take');
Route::post('/simulados-gratis/{slug}/finalizar', [PublicController::class, 'finish'])->middleware('throttle:15,1')->name('public-simulations.finish');
Route::get('/simulados-gratis/{slug}/resultado', [PublicController::class, 'result'])->name('public-simulations.result');

Route::middleware(['auth', EnsureSingleSession::class, EnsureAdmin::class])
    ->prefix('admin/simulados-publicos')
    ->name('admin.public-simulations.')
    ->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/novo', [AdminController::class, 'create'])->name('create');
        Route::get('/escopo', [AdminController::class, 'scope'])->name('scope');
        Route::get('/{simulation}/editar', [AdminController::class, 'edit'])->name('edit');
        Route::post('/', [AdminController::class, 'save'])->name('save');
        Route::patch('/{simulation}/publicacao', [AdminController::class, 'togglePublication'])->name('toggle-publication');
        Route::delete('/{simulation}', [AdminController::class, 'destroy'])->name('destroy');
    });
