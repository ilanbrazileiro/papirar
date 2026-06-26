<?php

use App\Http\Controllers\Admin\CollaboratorController;
use App\Http\Controllers\Admin\CommentModerationController;
use App\Http\Controllers\Admin\ContentDashboardController;
use App\Http\Controllers\Admin\CorporationController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\PlannedExamController;
use App\Http\Controllers\Admin\QuestionPreviewController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuestionImportController;
use App\Http\Controllers\Admin\QuestionImportBatchController;
use App\Http\Controllers\Admin\QuestionImportReviewController;
use App\Http\Controllers\Admin\QuestionDraftController;
use App\Http\Controllers\Admin\QuestionDuplicateController;
use App\Http\Controllers\Admin\QuestionBulkStatusController;
use App\Http\Controllers\Admin\QuestionReportController;
use App\Http\Controllers\Admin\QuestionSimilarityController;

use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\Admin\EditorImageUploadController;
use App\Http\Controllers\Admin\SourceMaterialController;
use App\Http\Controllers\Admin\ExamSubjectSourceMaterialController;
use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\CourseAccessController;
use App\Http\Controllers\Admin\QuestionVideoLessonController;
use App\Http\Controllers\Admin\CourseReportController;

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\ResetPasswordController;
//use App\Http\Controllers\Billing\MercadoPagoWebhookController;
use App\Http\Controllers\SiteController;

use App\Http\Controllers\Student\AccountController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\QuestionCommentController;
use App\Http\Controllers\Student\QuestionDifficultyVoteController;
use App\Http\Controllers\Student\SimulatedController;
use App\Http\Controllers\Student\StudyController;
use App\Http\Controllers\Student\SubscriptionController;
use App\Http\Controllers\Student\TicketController;
use App\Http\Controllers\Student\ExamStudyController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Student\CourseStudyController;
use App\Http\Controllers\Student\CourseSimulatedController;
use App\Http\Controllers\Student\CourseCheckoutController;
use App\Http\Controllers\Student\CoursePurchaseController;
use App\Http\Controllers\Student\CoursePaymentReturnController;
use App\Http\Controllers\Student\CoursePerformanceController;
use App\Http\Controllers\Student\CourseFavoriteController;
use App\Http\Controllers\Student\CourseTrialController;

