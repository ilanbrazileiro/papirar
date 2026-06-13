<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGptApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = trim((string) config('services.gpt_api.token'));

        $providedToken = trim((string) $request->bearerToken());

        if ($providedToken === '') {
            $providedToken = trim((string) $request->header('X-GPT-Token'));
        }

        if ($providedToken === '') {
            $providedToken = trim((string) $request->server('HTTP_X_GPT_TOKEN'));
        }

        if (
            $configuredToken === '' ||
            $providedToken === '' ||
            !hash_equals($configuredToken, $providedToken)
        ) {
            return response()->json([
                'message' => 'Token inválido ou ausente.',
            ], 401);
        }

        return $next($request);
    }
}