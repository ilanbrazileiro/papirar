<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class VerifyEmailController extends Controller
{
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        /** @var User|null $user */
        $user = User::query()->find($id);

        if (! $user) {
            return redirect()->route('auth.login')->with('error', 'Link de verificação inválido.');
        }

        if (! URL::hasValidSignature($request)) {
            return redirect()->route('auth.login')->with('error', 'O link de verificação expirou ou é inválido.');
        }

        if (! hash_equals(sha1($user->email), $hash)) {
            throw new AuthorizationException();
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'E-mail confirmado com sucesso.');
    }

    public function resend(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return back()->with('success', 'Seu e-mail já foi confirmado.');
        }

        $user->notify(new \App\Notifications\VerifyEmailNotification());

        return back()->with('success', 'Enviamos um novo e-mail de confirmação.');
    }
}
