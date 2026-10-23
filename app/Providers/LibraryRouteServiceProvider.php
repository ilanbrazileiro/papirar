<?php
namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LibraryRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('web')->group(base_path('routes/library.php'));
        Route::middleware(['web', 'auth', \App\Http\Middleware\EnsureSingleSession::class, \App\Http\Middleware\EnsureAdmin::class])
            ->get('/admin/dashboard/trafego', [\App\Http\Controllers\Admin\DashboardTrafficController::class, 'index'])
            ->name('admin.dashboard.traffic');
    }
}
