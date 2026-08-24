<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMarketingGptApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        $configuredToken = trim((string) config('services.marketing_gpt_api.token'));
        $providedToken = trim((string) $request->bearerToken());

        if ($providedToken === '') {
            $providedToken = trim((string) $request->header('X-Marketing-GPT-Token'));
        }

        if (
            $configuredToken === '' ||
            $providedToken === '' ||
            ! hash_equals($configuredToken, $providedToken)
        ) {
            return response()->json([
                'message' => 'Token de Marketing GPT inválido ou ausente.',
            ], 401);
        }

        return $next($request);
    }
}
