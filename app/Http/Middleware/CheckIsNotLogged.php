<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIsNotLogged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $route = Auth::user()?->isAdmin() ? 'admin.dashboard' : 'student.dashboard';

            return redirect()->route($route);
        }

        return $next($request);
    }
}
