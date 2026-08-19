<?php

use App\Http\Controllers\Api\Gpt\QuestionReviewApiController;
use App\Http\Middleware\EnsureGptApiToken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Billing\MercadoPagoWebhookController;
use App\Http\Controllers\Api\Gpt\QuestionWriteApiController;
use App\Http\Controllers\Api\Gpt\TaxonomyWriteApiController;
use App\Http\Controllers\Api\Gpt\QuestionBatchWriteApiController;

Route::post('/webhooks/mercado-pago', [MercadoPagoWebhookController::class, 'handle']);

Route::prefix('gpt')
    ->middleware([EnsureGptApiToken::class])
    ->group(function () {
        Route::get('/health', [QuestionReviewApiController::class, 'health']);

        Route::get('/corporations', [QuestionReviewApiController::class, 'corporations']);
        Route::get('/exam-boards', [QuestionReviewApiController::class, 'examBoards']);
        Route::get('/exams', [QuestionReviewApiController::class, 'exams']);
        Route::get('/subjects', [QuestionReviewApiController::class, 'subjects']);
        Route::get('/topics', [QuestionReviewApiController::class, 'topics']);
        Route::get('/source-materials', [QuestionReviewApiController::class, 'sourceMaterials']);

        Route::get('/questions', [QuestionReviewApiController::class, 'questions']);
        Route::post('/questions/duplicate-check', [QuestionReviewApiController::class, 'duplicateCheck']);
        //escita
        Route::post('/questions', [QuestionWriteApiController::class, 'store']);

        Route::post('/taxonomy/check', [TaxonomyWriteApiController::class, 'check']);
        Route::post('/subjects', [TaxonomyWriteApiController::class, 'storeSubject']);
        Route::post('/topics', [TaxonomyWriteApiController::class, 'storeTopic']);

        Route::get('/questions/{question}', [QuestionReviewApiController::class, 'question']);
        Route::get('/questions/{question}/comments', [QuestionReviewApiController::class, 'comments']);
        Route::get('/questions/{question}/stats', [QuestionReviewApiController::class, 'stats']);

        Route::post('/questions/batch', [QuestionBatchWriteApiController::class, 'store']);

    });