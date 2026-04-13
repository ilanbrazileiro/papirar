<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'address.cep' => ['nullable', 'string', 'max:10'],
            'address.street' => ['nullable', 'string', 'max:250'],
            'address.number' => ['nullable', 'string', 'max:20'],
            'address.complement' => ['nullable', 'string', 'max:250'],
            'address.neighborhood' => ['nullable', 'string', 'max:100'],
            'address.city' => ['nullable', 'string', 'max:100'],
            'address.state' => ['nullable', 'string', 'size:2'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'cpf' => $validated['cpf'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
        ]);
        $user->save();

        $addressData = $validated['address'] ?? [];
        $user->address()->updateOrCreate([], [
            'cep' => $addressData['cep'] ?? null,
            'street' => $addressData['street'] ?? null,
            'number' => $addressData['number'] ?? null,
            'complement' => $addressData['complement'] ?? null,
            'neighborhood' => $addressData['neighborhood'] ?? null,
            'city' => $addressData['city'] ?? null,
            'state' => $addressData['state'] ?? null,
        ]);

        return redirect()->route('student.account.edit')->with('success', 'Dados atualizados com sucesso.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'A senha atual informada está incorreta.',
            ]);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('student.account.edit')->with('success', 'Senha alterada com sucesso.');
    }
}
