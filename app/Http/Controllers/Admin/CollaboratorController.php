<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CollaboratorController extends Controller
{
    private const ROLES = [
        'admin' => 'Administrador',
        'content' => 'Conteúdo',
        'moderator' => 'Moderador',
        'finance' => 'Financeiro',
        'marketing' => 'Marketing',
    ];

    public function index(Request $request): View
    {
        $query = User::query()
            ->whereIn('role', array_keys(self::ROLES))
            ->orderByRaw("FIELD(role, 'admin', 'content', 'moderator', 'finance', 'marketing')")
            ->latest('id');

        if ($request->filled('role') && array_key_exists($request->role, self::ROLES)) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return view('admin.collaborators.index', [
            'collaborators' => $query->paginate(20)->withQueryString(),
            'roles' => self::ROLES,
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('admin.collaborators.create', [
            'collaborator' => new User(['role' => 'content', 'is_active' => true]),
            'roles' => self::ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $plainPassword = $data['password'] ?: '12345678';

        $collaborator = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($plainPassword),
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.collaborators.edit', $collaborator)
            ->with('success', 'Colaborador criado com sucesso. Senha inicial: ' . $plainPassword);
    }

    public function edit(User $collaborator): View
    {
        $this->ensureCollaborator($collaborator);

        return view('admin.collaborators.edit', [
            'collaborator' => $collaborator,
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $collaborator): RedirectResponse
    {
        $this->ensureCollaborator($collaborator);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($collaborator->id)],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($collaborator->id === auth()->id() && $data['role'] !== 'admin') {
            return back()
                ->withInput()
                ->with('error', 'Você não pode remover o próprio perfil de administrador.');
        }

        $collaborator->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($data['password'])) {
            $collaborator->password = Hash::make($data['password']);
            $collaborator->force_logout_at = now();
        }

        $collaborator->save();

        return redirect()
            ->route('admin.collaborators.edit', $collaborator)
            ->with('success', 'Colaborador atualizado com sucesso.');
    }

    public function destroy(User $collaborator): RedirectResponse
    {
        $this->ensureCollaborator($collaborator);

        if ($collaborator->id === auth()->id()) {
            return back()->with('error', 'Você não pode remover o próprio usuário.');
        }

        if ($collaborator->role === 'admin' && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'Não é possível remover o último administrador ativo.');
        }

        $collaborator->delete();

        return redirect()
            ->route('admin.collaborators.index')
            ->with('success', 'Colaborador removido com sucesso.');
    }

    private function ensureCollaborator(User $user): void
    {
        abort_unless(in_array($user->role, array_keys(self::ROLES), true), 404);
    }
}
