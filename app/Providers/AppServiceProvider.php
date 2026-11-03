<?php

namespace App\Providers;

use App\Services\Questions\EducationAwareQuestionCsvImportService;
use App\Services\Questions\QuestionCsvImportService;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Injeta a extensão sem substituir o importador original e seus fluxos.
        $this->app->bind(QuestionCsvImportService::class, EducationAwareQuestionCsvImportService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::authorizationView(fn (array $parameters) => view('mcp.authorize', $parameters));
        Passport::tokensExpireIn(now()->addHour());
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}
