<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSessionIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        $plainToken = (string) $request->session()->get('auth_session_token', '');

        if ($plainToken === '') {
            return $this->logoutAndRedirect($request, 'Sua sessão expirou. Faça login novamente.');
        }

        /** @var UserSession|null $currentSession */
        $currentSession = UserSession::query()->where('user_id', $user->id)->first();

        if (! $currentSession) {
            return $this->logoutAndRedirect($request, 'Sua sessão foi encerrada. Faça login novamente.');
        }

        if (! hash_equals((string) $currentSession->session_token, hash('sha256', $plainToken))) {
            return $this->logoutAndRedirect(
                $request,
                'Sua conta foi acessada em outro dispositivo. Esta sessão foi encerrada.'
            );
        }

        $currentSession->forceFill([
            'last_activity_at' => now(),
            'ip_address' => (string) $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ])->save();

        return $next($request);
    }

    private function logoutAndRedirect(Request $request, string $message): Response
    {
        Auth::logout();
        $request->session()->forget('auth_session_token');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('auth.login')
            ->withErrors(['email' => $message]);
    }
}
