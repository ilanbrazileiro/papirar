<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
class PublicSimulationRouteServiceProvider extends ServiceProvider {
 public function boot():void {Route::middleware('web')->group(base_path('routes/public_simulations.php'));}
}
