<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TopicController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $subjectId = $request->integer('subject_id');

        $topics = Topic::query()
            ->with('subject')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->orderBy('subject_id')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $subjects = Subject::query()->orderBy('name')->get();

        return view('admin.topics.index', compact('topics', 'subjects', 'search', 'subjectId'));
    }

    public function create()
    {
        $topic = new Topic([
            'active' => true,
        ]);

        $subjects = Subject::query()->orderBy('name')->get();

        return view('admin.topics.create', compact('topic', 'subjects'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        Topic::create($data);

        return redirect()
            ->route('admin.topics.index')
            ->with('success', 'Assunto criado com sucesso.');
    }

    public function show(Topic $topic)
    {
        $topic->load('subject');

        return view('admin.topics.show', compact('topic'));
    }

    public function edit(Topic $topic)
    {
        $subjects = Subject::query()->orderBy('name')->get();

        return view('admin.topics.edit', compact('topic', 'subjects'));
    }

    public function update(Request $request, Topic $topic)
    {
        $data = $this->validatedData($request, $topic->id);

        $topic->update($data);

        return redirect()
            ->route('admin.topics.edit', $topic)
            ->with('success', 'Assunto atualizado com sucesso.');
    }

    public function destroy(Topic $topic)
    {
        $topic->delete();

        return redirect()
            ->route('admin.topics.index')
            ->with('success', 'Assunto removido com sucesso.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('topics', 'name')
                    ->where(fn ($q) => $q->where('subject_id', $request->input('subject_id')))
                    ->ignore($ignoreId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:160',
                Rule::unique('topics', 'slug')
                    ->where(fn ($q) => $q->where('subject_id', $request->input('subject_id')))
                    ->ignore($ignoreId),
            ],
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
