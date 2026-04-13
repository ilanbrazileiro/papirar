<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CorporationController extends Controller
{
     public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $corporations = Corporation::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.corporations.index', compact('corporations', 'search'));
    }

    public function create()
    {
        $corporation = new Corporation([
            'active' => true,
        ]);

        return view('admin.corporations.create', compact('corporation'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        Corporation::create($data);

        return redirect()
            ->route('admin.corporations.index')
            ->with('success', 'Corporação cadastrada com sucesso.');
    }

    public function show(Corporation $corporation)
    {
        return view('admin.corporations.show', compact('corporation'));
    }

    public function edit(Corporation $corporation)
    {
        return view('admin.corporations.edit', compact('corporation'));
    }

    public function update(Request $request, Corporation $corporation): RedirectResponse
    {
        $data = $this->validatedData($request, $corporation->id);

        $corporation->update($data);

        return redirect()
            ->route('admin.corporations.edit', $corporation)
            ->with('success', 'Corporação atualizada com sucesso.');
    }

    public function destroy(Corporation $corporation): RedirectResponse
    {
        $corporation->delete();

        return redirect()
            ->route('admin.corporations.index')
            ->with('success', 'Corporação removida com sucesso.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('corporations', 'name')->ignore($ignoreId)],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('corporations', 'slug')->ignore($ignoreId)],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);

        $slug = trim((string) ($validated['slug'] ?? ''));
        if ($slug === '') {
            $slug = Str::slug($validated['name']);
        }

        $validated['slug'] = $slug;
        $validated['active'] = (bool) ($validated['active'] ?? false);

        return $validated;
    }
}
