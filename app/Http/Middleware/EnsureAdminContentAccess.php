<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminContentAccess
{
    /**
     * Limita o perfil administrativo "content" apenas às áreas de conteúdo e suporte.
     * Demais perfis administrativos continuam com acesso normal.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ($user->role ?? null) !== 'content') {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName === 'admin.dashboard') {
            return redirect()->route('admin.content.dashboard');
        }

        $allowedExactRoutes = [
            'admin.content.dashboard',
            'admin.editor-images.upload',
            'admin.questions.ajax.exams',
            'admin.questions.ajax.topics',
            'admin.questions.ajax-source-materials',
            'admin.questions.import.template',
            'admin.questions.import.topics-csv',
            'admin.questions.import.source-materials-csv',
        ];

        if (in_array($routeName, $allowedExactRoutes, true)) {
            return $next($request);
        }

        $allowedPrefixes = [
            'admin.questions.',
            'admin.comments.',
            'admin.corporations.',
            'admin.subjects.',
            'admin.topics.',
            'admin.source-materials.',
            'admin.exams.',
            'admin.planned-exams.',
            'admin.tickets.',
        ];

        foreach ($allowedPrefixes as $prefix) {
            if ($routeName && str_starts_with($routeName, $prefix)) {
                return $next($request);
            }
        }

        abort(403, 'Seu perfil de colaborador permite acesso apenas às áreas de conteúdo e suporte.');
    }
}
