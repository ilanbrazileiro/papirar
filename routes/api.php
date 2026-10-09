<?php

use App\Http\Controllers\Api\Gpt\CatalogCourseApiController;
use App\Http\Controllers\Api\Gpt\CoursePreparationApiController;
use App\Http\Controllers\Api\Gpt\Ga4MarketingApiController;
use App\Http\Controllers\Api\Gpt\MarketingEngagementApiController;
use App\Http\Controllers\Api\Gpt\MarketingReadApiController;
use App\Http\Controllers\Api\Gpt\QuestionBatchWriteApiController;
use App\Http\Controllers\Api\Gpt\QuestionReviewApiController;
use App\Http\Controllers\Api\Gpt\QuestionReviewerApiController;
use App\Http\Controllers\Api\Gpt\QuestionTaxonomyReviewApiController;
use App\Http\Controllers\Api\Gpt\QuestionWriteApiController;
use App\Http\Controllers\Api\Gpt\TaxonomyWriteApiController;
use App\Http\Controllers\Billing\MercadoPagoWebhookController;
use App\Http\Middleware\EnsureGptApiToken;
use App\Http\Middleware\EnsureMarketingGptApiToken;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/mercado-pago', [MercadoPagoWebhookController::class, 'handle']);

Route::prefix('gpt')->middleware([EnsureGptApiToken::class])->group(function () {
    Route::get('/health', [QuestionReviewApiController::class, 'health']);
    Route::get('/corporations', [QuestionReviewApiController::class, 'corporations']);
    Route::get('/exam-boards', [QuestionReviewApiController::class, 'examBoards']);
    Route::get('/exams', [QuestionReviewApiController::class, 'exams']);
    Route::get('/subjects', [QuestionReviewApiController::class, 'subjects']);
    Route::get('/topics', [QuestionReviewApiController::class, 'topics']);
    Route::get('/source-materials', [QuestionReviewApiController::class, 'sourceMaterials']);

    Route::get('/questions', [QuestionReviewApiController::class, 'questions']);
    Route::post('/questions/duplicate-check', [QuestionReviewApiController::class, 'duplicateCheck']);
    Route::post('/questions', [QuestionWriteApiController::class, 'store']);
    Route::post('/questions/batch', [QuestionBatchWriteApiController::class, 'store']);
    Route::post('/taxonomy/check', [TaxonomyWriteApiController::class, 'check']);
    Route::post('/subjects', [TaxonomyWriteApiController::class, 'storeSubject']);
    Route::post('/topics', [TaxonomyWriteApiController::class, 'storeTopic']);
    Route::get('/questions/{question}', [QuestionReviewApiController::class, 'question']);
    Route::get('/questions/{question}/comments', [QuestionReviewApiController::class, 'comments']);
    Route::get('/questions/{question}/stats', [QuestionReviewApiController::class, 'stats']);

    Route::patch('/questions/{question}/review-content', [QuestionReviewerApiController::class, 'updateContent']);
    Route::patch('/questions/{question}/review-finalize', [QuestionReviewerApiController::class, 'finalizeReview']);
    Route::patch('/questions/{question}/archive', [QuestionReviewerApiController::class, 'archive']);
    Route::patch('/questions/{question}/review-publish', [QuestionReviewerApiController::class, 'reviewAndPublish']);

    Route::get('/taxonomy/review', [QuestionTaxonomyReviewApiController::class, 'reviewTaxonomy']);
    Route::patch('/questions/{question}/classification', [QuestionTaxonomyReviewApiController::class, 'updateQuestionClassification']);
    Route::patch('/topics/{topic}/move', [QuestionTaxonomyReviewApiController::class, 'moveTopic']);
    Route::post('/topics/{sourceTopic}/merge', [QuestionTaxonomyReviewApiController::class, 'mergeTopic']);
    Route::post('/subjects/{sourceSubject}/merge', [QuestionTaxonomyReviewApiController::class, 'mergeSubject']);

    Route::post('/catalog/corporations', [CatalogCourseApiController::class, 'storeCorporation']);
    Route::patch('/catalog/corporations/{corporation}', [CatalogCourseApiController::class, 'updateCorporation']);
    Route::post('/catalog/exam-boards', [CatalogCourseApiController::class, 'storeExamBoard']);
    Route::patch('/catalog/exam-boards/{examBoard}', [CatalogCourseApiController::class, 'updateExamBoard']);
    Route::post('/catalog/source-materials', [CatalogCourseApiController::class, 'storeSourceMaterial']);
    Route::patch('/catalog/source-materials/{sourceMaterial}', [CatalogCourseApiController::class, 'updateSourceMaterial']);
    Route::post('/catalog/exams', [CatalogCourseApiController::class, 'storeExam']);
    Route::patch('/catalog/exams/{exam}', [CatalogCourseApiController::class, 'updateExam']);
    Route::put('/catalog/exams/{exam}/scope', [CatalogCourseApiController::class, 'replaceExamScopeApi']);

    Route::get('/courses', [CatalogCourseApiController::class, 'courses']);
    Route::post('/courses', [CoursePreparationApiController::class, 'store']);
    Route::get('/courses/{course}', [CatalogCourseApiController::class, 'course']);
    Route::get('/courses/{course}/coverage', [CatalogCourseApiController::class, 'courseCoverage']);
    Route::patch('/courses/{course}/metadata', [CoursePreparationApiController::class, 'updateMetadata']);
    Route::put('/courses/{course}/scope', [CoursePreparationApiController::class, 'replaceScope']);
    Route::put('/courses/{course}/bundle', [CoursePreparationApiController::class, 'replaceBundle']);
    Route::patch('/courses/{course}/pricing', [CoursePreparationApiController::class, 'updatePricing']);
    Route::patch('/courses/{course}/landing', [CoursePreparationApiController::class, 'updateLanding']);
    Route::patch('/courses/{course}/publication', [CoursePreparationApiController::class, 'setPublication']);
    Route::delete('/courses/{course}', [CoursePreparationApiController::class, 'destroy']);
});

Route::prefix('gpt/marketing')->middleware([EnsureMarketingGptApiToken::class])->group(function () {
    Route::get('/health', [MarketingReadApiController::class, 'health']);
    Route::get('/funnel', [MarketingReadApiController::class, 'funnel']);
    Route::get('/acquisition', [MarketingReadApiController::class, 'acquisition']);
    Route::get('/courses', [MarketingReadApiController::class, 'courses']);
    Route::get('/revenue', [MarketingReadApiController::class, 'revenue']);
    Route::get('/engagement', [MarketingEngagementApiController::class, 'cohort']);
    Route::get('/ga4/health', [Ga4MarketingApiController::class, 'health']);
    Route::get('/ga4/overview', [Ga4MarketingApiController::class, 'overview']);
    Route::get('/ga4/acquisition', [Ga4MarketingApiController::class, 'acquisition']);
    Route::get('/ga4/landing-pages', [Ga4MarketingApiController::class, 'landingPages']);
    Route::get('/ga4/events', [Ga4MarketingApiController::class, 'events']);
});
