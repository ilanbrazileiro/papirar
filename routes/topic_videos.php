<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TopicVideoLessonController;
use App\Http\Controllers\Student\TopicVideoController;
use App\Http\Middleware\EnsureSingleSession;
use App\Http\Middleware\EnsureAdmin;
Route::middleware(['auth',EnsureSingleSession::class])->group(function(){
 Route::get('/aluno/cursos/{course}/aulas/{video}',[TopicVideoController::class,'show'])->name('student.courses.topic-videos.show');
 Route::middleware(EnsureAdmin::class)->prefix('admin/aulas-por-topico')->name('admin.topic-videos.')->group(function(){
  Route::get('/',[TopicVideoLessonController::class,'index'])->name('index');
  Route::post('/',[TopicVideoLessonController::class,'save'])->name('save');
  Route::patch('/{video}/despublicar',[TopicVideoLessonController::class,'destroy'])->name('destroy');
 });
});
