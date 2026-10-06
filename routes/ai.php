<?php

use App\Http\Middleware\EnsureMcpContentAccess;
use App\Mcp\Servers\ExtractorServer;
use App\Mcp\Servers\PapirarServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

Route::middleware('throttle:20,1')->group(fn () => Mcp::oauthRoutes());

// Compatibilidade com o plugin Extrator atual durante a migração.
Mcp::web('/mcp/extractor', ExtractorServer::class)
    ->middleware(['auth:api', EnsureMcpContentAccess::class, 'throttle:60,1']);

// Endpoint unificado do Papirar MCP 2.0.
Mcp::web('/mcp/papirar', PapirarServer::class)
    ->middleware(['auth:api', EnsureMcpContentAccess::class, 'throttle:60,1']);
