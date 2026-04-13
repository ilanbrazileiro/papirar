<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            UserSession::query()->where('user_id', $user->id)->delete();

            $user->forceFill([
                'force_logout_at' => now(),
            ])->save();
        }

        Auth::logout();

        $request->session()->forget('auth_session_token');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('auth.login')
            ->with('success', 'Sessão encerrada com sucesso.');
    }
}
