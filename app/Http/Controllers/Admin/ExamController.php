<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $corporationId = $request->integer('corporation_id');
        $year = $request->integer('year');

        $exams = Exam::query()
            ->with('corporation')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('exam_type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($corporationId, fn ($q) => $q->where('corporation_id', $corporationId))
            ->when($year, fn ($q) => $q->where('year', $year))
            ->orderByDesc('year')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        $corporations = Corporation::query()->orderBy('name')->get();
        $years = Exam::query()->select('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('admin.exams.index', compact('exams', 'corporations', 'years', 'search', 'corporationId', 'year'));
    }

    public function create()
    {
        $exam = new Exam([
            'active' => true,
        ]);

        $corporations = Corporation::query()->orderBy('name')->get();

        return view('admin.exams.create', compact('exam', 'corporations'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        Exam::create($data);

        return redirect()
            ->route('admin.exams.index')
            ->with('success', 'Concurso cadastrado com sucesso.');
    }

    public function show(Exam $exam)
    {
        $exam->load('corporation');

        return view('admin.exams.show', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        $corporations = Corporation::query()->orderBy('name')->get();

        return view('admin.exams.edit', compact('exam', 'corporations'));
    }

    public function update(Request $request, Exam $exam)
    {
        $data = $this->validatedData($request, $exam->id);

        $exam->update($data);

        return redirect()
            ->route('admin.exams.edit', $exam)
            ->with('success', 'Concurso atualizado com sucesso.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return redirect()
            ->route('admin.exams.index')
            ->with('success', 'Concurso removido com sucesso.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'corporation_id' => ['required', 'integer', 'exists:corporations,id'],
            'title' => ['required', 'string', 'max:180'],
            'year' => ['required', 'integer', 'between:1900,2100'],
            'exam_type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);

        $exists = Exam::query()
            ->where('corporation_id', $validated['corporation_id'])
            ->where('title', $validated['title'])
            ->where('year', $validated['year'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            abort(back()->withErrors(['title' => 'Já existe um concurso com esse título, corporação e ano.'])->withInput());
        }

        $validated['active'] = (bool) ($validated['active'] ?? false);

        return $validated;
    }
}
