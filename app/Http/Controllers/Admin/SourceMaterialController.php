<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\SourceMaterial;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SourceMaterialController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $corporationId = $request->get('corporation_id');
        $subjectId = $request->get('subject_id');
        $active = $request->get('active');

        $materials = SourceMaterial::query()
            ->with(['corporation', 'subject'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('reference_code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($corporationId !== null && $corporationId !== '', fn ($query) => $query->where('corporation_id', $corporationId))
            ->when($subjectId !== null && $subjectId !== '', fn ($query) => $query->where('subject_id', $subjectId))
            ->when($active !== null && $active !== '', fn ($query) => $query->where('active', (bool) $active))
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        $corporations = Corporation::query()->orderBy('name')->get();
        $subjects = Subject::query()->orderBy('name')->get();

        return view('admin.source_materials.index', compact(
            'materials',
            'corporations',
            'subjects',
            'search',
            'corporationId',
            'subjectId',
            'active'
        ));
    }

    public function create()
    {
        $material = new SourceMaterial([
            'active' => true,
            'material_type' => 'manual',
        ]);

        $corporations = Corporation::query()->orderBy('name')->get();
        $subjects = Subject::query()->orderBy('name')->get();

        return view('admin.source_materials.create', compact('material', 'corporations', 'subjects'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        SourceMaterial::create($data);

        return redirect()
            ->route('admin.source-materials.index')
            ->with('success', 'Fonte/material criado com sucesso.');
    }

    public function show(SourceMaterial $sourceMaterial)
    {
        $sourceMaterial->load(['corporation', 'subject']);
        return view('admin.source_materials.show', ['material' => $sourceMaterial]);
    }

    public function edit(SourceMaterial $sourceMaterial)
    {
        $corporations = Corporation::query()->orderBy('name')->get();
        $subjects = Subject::query()->orderBy('name')->get();

        return view('admin.source_materials.edit', [
            'material' => $sourceMaterial,
            'corporations' => $corporations,
            'subjects' => $subjects,
        ]);
    }

    public function update(Request $request, SourceMaterial $sourceMaterial)
    {
        $data = $this->validatedData($request, $sourceMaterial->id);
        $sourceMaterial->update($data);

        return redirect()
            ->route('admin.source-materials.edit', $sourceMaterial)
            ->with('success', 'Fonte/material atualizado com sucesso.');
    }

    public function destroy(SourceMaterial $sourceMaterial)
    {
        if ($sourceMaterial->questions()->exists() || $sourceMaterial->examSubjectLinks()->exists()) {
            return redirect()
                ->route('admin.source-materials.index')
                ->with('error', 'Esta fonte/material já está vinculada a questões ou concursos. Desative em vez de excluir.');
        }

        $sourceMaterial->delete();

        return redirect()
            ->route('admin.source-materials.index')
            ->with('success', 'Fonte/material removido com sucesso.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'corporation_id' => ['nullable', 'integer', 'exists:corporations,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('source_materials', 'slug')->ignore($ignoreId)],
            'description' => ['nullable', 'string'],
            'material_type' => ['required', 'string', 'max:50'],
            'year' => ['nullable', 'integer', 'min:1800', 'max:' . ((int) date('Y') + 5)],
            'reference_code' => ['nullable', 'string', 'max:100'],
            'url' => ['nullable', 'string', 'max:500'],
            'active' => ['nullable', 'boolean'],
        ]);

        $slug = trim((string) ($validated['slug'] ?? ''));
        if ($slug === '') {
            $slug = Str::slug($validated['title']);
        }

        $validated['slug'] = $slug;
        $validated['active'] = (bool) ($validated['active'] ?? false);
        $validated['corporation_id'] = $validated['corporation_id'] ?? null;
        $validated['year'] = $validated['year'] ?? null;

        return $validated;
    }
}
