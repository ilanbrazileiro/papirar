<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'seguranca' => ['nullable', 'max:0'],
        ], [
            'name.required' => 'Informe seu nome.',
            'name.min' => 'Informe seu nome completo.',
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'Informe uma senha.',
            'password.confirmed' => 'As senhas não conferem.',
            'seguranca.max' => 'Cadastro inválido.',
        ]);

        $user = User::query()->create([
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'role' => 'student',
            'is_active' => 1,
            'email_verified_at' => null,
        ]);

        $user->notify(new VerifyEmailNotification());

        Auth::login($user);
        $request->session()->regenerate();

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

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Cadastro realizado com sucesso. Enviamos um e-mail para confirmação da sua conta.');
    }
}