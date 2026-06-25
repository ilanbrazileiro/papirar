<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function index(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Informe seu melhor e-mail.',
            'email.email' => 'Informe um e-mail válido.',
        ]);

        $status = Password::sendResetLink([
            'email' => $data['email'],
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => match ($status) {
                        Password::INVALID_USER => 'Não encontramos uma conta cadastrada com este e-mail.',
                        Password::RESET_THROTTLED => 'Aguarde alguns minutos antes de tentar novamente.',
                        default => 'Não foi possível enviar o link de recuperação agora.',
                    },
                ]);
        }

        return back()->with('success', 'Enviamos as instruções de recuperação para o seu e-mail.');
    }
}
