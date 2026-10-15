<?php

namespace App\Http\Middleware;

use App\Models\Question;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveGptQuestion
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $value = $route?->parameter('question');

        if ($value !== null) {
            if ($value instanceof Question) {
                if (! $value->exists || ! $value->getKey()) {
                    abort(404, 'Questão não encontrada.');
                }
            } else {
                if (! ctype_digit((string) $value) || (int) $value < 1) {
                    abort(404, 'Questão não encontrada.');
                }
                $route->setParameter('question', Question::query()->findOrFail((int) $value));
            }
        }

        return $next($request);
    }
}
