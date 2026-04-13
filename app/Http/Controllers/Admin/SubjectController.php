<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $subjects = Subject::query()
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

        return view('admin.subjects.index', compact('subjects', 'search'));
    }

    public function create()
    {
        $subject = new Subject([
            'active' => true,
        ]);

        return view('admin.subjects.create', compact('subject'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        Subject::create($data);

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Disciplina criada com sucesso.');
    }

    public function show(Subject $subject)
    {
        return view('admin.subjects.show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $this->validatedData($request, $subject->id);

        $subject->update($data);

        return redirect()
            ->route('admin.subjects.edit', $subject)
            ->with('success', 'Disciplina atualizada com sucesso.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Disciplina removida com sucesso.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('subjects', 'name')->ignore($ignoreId)],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('subjects', 'slug')->ignore($ignoreId)],
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
