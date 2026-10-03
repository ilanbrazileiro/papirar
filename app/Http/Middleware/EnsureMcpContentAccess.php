<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMcpContentAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');
        $allowedOrigins = [rtrim(config('app.url'), '/'), 'https://chatgpt.com'];
        if ($origin !== null && ! in_array($origin, $allowedOrigins, true)) {
            return response()->json(['message' => 'Origem MCP não permitida.'], 403);
        }

        $user = $request->user();

        if (! $user || ! $user->is_active || ! in_array($user->role, ['admin', 'content'], true)) {
            return response()->json(['message' => 'Acesso MCP restrito à administração e conteúdo.'], 403);
        }

        if (! $user->tokenCan('mcp:use')) {
            return response()->json(['message' => 'Token sem permissão MCP.'], 403);
        }

        return $next($request);
    }
}
