<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\TesteController;
use App\Http\Middleware\CheckIsLogged;
use App\Http\Middleware\CheckIsNotLogged;
use App\Http\Controllers\StudyController;
use Illuminate\Support\Facades\Route;

// routes auth
Route::middleware([CheckIsNotLogged::class])->group(function(){
    Route::get('/login', [AuthController::class, 'login']);
    Route::post('/login', [AuthController::class, 'loginSubmit']);
    Route::get('/forgout', [AuthController::class, 'forgout']);
    Route::post('/forgout', [AuthController::class, 'forgoutSubmit']);
    
    Route::get('/', [SiteController::class, 'home'])->name('site_home');

    Route::get('/teste', [TesteController::class, 'teste'])->name('teste');

    

});

//rotas com usuário logado
Route::middleware([CheckIsLogged::class])->group(function(){
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    // ROTAS DO FRONTEND
    Route::get('/main', [MainController::class, 'index'])->name('inicio');

    //ROTAS CRIADAS PELO GPT
    Route::get('/estudar', [StudyController::class, 'index'])->name('study.index');
    Route::post('/estudar/iniciar', [StudyController::class, 'start'])->name('study.start');
    Route::get('/estudar/sessao/{session}', [StudyController::class, 'showQuestion'])->name('study.question');
    Route::post('/estudar/sessao/{session}/responder', [StudyController::class, 'answer'])->name('study.answer');
    Route::post('/estudar/sessao/{session}/proxima', [StudyController::class, 'next'])->name('study.next');
    Route::get('/estudar/sessao/{session}/resultado', [StudyController::class, 'result'])->name('study.result');


    //ROTAS DO BACKEND
    Route::get('/listQuestions', [BackController::class, 'listQuestions'])->name('listQuestions');


    /***** ROTAS DO NOTE
    
    Route::get('/newnote', [MainController::class, 'newNote'])->name('new');
    Route::post('/newnoteSubmit', [MainController::class, 'newNoteSubmit'])->name('newNoteSubmit');
    //Editar Nota
    Route::get('/editNote/{id}', [MainController::class, 'editNote'])->name('edit');
    Route::post('/editNoteSubmit', [MainController::class, 'editNoteSubmit'])->name('editNoteSubmit');
    //Deletar Nota
    Route::get('/deleteNote/{id}', [MainController::class, 'deleteNote'])->name('delete');
    Route::get('/deleteNoteConfirm/{id}', [MainController::class, 'deleteNoteConfirm'])->name('deleteNoteConfirm');
    *******/
    
});



Route::middleware(['auth'])->prefix('aluno')->name('student.')->group(function () {
    
});