use App\Http\Middleware\CheckIsLogged;
use App\Http\Middleware\CheckIsNotLogged;
use App\Http\Middleware\EnsureAdminContentAccess;
use App\Http\Middleware\EnsureSingleSession;
use App\Http\Middleware\EnsureActiveSubscription;
use App\Http\Middleware\EnsureActiveCourseAccess;
use App\Http\Middleware\EnsureAdmin;

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
    Route::get('/cadastro', [RegisterController::class, 'create'])->name('register');
    Route::post('/cadastro', [RegisterController::class, 'store']);
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
        Route::post('/assinaturas/{subscription}/retentar', [SubscriptionController::class, 'retry'])->name('subscriptions.retry');
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/novo', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/responder', [TicketController::class, 'reply'])->name('tickets.reply');
        Route::post('/tickets/{ticket}/fechar', [TicketController::class, 'close'])->name('tickets.close');

        /*
        |--------------------------------------------------------------------------
        | Novo fluxo principal do aluno
        |--------------------------------------------------------------------------
        |
        | O aluno acessa primeiro "Meus Cursos". Qualquer tela que exiba questões,
        | sessão de estudo ou simulado dentro de um curso precisa passar pelo
        | middleware EnsureActiveCourseAccess.
        |
        */
        Route::get('/cursos', [StudentCourseController::class, 'index'])->name('courses.index');
        Route::post('/cursos/{course}/checkout', [CourseCheckoutController::class, 'checkout'])->name('courses.checkout');
        Route::post('/cursos/{course}/trial', [CourseTrialController::class, 'start'])->name('courses.trial.start');
        Route::get('/cursos/checkout/{transaction}/sucesso', [CoursePaymentReturnController::class, 'success'])->name('courses.checkout.success');
        Route::get('/cursos/checkout/{transaction}/pendente', [CoursePaymentReturnController::class, 'pending'])->name('courses.checkout.pending');
        Route::get('/cursos/checkout/{transaction}/falha', [CoursePaymentReturnController::class, 'failure'])->name('courses.checkout.failure');
        Route::get('/compras', [CoursePurchaseController::class, 'index'])->name('purchases.index');

        Route::middleware([EnsureActiveCourseAccess::class])->group(function () {
            Route::get('/cursos/estudar/sessao/{session}', [CourseStudyController::class, 'showQuestion'])->name('course-study.question');
            Route::post('/cursos/estudar/sessao/{session}/responder', [CourseStudyController::class, 'answer'])->name('course-study.answer');
            Route::post('/cursos/estudar/sessao/{session}/proxima', [CourseStudyController::class, 'next'])->name('course-study.next');
            Route::get('/cursos/estudar/sessao/{session}/questao/{question}/revisao', [CourseStudyController::class, 'review'])->name('course-study.review');
            Route::get('/cursos/estudar/sessao/{session}/resultado', [CourseStudyController::class, 'result'])->name('course-study.result');

            Route::get('/cursos/{course}', [StudentCourseController::class, 'show'])->name('courses.show');
            Route::get('/cursos/{course}/estudar', [StudentCourseController::class, 'study'])->name('courses.study');
            Route::post('/cursos/{course}/estudar/iniciar', [CourseStudyController::class, 'start'])->name('course-study.start');
            Route::get('/cursos/{course}/desempenho', [CoursePerformanceController::class, 'show'])->name('courses.performance');
            
            Route::get('/cursos/{course}/favoritas', [CourseFavoriteController::class, 'index'])->name('courses.favorites.index');
            Route::get('/cursos/{course}/favoritas/questoes/{question}', [CourseFavoriteController::class, 'show'])->name('courses.favorites.show');
            Route::post('/cursos/{course}/favoritas/questoes/{question}/refazer', [CourseFavoriteController::class, 'retry'])->name('courses.favorites.retry');
            Route::post('/cursos/{course}/questoes/{question}/favoritar', [CourseFavoriteController::class, 'toggle'])->name('courses.questions.favorite');
            Route::patch('/cursos/{course}/favoritas/questoes/{question}/anotacao', [CourseFavoriteController::class, 'updateNote'])->name('courses.favorites.note');

            /*
             * Rotas do Lote 3 — Simulados por curso.
             * Se o controller CourseSimulatedController ainda não tiver sido copiado,
             * aplique primeiro os arquivos do Lote 3 antes de acessar essas URLs.
             */
            Route::get('/cursos/{course}/simulados', [CourseSimulatedController::class, 'index'])->name('courses.simulated.index');
            Route::post('/cursos/{course}/simulados', [CourseSimulatedController::class, 'store'])->name('courses.simulated.store');
            Route::get('/cursos/{course}/simulados/{simulatedExam}', [CourseSimulatedController::class, 'show'])->name('courses.simulated.show');
            Route::post('/cursos/{course}/simulados/{simulatedExam}/salvar-resposta', [CourseSimulatedController::class, 'saveAnswer'])->name('courses.simulated.save_answer');
            Route::post('/cursos/{course}/simulados/{simulatedExam}/finalizar', [CourseSimulatedController::class, 'finish'])->name('courses.simulated.finish');
            Route::get('/cursos/{course}/simulados/{simulatedExam}/resultado', [CourseSimulatedController::class, 'result'])->name('courses.simulated.result');
        });

        /*
        |--------------------------------------------------------------------------
        | Fluxos antigos do aluno — desativados
        |--------------------------------------------------------------------------
        |
        | As rotas antigas continuam existindo apenas para evitar erro 404 em links
        | salvos, botões antigos ou histórico do navegador. Todas redirecionam para
        | "Meus Cursos", que é o novo ponto de entrada da área do aluno.
        |
        */
        Route::get('/estudar', fn () => redirect()->route('student.courses.index'))->name('study.index');
        Route::get('/estudar/filtro-livre', fn () => redirect()->route('student.courses.index'))->name('study.filter');
        Route::post('/estudar/iniciar', fn () => redirect()->route('student.courses.index'))->name('study.start');
        Route::get('/estudar/sessao/{session}', fn () => redirect()->route('student.courses.index'))->name('study.question');
        Route::post('/estudar/sessao/{session}/responder', fn () => redirect()->route('student.courses.index'))->name('study.answer');
        Route::post('/estudar/sessao/{session}/proxima', fn () => redirect()->route('student.courses.index'))->name('study.next');
        Route::get('/estudar/sessao/{session}/resultado', fn () => redirect()->route('student.courses.index'))->name('study.result');
        Route::get('/estudar/sessao/{session}/questao/{question}/revisao', fn () => redirect()->route('student.courses.index'))->name('study.review');

        Route::get('/simulados', fn () => redirect()->route('student.courses.index'))->name('simulated.index');
        Route::post('/simulados', fn () => redirect()->route('student.courses.index'))->name('simulated.store');
        Route::get('/simulados/{simulatedExam}', fn () => redirect()->route('student.courses.index'))->name('simulated.show');
        Route::post('/simulados/{simulatedExam}/salvar-resposta', fn () => redirect()->route('student.courses.index'))->name('simulated.save_answer');
        Route::post('/simulados/{simulatedExam}/finalizar', fn () => redirect()->route('student.courses.index'))->name('simulated.finish');
        Route::get('/simulados/{simulatedExam}/resultado', fn () => redirect()->route('student.courses.index'))->name('simulated.result');

        Route::get('/estudo-por-concurso', fn () => redirect()->route('student.courses.index'))->name('exam-study.index');
        Route::post('/estudo-por-concurso/iniciar', fn () => redirect()->route('student.courses.index'))->name('exam-study.start');
        Route::get('/estudo-por-concurso/corporations/{corporation}/exams', fn () => redirect()->route('student.courses.index'))->name('exam-study.corporations.exams');
        Route::get('/estudo-por-concurso/exams/{exam}/subjects', fn () => redirect()->route('student.courses.index'))->name('exam-study.exams.subjects');

        /*
         * Comentários e voto de dificuldade permanecem ativos temporariamente
         * porque podem ser usados pelas telas de revisão do novo fluxo por curso.
         * Em lote futuro, o ideal é vinculá-los ao curso/sessão para validação
         * explícita de acesso.
         */
        Route::post('/questoes/{question}/comentarios', [QuestionCommentController::class, 'store'])->name('questions.comments.store');
        Route::put('/questoes/{question}/comentarios/{comment}', [QuestionCommentController::class, 'update'])->name('questions.comments.update');
        Route::post('/questoes/{question}/dificuldade', [QuestionDifficultyVoteController::class, 'store'])->name('questions.difficulty.store');
    });

    Route::prefix('admin')
        ->name('admin.')
        ->middleware([EnsureAdmin::class, EnsureAdminContentAccess::class])
        ->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
            Route::get('/conteudo/dashboard', ContentDashboardController::class)->name('content.dashboard');

            Route::resource('corporations', CorporationController::class);
            Route::resource('exams', ExamController::class);
            Route::resource('subjects', SubjectController::class);
            Route::resource('topics', TopicController::class);
            Route::resource('source-materials', SourceMaterialController::class);
            Route::resource('courses', CourseController::class);
            Route::resource('course-accesses', CourseAccessController::class)->except(['show']);
            Route::patch('course-accesses/{courseAccess}/cancel', [CourseAccessController::class, 'cancel'])->name('course-accesses.cancel');
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/courses', [CourseReportController::class, 'index'])->name('courses.index');
                Route::get('/courses/{course}', [CourseReportController::class, 'show'])->name('courses.show');
                Route::get('/questions', [QuestionReportController::class, 'index'])->name('questions.index');
            });

            Route::get('exams/{exam}/source-materials', [ExamSubjectSourceMaterialController::class, 'edit'])->name('exams.source-materials.edit');
            Route::put('exams/{exam}/source-materials', [ExamSubjectSourceMaterialController::class, 'update'])->name('exams.source-materials.update');
            Route::get('/minha-conta', [AdminAccountController::class, 'edit'])->name('account.edit');
            Route::put('/minha-conta', [AdminAccountController::class, 'update'])->name('account.update');
            Route::put('/minha-conta/senha', [AdminAccountController::class, 'updatePassword'])->name('account.password.update');
            Route::resource('plans', PlanController::class);
            Route::resource('collaborators', CollaboratorController::class);
            Route::resource('customers', CustomerController::class)->only(['index', 'show', 'edit', 'update']);
            Route::post('/customers/{customer}/grant-access', [CustomerController::class, 'grantAccess'])->name('customers.grant-access');
            Route::resource('subscriptions', AdminSubscriptionController::class)->only(['index', 'show', 'update']);
            Route::resource('question-video-lessons', QuestionVideoLessonController::class)->except(['show']);

            Route::get('questions/similar', [QuestionSimilarityController::class, 'index'])->name('questions.similar.index');
            Route::post('questions/imports/{batch}/rows/{row}/import', [QuestionImportReviewController::class, 'importRow'])->name('question-import-batches.rows.import');
            Route::post('questions/imports/{batch}/rows/{row}/ignore', [QuestionImportReviewController::class, 'ignoreRow'])->name('question-import-batches.rows.ignore');
            Route::patch('questions/bulk-status', [QuestionBulkStatusController::class, 'update'])->name('questions.bulk-status');
            Route::post('questions/check-duplicate', QuestionDuplicateController::class)->name('questions.check-duplicate');
            Route::get('questions/drafts', QuestionDraftController::class)->name('questions.drafts');
            Route::get('questions/{question}/preview', QuestionPreviewController::class)->name('questions.preview');
            Route::get('questions/imports/{batch}/review', [QuestionImportReviewController::class, 'review'])->name('question-import-batches.review');
            Route::post('questions/imports/{batch}/confirm', [QuestionImportReviewController::class, 'confirm'])->name('question-import-batches.confirm');
            Route::post('questions/imports/{batch}/cancel', [QuestionImportReviewController::class, 'cancel'])->name('question-import-batches.cancel');
            Route::get('questions/imports', [QuestionImportBatchController::class, 'index'])->name('question-import-batches.index');
            Route::get('questions/imports/{questionImportBatch}', [QuestionImportBatchController::class, 'show'])->name('question-import-batches.show');
            Route::get('questions/import', [QuestionImportController::class, 'create'])->name('questions.import.create');
            Route::post('questions/import/direct', [QuestionImportController::class, 'storeDirect'])->name('questions.import.direct');
            Route::post('questions/import', [QuestionImportController::class, 'store'])->name('questions.import.store');
            Route::get('questions/import/template', [QuestionImportController::class, 'downloadTemplate'])->name('questions.import.template');
            Route::get('questions/ajax/exams', [QuestionController::class, 'ajaxExams'])->name('questions.ajax.exams');
            Route::get('questions/ajax/topics', [QuestionController::class, 'ajaxTopics'])->name('questions.ajax.topics');
            Route::resource('questions', QuestionController::class);
            Route::post('/editor/images/upload', [EditorImageUploadController::class, 'store'])->name('editor-images.upload');
            Route::get('questions/ajax/source-materials', [QuestionController::class, 'ajaxSourceMaterials'])->name('questions.ajax-source-materials');
            Route::get('questions/import/source-materials-csv', [QuestionImportController::class, 'downloadSourceMaterialsCsv'])->name('questions.import.source-materials-csv');

            Route::get('/comentarios', [CommentModerationController::class, 'index'])->name('comments.index');
            Route::patch('/comentarios/{comment}/aprovar', [CommentModerationController::class, 'approve'])->name('comments.approve');
            Route::patch('/comentarios/{comment}/rejeitar', [CommentModerationController::class, 'reject'])->name('comments.reject');
            Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
            Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
            Route::post('/tickets/{ticket}/mensagens', [AdminTicketController::class, 'reply'])->name('tickets.reply');
            Route::patch('/tickets/{ticket}/status', [AdminTicketController::class, 'updateStatus'])->name('tickets.status.update');
            Route::get('/questions/import/topics-csv', [QuestionImportController::class, 'downloadTopicsCsv'])->name('questions.import.topics-csv');
            Route::resource('planned-exams', PlannedExamController::class)->except(['show', 'destroy'])->names('planned-exams');
        });
});
