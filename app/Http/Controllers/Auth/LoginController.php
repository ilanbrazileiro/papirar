<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function index(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'Informe a senha.',
        ]);

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => 1,
        ], $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Login ou senha inválidos ou conta inativa.',
                ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();
        $sessionToken = Str::random(80);

        UserSession::updateOrCreate(
            ['user_id' => $user->id],
            [
                'session_token' => hash('sha256', $sessionToken),
                'ip_address' => (string) $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'last_activity_at' => now(),
            ]
        );

        $user->forceFill([
            'last_login_at' => now(),
            'force_logout_at' => null,
        ])->save();

        $request->session()->put('auth_session_token', $sessionToken);

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('student.dashboard');
    }
}
