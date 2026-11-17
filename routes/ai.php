<?php

use App\Http\Middleware\EnsureMcpContentAccess;
use App\Mcp\Servers\PapirarServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

Route::middleware('throttle:20,1')->group(fn () => Mcp::oauthRoutes());

// Compatibilidade com aplicativos já conectados ao antigo endpoint do Extrator.
// Mantém URL, OAuth, autorização e limite de requisições sem duplicar o catálogo.
// Todas as operações agora são atendidas pelo mesmo servidor MCP unificado.
Mcp::web('/mcp/extractor', PapirarServer::class)
    ->middleware(['auth:api', EnsureMcpContentAccess::class, 'throttle:60,1']);

// Endpoint canônico do Papirar.
Mcp::web('/mcp/papirar', PapirarServer::class)
    ->middleware(['auth:api', EnsureMcpContentAccess::class, 'throttle:60,1']);
