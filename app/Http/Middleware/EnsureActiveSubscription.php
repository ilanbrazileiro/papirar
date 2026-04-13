<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('auth.login');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        $activeSubscription = Subscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->latest('expires_at')
            ->first();

        if (! $activeSubscription) {
            return redirect()
                ->route('student.subscriptions.index')
                ->with('error', 'Sua assinatura está inativa ou expirada. Regularize o acesso para continuar estudando.');
        }

        return $next($request);
    }
}
