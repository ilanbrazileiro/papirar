<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGptApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = (string) config('services.gpt_api.token');

        if ($configuredToken === '') {
            return response()->json([
                'message' => 'API GPT não configurada. Defina GPT_API_TOKEN no .env.',
            ], 503);
        }

        $providedToken = $request->bearerToken()
            ?: $request->header('X-GPT-API-TOKEN')
            ?: (string) $request->query('api_token', '');

        if (! hash_equals($configuredToken, (string) $providedToken)) {
            return response()->json([
                'message' => 'Token inválido ou ausente.',
            ], 401);
        }

        return $next($request);
    }
}
