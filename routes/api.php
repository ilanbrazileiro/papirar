<?php

use App\Http\Controllers\Api\Gpt\QuestionReviewApiController;
use App\Http\Middleware\EnsureGptApiToken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Billing\MercadoPagoWebhookController;

Route::post('/webhooks/mercado-pago', [MercadoPagoWebhookController::class, 'handle']);

Route::prefix('gpt')
    ->middleware([EnsureGptApiToken::class])
    ->group(function () {
        Route::get('/health', [QuestionReviewApiController::class, 'health']);

        Route::get('/corporations', [QuestionReviewApiController::class, 'corporations']);
        Route::get('/exams', [QuestionReviewApiController::class, 'exams']);
        Route::get('/subjects', [QuestionReviewApiController::class, 'subjects']);
        Route::get('/topics', [QuestionReviewApiController::class, 'topics']);
        Route::get('/source-materials', [QuestionReviewApiController::class, 'sourceMaterials']);

        Route::get('/questions', [QuestionReviewApiController::class, 'questions']);
        Route::get('/questions/{question}', [QuestionReviewApiController::class, 'question']);
        Route::post('/questions/duplicate-check', [QuestionReviewApiController::class, 'duplicateCheck']);
    });