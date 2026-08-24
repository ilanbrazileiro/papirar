<?php

use App\Http\Controllers\Api\Gpt\QuestionReviewApiController;
use App\Http\Middleware\EnsureGptApiToken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Billing\MercadoPagoWebhookController;
use App\Http\Controllers\Api\Gpt\QuestionWriteApiController;
use App\Http\Controllers\Api\Gpt\TaxonomyWriteApiController;
use App\Http\Controllers\Api\Gpt\QuestionBatchWriteApiController;
use App\Http\Controllers\Api\Gpt\QuestionReviewerApiController;
use App\Http\Controllers\Api\Gpt\QuestionTaxonomyReviewApiController;
use App\Http\Controllers\Api\Gpt\MarketingReadApiController;
use App\Http\Controllers\Api\Gpt\Ga4MarketingApiController;
use App\Http\Middleware\EnsureMarketingGptApiToken;

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

        //revisor
        Route::patch('/questions/{question}/review-publish', [QuestionReviewerApiController::class, 'reviewAndPublish']);
        Route::get('/taxonomy/review', [QuestionTaxonomyReviewApiController::class, 'reviewTaxonomy']);
        Route::patch('/questions/{question}/classification', [QuestionTaxonomyReviewApiController::class, 'updateQuestionClassification']);
        Route::patch('/topics/{topic}/move', [QuestionTaxonomyReviewApiController::class, 'moveTopic']);
        Route::post('/topics/{sourceTopic}/merge', [QuestionTaxonomyReviewApiController::class, 'mergeTopic']);
        Route::post('/subjects/{sourceSubject}/merge', [QuestionTaxonomyReviewApiController::class, 'mergeSubject']);

    });

    Route::prefix('gpt/marketing')
        ->middleware([EnsureMarketingGptApiToken::class])
        ->group(function () {
            Route::get('/health', [MarketingReadApiController::class, 'health']);
            Route::get('/funnel', [MarketingReadApiController::class, 'funnel']);
            Route::get('/acquisition', [MarketingReadApiController::class, 'acquisition']);
            Route::get('/courses', [MarketingReadApiController::class, 'courses']);
            Route::get('/revenue', [MarketingReadApiController::class, 'revenue']);

            Route::get('/ga4/health', [Ga4MarketingApiController::class, 'health']);
            Route::get('/ga4/overview', [Ga4MarketingApiController::class, 'overview']);
            Route::get('/ga4/acquisition', [Ga4MarketingApiController::class, 'acquisition']);
            Route::get('/ga4/landing-pages', [Ga4MarketingApiController::class, 'landingPages']);
            Route::get('/ga4/events', [Ga4MarketingApiController::class, 'events']);

    });