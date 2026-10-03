<?php

use App\Http\Middleware\EnsureMcpContentAccess;
use App\Mcp\Servers\ExtractorServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

Route::middleware('throttle:20,1')->group(fn () => Mcp::oauthRoutes());

Mcp::web('/mcp/extractor', ExtractorServer::class)
    ->middleware(['auth:api', EnsureMcpContentAccess::class, 'throttle:60,1']);
