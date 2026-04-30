<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlannedExamController extends Controller
{
    public function index(): View
    {
        $exams = Exam::query()
            ->with(['corporation', 'plannedSubjects'])
            ->whereIn('status', [Exam::STATUS_PLANNED, Exam::STATUS_PUBLISHED])
            ->latest('id')
            ->paginate(20);

        return view('admin.planned-exams.index', compact('exams'));
    }

    public function create(): View
    {
        $exam = new Exam([
            'status' => Exam::STATUS_PLANNED,
            'active' => true,
        ]);

        return view('admin.planned-exams.create', [
            'exam' => $exam,
            'corporations' => Corporation::query()->where('active', true)->orderBy('name')->get(),
            'subjects' => Subject::query()->where('active', true)->orderBy('name')->get(),
            'selectedSubjects' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);

        $exam = Exam::query()->create([
            'corporation_id' => $data['corporation_id'],
            'title' => $data['title'],
            'year' => $data['year'],
            'exam_type' => $data['exam_type'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'active' => $request->boolean('active', true),
        ]);

        $this->syncSubjects($exam, $data['subject_ids']);

        return redirect()
            ->route('admin.planned-exams.index')
            ->with('success', 'Concurso cadastrado com sucesso.');
    }

    public function edit(Exam $planned_exam): View
    {
        $exam = $planned_exam->load('plannedSubjects');

        return view('admin.planned-exams.edit', [
            'exam' => $exam,
            'corporations' => Corporation::query()->where('active', true)->orderBy('name')->get(),
            'subjects' => Subject::query()->where('active', true)->orderBy('name')->get(),
            'selectedSubjects' => $exam->plannedSubjects->pluck('id')->map(fn ($id) => (int) $id)->toArray(),
        ]);
    }

    public function update(Request $request, Exam $planned_exam): RedirectResponse
    {
        $data = $this->validatePayload($request);

        $planned_exam->update([
            'corporation_id' => $data['corporation_id'],
            'title' => $data['title'],
            'year' => $data['year'],
            'exam_type' => $data['exam_type'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'active' => $request->boolean('active', true),
        ]);

        $this->syncSubjects($planned_exam, $data['subject_ids']);

        return redirect()
            ->route('admin.planned-exams.index')
            ->with('success', 'Concurso atualizado com sucesso.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'corporation_id' => ['required', 'integer', 'exists:corporations,id'],
            'title' => ['required', 'string', 'max:180'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'exam_type' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:planned,published'],
            'description' => ['nullable', 'string'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ], [
            'corporation_id.required' => 'Selecione a corporação.',
            'title.required' => 'Informe o nome do concurso.',
            'year.required' => 'Informe o ano.',
            'exam_type.required' => 'Informe o tipo do concurso.',
            'status.required' => 'Informe se o concurso é previsto ou publicado.',
            'subject_ids.required' => 'Selecione pelo menos uma disciplina.',
            'subject_ids.min' => 'Selecione pelo menos uma disciplina.',
        ]);
    }

    private function syncSubjects(Exam $exam, array $subjectIds): void
    {
        $sync = [];

        foreach (array_values($subjectIds) as $index => $subjectId) {
            $sync[(int) $subjectId] = [
                'sort_order' => $index + 1,
                'is_active' => true,
            ];
        }

        $exam->plannedSubjects()->sync($sync);
    }
}
