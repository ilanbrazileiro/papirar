<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user()->loadMissing('address');

        return view('student.account.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'cpf' => ['nullable', 'string', 'max:14', Rule::unique('users', 'cpf')->ignore($user->id)],
            'ddd' => ['nullable', 'string', 'max:3'],
            'phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'address.cep' => ['nullable', 'string', 'max:10'],
            'address.street' => ['nullable', 'string', 'max:250'],
            'address.number' => ['nullable', 'string', 'max:20'],
            'address.complement' => ['nullable', 'string', 'max:250'],
            'address.neighborhood' => ['nullable', 'string', 'max:100'],
            'address.city' => ['nullable', 'string', 'max:100'],
            'address.state' => ['nullable', 'string', 'size:2'],
        ], [
            'name.required' => 'Informe seu nome completo.',
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está sendo usado por outra conta.',
            'cpf.unique' => 'Este CPF já está sendo usado por outra conta.',
            'birth_date.date' => 'Informe uma data de nascimento válida.',
            'address.state.size' => 'Informe a UF com 2 letras.',
        ]);

        $cpf = $this->onlyDigits($validated['cpf'] ?? null);

        if ($cpf && ! $this->isValidCpf($cpf)) {
            return back()
                ->withInput()
                ->withErrors(['cpf' => 'Informe um CPF válido.']);
        }

        $birthDate = null;

        if (!empty($validated['birth_date'])) {
            $birthDate = Carbon::parse($validated['birth_date'])->startOfDay();
            $minDate = now()->subYears(90)->startOfDay();
            $maxDate = now()->subYears(10)->endOfDay();

            if ($birthDate->lt($minDate) || $birthDate->gt($maxDate)) {
                return back()
                    ->withInput()
                    ->withErrors(['birth_date' => 'A idade permitida deve estar entre 10 e 90 anos.']);
            }
        }

        $ddd = $this->onlyDigits($validated['ddd'] ?? null);
        $phone = $this->onlyDigits($validated['phone'] ?? null);

        $addressData = $validated['address'] ?? [];
        $cep = $this->onlyDigits($addressData['cep'] ?? null);

        $user->fill([
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'cpf' => $cpf ?: null,
            'ddd' => $ddd ?: null,
            'phone' => $phone ?: null,
            'birth_date' => $birthDate ? $birthDate->format('Y-m-d') : null,
        ]);
        $user->save();

        $user->address()->updateOrCreate([], [
            'cep' => $cep ?: null,
            'street' => $this->nullableTrim($addressData['street'] ?? null),
            'number' => $this->nullableTrim($addressData['number'] ?? null),
            'complement' => $this->nullableTrim($addressData['complement'] ?? null),
            'neighborhood' => $this->nullableTrim($addressData['neighborhood'] ?? null),
            'city' => $this->nullableTrim($addressData['city'] ?? null),
            'state' => strtoupper($this->nullableTrim($addressData['state'] ?? '') ?? ''),
        ]);

        return redirect()->route('student.account.edit')->with('success', 'Dados atualizados com sucesso.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Informe sua senha atual.',
            'password.required' => 'Informe a nova senha.',
            'password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da nova senha não confere.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'A senha atual informada está incorreta.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('student.account.edit')->with('success', 'Senha alterada com sucesso.');
    }

    private function onlyDigits(?string $value): ?string
    {
        return $value === null ? null : preg_replace('/\D+/', '', $value);
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }

    private function isValidCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D+/', '', $cpf);

        if (strlen($cpf) !== 11) return false;
        if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ((int) $cpf[$t] !== $digit) return false;
        }

        return true;
    }
}
