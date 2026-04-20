<?php

use App\Http\Controllers\Admin\CollaboratorController;
use App\Http\Controllers\Admin\CommentModerationController;
use App\Http\Controllers\Admin\CorporationController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuestionImportController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Billing\MercadoPagoWebhookController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\Student\AccountController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\QuestionCommentController;
use App\Http\Controllers\Student\QuestionDifficultyVoteController;
use App\Http\Controllers\Student\SimulatedController;
use App\Http\Controllers\Student\StudyController;
use App\Http\Controllers\Student\SubscriptionController;
use App\Http\Controllers\Student\TicketController;
use App\Http\Middleware\CheckIsLogged;
use App\Http\Middleware\CheckIsNotLogged;
use App\Http\Middleware\EnsureActiveSubscription;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureSingleSession;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('site.home');

Route::middleware([CheckIsNotLogged::class])->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('auth.login');
    Route::post('/login', [LoginController::class, 'store'])->name('auth.login.store');
    Route::post('/cadastro', [RegisterController::class, 'store'])->name('auth.register.store');
    Route::get('/esqueci-a-senha', [ForgotPasswordController::class, 'index'])->name('auth.forgot-password');
    Route::post('/esqueci-a-senha', [ForgotPasswordController::class, 'store'])->name('auth.forgot-password.store');
    Route::get('/redefinir-senha/{token}', [ResetPasswordController::class, 'edit'])->name('password.reset');
    Route::post('/redefinir-senha', [ResetPasswordController::class, 'update'])->name('password.update');
});

Route::get('/email/verificar/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['signed'])
    ->name('auth.verification.verify');

Route::middleware([CheckIsLogged::class, EnsureSingleSession::class])->group(function () {
    Route::post('/logout', [LogoutController::class, 'store'])->name('auth.logout');
    Route::post('/email/verificar/reenviar', [VerifyEmailController::class, 'resend'])->name('auth.verification.resend');

    Route::prefix('aluno')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/minha-conta', [AccountController::class, 'edit'])->name('account.edit');
        Route::put('/minha-conta', [AccountController::class, 'update'])->name('account.update');
        Route::put('/minha-conta/senha', [AccountController::class, 'updatePassword'])->name('account.password.update');
        Route::get('/assinaturas', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/assinaturas/checkout', [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
        Route::get('/assinaturas/historico', [SubscriptionController::class, 'history'])->name('subscriptions.history');
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/novo', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/responder', [TicketController::class, 'reply'])->name('tickets.reply');
        Route::post('/tickets/{ticket}/fechar', [TicketController::class, 'close'])->name('tickets.close');

        Route::middleware([EnsureActiveSubscription::class])->group(function () {
            Route::get('/estudar', [StudyController::class, 'index'])->name('study.index');
            Route::post('/estudar/iniciar', [StudyController::class, 'start'])->name('study.start');
            Route::get('/estudar/sessao/{session}', [StudyController::class, 'showQuestion'])->name('study.question');
            Route::post('/estudar/sessao/{session}/responder', [StudyController::class, 'answer'])->name('study.answer');
            Route::post('/estudar/sessao/{session}/proxima', [StudyController::class, 'next'])->name('study.next');
            Route::get('/estudar/sessao/{session}/resultado', [StudyController::class, 'result'])->name('study.result');
            Route::get('/estudar/sessao/{session}/questao/{question}/revisao', [StudyController::class, 'review'])->name('study.review');
            Route::get('/simulados', [SimulatedController::class, 'index'])->name('simulated.index');
            Route::post('/simulados', [SimulatedController::class, 'store'])->name('simulated.store');
            Route::get('/simulados/{simulatedExam}', [SimulatedController::class, 'show'])->name('simulated.show');
            Route::post('/simulados/{simulatedExam}/salvar-resposta', [SimulatedController::class, 'saveAnswer'])->name('simulated.save_answer');
            Route::post('/simulados/{simulatedExam}/finalizar', [SimulatedController::class, 'finish'])->name('simulated.finish');
            Route::get('/simulados/{simulatedExam}/resultado', [SimulatedController::class, 'result'])->name('simulated.result');
            Route::post('/questoes/{question}/comentarios', [QuestionCommentController::class, 'store'])->name('questions.comments.store');
            Route::put('/questoes/{question}/comentarios/{comment}', [QuestionCommentController::class, 'update'])->name('questions.comments.update');
            Route::post('/questoes/{question}/dificuldade', [QuestionDifficultyVoteController::class, 'store'])->name('questions.difficulty.store');
        });
    });

    Route::prefix('admin')->name('admin.')->middleware([EnsureAdmin::class])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('corporations', CorporationController::class);
        Route::resource('exams', ExamController::class);
        Route::resource('subjects', SubjectController::class);
        Route::resource('topics', TopicController::class);
        Route::resource('plans', PlanController::class);
        Route::resource('collaborators', CollaboratorController::class);
        Route::resource('customers', CustomerController::class)->only(['index', 'show', 'edit', 'update']);
        Route::resource('subscriptions', AdminSubscriptionController::class)->only(['index', 'show', 'update']);
        Route::get('questions/import', [QuestionImportController::class, 'create'])->name('questions.import.create');
        Route::post('questions/import', [QuestionImportController::class, 'store'])->name('questions.import.store');
        Route::get('questions/import/template', [QuestionImportController::class, 'downloadTemplate'])->name('questions.import.template');
        Route::get('questions/ajax/exams', [QuestionController::class, 'ajaxExams'])->name('questions.ajax.exams');
        Route::get('questions/ajax/topics', [QuestionController::class, 'ajaxTopics'])->name('questions.ajax.topics');
        Route::resource('questions', QuestionController::class);
        Route::get('/comentarios', [CommentModerationController::class, 'index'])->name('comments.index');
        Route::patch('/comentarios/{comment}/aprovar', [CommentModerationController::class, 'approve'])->name('comments.approve');
        Route::patch('/comentarios/{comment}/rejeitar', [CommentModerationController::class, 'reject'])->name('comments.reject');
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/mensagens', [AdminTicketController::class, 'reply'])->name('tickets.reply');
        Route::patch('/tickets/{ticket}/status', [AdminTicketController::class, 'updateStatus'])->name('tickets.status.update');
    });
});
