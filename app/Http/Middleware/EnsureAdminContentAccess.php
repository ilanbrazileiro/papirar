<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminContentAccess
{
    /**
     * Limita o colaborador com role "content" apenas às áreas de conteúdo do admin.
     *
     * Regras:
     * - admin, moderator, finance e marketing continuam seguindo a regra atual do sistema;
     * - content acessa apenas questões, corporações, disciplinas, tópicos, bibliografias/fontes,
     *   concursos e moderação de comentários;
     * - content não acessa colaboradores, clientes, planos, assinaturas, financeiro e tickets admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('auth.login');
        }

        if ($user->role !== 'content') {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName === 'admin.dashboard') {
            return redirect()->route('admin.questions.index');
        }

        $allowedRoutePatterns = [
            'admin.questions.*',
            'admin.editor-images.upload',
            'admin.comments.*',
            'admin.corporations.*',
            'admin.subjects.*',
            'admin.topics.*',
            'admin.source-materials.*',
            'admin.exams.*',
            'admin.planned-exams.*',
        ];

        foreach ($allowedRoutePatterns as $pattern) {
            if ($routeName && Str::is($pattern, $routeName)) {
                return $next($request);
            }
        }

        abort(403, 'Seu perfil de colaborador permite acesso apenas às áreas de conteúdo.');
    }
}
