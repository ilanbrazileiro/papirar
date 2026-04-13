<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('auth.login');
        }

        $plainToken = (string) $request->session()->get('auth_session_token', '');

        if ($plainToken === '') {
            $this->logoutAndInvalidate($request);

            return redirect()
                ->route('auth.login')
                ->withErrors([
                    'email' => 'Sua sessão expirou. Faça login novamente.',
                ]);
        }

        /** @var UserSession|null $currentSession */
        $currentSession = UserSession::query()->where('user_id', $user->id)->first();

        if (! $currentSession) {
            $this->logoutAndInvalidate($request);

            return redirect()
                ->route('auth.login')
                ->withErrors([
                    'email' => 'Sua sessão foi encerrada. Faça login novamente.',
                ]);
        }

        if (! hash_equals((string) $currentSession->session_token, hash('sha256', $plainToken))) {
            $this->logoutAndInvalidate($request);

            return redirect()
                ->route('auth.login')
                ->withErrors([
                    'email' => 'Sua conta foi acessada em outro dispositivo. Esta sessão foi encerrada.',
                ]);
        }

        $currentSession->forceFill([
            'last_activity_at' => now(),
            'ip_address' => (string) $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ])->save();

        return $next($request);
    }

    private function logoutAndInvalidate(Request $request): void
    {
        Auth::logout();
        $request->session()->forget('auth_session_token');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
