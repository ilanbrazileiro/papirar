<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
class TopicVideoRouteServiceProvider extends ServiceProvider {
 public function boot():void {Route::middleware('web')->group(base_path('routes/topic_videos.php'));}
}
