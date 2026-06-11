<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminContentAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'content') {
            return $next($request);
        }

        if ($request->routeIs('admin.dashboard')) {
            return redirect()->route('admin.content.dashboard');
        }

        if ($request->routeIs([
            'admin.content.dashboard',
            'admin.account.edit',
            'admin.account.update',
            'admin.account.password.update',
            'admin.questions.*',
            'admin.editor-images.*',
            'admin.comments.*',
            'admin.corporations.*',
            'admin.subjects.*',
            'admin.topics.*',
            'admin.source-materials.*',
            'admin.exams.*',
            'admin.planned-exams.*',
            'admin.tickets.*',
        ])) {
            return $next($request);
        }

        abort(403, 'Você não tem permissão para acessar esta área.');
    }
}
