<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Exam;
use App\Models\Question;
use App\Models\SavedFilter;
use App\Models\SimulatedExam;
use App\Models\SimulatedExamQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SimulatedController extends Controller
{
    public function index(): View
    {
        $savedFilter = SavedFilter::query()->firstOrCreate(
            ['user_id' => auth()->id()],
            ['mode' => 'exam', 'quantity' => 20]
        );

        $simulatedExams = SimulatedExam::query()
            ->with(['corporation', 'exam', 'subject'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        $corporations = Corporation::query()
            ->where('active', true)
            ->whereHas('exams', function ($query) {
                $query->where('active', true)
                    ->whereIn('status', [Exam::STATUS_PLANNED, Exam::STATUS_PUBLISHED]);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $exams = Exam::query()
            ->where('active', true)
            ->whereIn('status', [Exam::STATUS_PLANNED, Exam::STATUS_PUBLISHED])
            ->orderByRaw("CASE WHEN status = 'planned' THEN 0 ELSE 1 END")
            ->orderByDesc('year')
            ->orderBy('title')
            ->get(['id', 'corporation_id', 'title', 'year', 'status']);

        $examSubjects = DB::table('exam_subjects')
            ->join('subjects', 'subjects.id', '=', 'exam_subjects.subject_id')
            ->where('exam_subjects.is_active', true)
            ->where('subjects.active', true)
            ->orderBy('exam_subjects.sort_order')
            ->orderBy('subjects.name')
            ->get([
                'exam_subjects.exam_id',
                'subjects.id',
                'subjects.name',
                'subjects.scope',
            ]);

        $examsByCorporation = $exams
            ->groupBy('corporation_id')
            ->map(fn ($items) => $items->map(fn (Exam $exam) => [
                'id' => $exam->id,
                'title' => $exam->title,
                'year' => $exam->year,
                'status' => $exam->status,
                'status_label' => $exam->status === Exam::STATUS_PLANNED ? 'Previsto' : 'Publicado',
            ])->values())
            ->toArray();

        $subjectsByExam = $examSubjects
            ->groupBy('exam_id')
            ->map(fn ($items) => $items->map(fn ($subject) => [
                'id' => (int) $subject->id,
                'name' => $subject->name,
                'scope' => $subject->scope ?? 'general',
                'scope_label' => ($subject->scope ?? 'general') === 'corporation_specific'
                    ? 'Específica da corporação'
                    : 'Geral / reaproveitável',
            ])->values())
            ->toArray();

        return view('student.simulated.index', [
            'savedFilter' => $savedFilter,
            'simulatedExams' => $simulatedExams,
            'corporations' => $corporations,
            'examsByCorporation' => $examsByCorporation,
            'subjectsByExam' => $subjectsByExam,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'corporation_id' => ['required', 'integer', 'exists:corporations,id'],
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:120'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:300'],
        ], [
            'corporation_id.required' => 'Selecione a corporação.',
            'exam_id.required' => 'Selecione o concurso.',
            'subject_ids.required' => 'Selecione pelo menos uma disciplina.',
            'subject_ids.min' => 'Selecione pelo menos uma disciplina.',
            'duration_minutes.required' => 'Informe o tempo do simulado.',
            'duration_minutes.min' => 'O tempo mínimo do simulado é de 5 minutos.',
            'duration_minutes.max' => 'O tempo máximo do simulado é de 300 minutos.',
        ]);

        $exam = Exam::query()
            ->where('corporation_id', (int) $data['corporation_id'])
            ->where('active', true)
            ->whereIn('status', [Exam::STATUS_PLANNED, Exam::STATUS_PUBLISHED])
            ->findOrFail((int) $data['exam_id']);

        $allowedSubjectIds = DB::table('exam_subjects')
            ->where('exam_id', $exam->id)
            ->where('is_active', true)
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $selectedSubjectIds = array_values(array_intersect(
            array_map('intval', $data['subject_ids']),
            $allowedSubjectIds
        ));

        if (empty($selectedSubjectIds)) {
            return back()
                ->withErrors(['subject_ids' => 'As disciplinas selecionadas não pertencem ao concurso escolhido.'])
                ->withInput();
        }

        $topicRules = DB::table('exam_subjects')
            ->join('exam_subject_topics', 'exam_subject_topics.exam_subject_id', '=', 'exam_subjects.id')
            ->where('exam_subjects.exam_id', $exam->id)
            ->whereIn('exam_subjects.subject_id', $selectedSubjectIds)
            ->where('exam_subjects.is_active', true)
            ->where('exam_subject_topics.is_active', true)
            ->get([
                'exam_subjects.subject_id',
                'exam_subject_topics.topic_id',
            ]);

        $subjectIdsWithTopicRules = $topicRules
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $allowedTopicIds = $topicRules
            ->pluck('topic_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $questionsQuery = Question::query()
            ->select('questions.*')
            ->join('subjects', 'subjects.id', '=', 'questions.subject_id')
            ->where('questions.status', 'published')
            ->whereIn('questions.subject_id', $selectedSubjectIds)
            ->where(function ($query) use ($data) {
                $query->where(function ($general) {
                    $general->where('subjects.scope', 'general')
                        ->orWhereNull('subjects.scope');
                })->orWhere(function ($specific) use ($data) {
                    $specific->where('subjects.scope', 'corporation_specific')
                        ->where('questions.corporation_id', (int) $data['corporation_id']);
                });
            });

        if (!empty($allowedTopicIds) && !empty($subjectIdsWithTopicRules)) {
            $questionsQuery->where(function ($query) use ($subjectIdsWithTopicRules, $allowedTopicIds) {
                $query->whereNotIn('questions.subject_id', $subjectIdsWithTopicRules)
                    ->orWhereIn('questions.topic_id', $allowedTopicIds);
            });
        }

        $questions = $questionsQuery
            ->with(['subject', 'topic'])
            ->inRandomOrder()
            ->limit((int) $data['quantity'])
            ->get();

        if ($questions->isEmpty()) {
            return back()
                ->with('error', 'Nenhuma questão publicada foi encontrada para esse concurso e disciplinas.')
                ->withInput();
        }

        $title = trim((string) ($data['title'] ?? '')) ?: 'Simulado ' . now()->format('d/m/Y H:i');
        $startedAt = now();
        $endsAt = $startedAt->copy()->addMinutes((int) $data['duration_minutes']);

        $simulatedExam = DB::transaction(function () use ($data, $questions, $title, $startedAt, $endsAt, $exam, $selectedSubjectIds) {
            $simulatedExam = SimulatedExam::query()->create([
                'user_id' => auth()->id(),
                'title' => $title,
                'corporation_id' => (int) $data['corporation_id'],
                'exam_id' => $exam->id,
                'subject_id' => count($selectedSubjectIds) === 1 ? $selectedSubjectIds[0] : null,
                'topic_id' => null,
                'source_material_id' => null,
                'total_questions' => $questions->count(),
                'correct_answers' => 0,
                'accuracy' => 0,
                'duration_minutes' => (int) $data['duration_minutes'],
                'started_at' => $startedAt,
                'ends_at' => $endsAt,
            ]);

            foreach ($questions->values() as $index => $question) {
                SimulatedExamQuestion::query()->create([
                    'simulated_exam_id' => $simulatedExam->id,
                    'question_id' => $question->id,
                    'position' => $index + 1,
                ]);
            }

            SavedFilter::query()->updateOrCreate(
                ['user_id' => auth()->id()],
                [
                    'corporation_id' => (int) $data['corporation_id'],
                    'exam_id' => $exam->id,
                    'subject_id' => count($selectedSubjectIds) === 1 ? $selectedSubjectIds[0] : null,
                    'topic_id' => null,
                    'source_material_id' => null,
                    'difficulty' => null,
                    'source_type' => null,
                    'quantity' => (int) $data['quantity'],
                    'mode' => 'exam',
                ]
            );

            return $simulatedExam;
        });

        return redirect()->route('student.simulated.show', $simulatedExam);
    }

    public function show(Request $request, SimulatedExam $simulatedExam): View|RedirectResponse
    {
        abort_unless($simulatedExam->user_id === auth()->id(), 403);

        if ($this->finishIfExpired($simulatedExam)) {
            return redirect()
                ->route('student.simulated.result', $simulatedExam)
                ->with('error', 'O tempo do simulado acabou. As questões não respondidas ficaram em branco.');
        }

        if (!is_null($simulatedExam->finished_at)) {
            return redirect()->route('student.simulated.result', $simulatedExam);
        }

        $items = SimulatedExamQuestion::query()
            ->with(['question.corporation', 'question.exam', 'question.subject', 'question.topic', 'question.alternatives'])
            ->where('simulated_exam_id', $simulatedExam->id)
            ->orderBy('position')
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('student.simulated.index')->with('error', 'Simulado sem questões.');
        }

        $requestedPosition = max(1, (int) $request->get('question', 1));
        $currentItem = $items->firstWhere('position', $requestedPosition) ?? $items->first();
        $question = $currentItem->question;
        $answeredCount = $items->whereNotNull('answered_at')->count();
        $currentPosition = (int) $currentItem->position;
        $previousItem = $items->firstWhere('position', $currentPosition - 1);
        $nextItem = $items->firstWhere('position', $currentPosition + 1);
        $remainingSeconds = max(0, now()->diffInSeconds($simulatedExam->ends_at, false));

        return view('student.simulated.show', [
            'simulatedExam' => $simulatedExam,
            'items' => $items,
            'currentItem' => $currentItem,
            'question' => $question,
            'currentPosition' => $currentPosition,
            'totalQuestions' => $items->count(),
            'answeredCount' => $answeredCount,
            'previousItem' => $previousItem,
            'nextItem' => $nextItem,
            'remainingSeconds' => $remainingSeconds,
        ]);
    }

    public function saveAnswer(Request $request, SimulatedExam $simulatedExam): RedirectResponse
    {
        abort_unless($simulatedExam->user_id === auth()->id(), 403);

        if (!is_null($simulatedExam->finished_at) || $this->finishIfExpired($simulatedExam)) {
            return redirect()
                ->route('student.simulated.result', $simulatedExam)
                ->with('error', 'Este simulado já foi finalizado.');
        }

        $data = $request->validate([
            'simulated_exam_question_id' => ['required', 'integer', 'exists:simulated_exam_questions,id'],
            'selected_alternative_id' => ['required', 'integer', 'exists:alternatives,id'],
            'next_position' => ['nullable', 'integer', 'min:1'],
        ]);

        $item = SimulatedExamQuestion::query()
            ->with('question.alternatives')
            ->where('simulated_exam_id', $simulatedExam->id)
            ->findOrFail($data['simulated_exam_question_id']);

        $selectedAlternative = $item->question->alternatives->firstWhere('id', (int) $data['selected_alternative_id']);

        if (!$selectedAlternative) {
            return back()->with('error', 'Alternativa inválida para esta questão.');
        }

        $item->update([
            'selected_alternative_id' => $selectedAlternative->id,
            'is_correct' => (bool) $selectedAlternative->is_correct,
            'answered_at' => now(),
        ]);

        $targetPosition = !empty($data['next_position']) ? (int) $data['next_position'] : ($item->position + 1);

        return redirect()->route('student.simulated.show', [
            'simulatedExam' => $simulatedExam->id,
            'question' => $targetPosition,
        ])->with('success', 'Resposta salva.');
    }

    public function finish(SimulatedExam $simulatedExam): RedirectResponse
    {
        abort_unless($simulatedExam->user_id === auth()->id(), 403);

        $this->finishExam($simulatedExam);

        return redirect()->route('student.simulated.result', $simulatedExam);
    }

    public function result(SimulatedExam $simulatedExam): View
    {
        abort_unless($simulatedExam->user_id === auth()->id(), 403);

        $this->finishIfExpired($simulatedExam);

        $items = SimulatedExamQuestion::query()
            ->with(['question.subject', 'question.topic', 'question.exam', 'question.alternatives', 'selectedAlternative'])
            ->where('simulated_exam_id', $simulatedExam->id)
            ->orderBy('position')
            ->get();

        $answeredCount = $items->whereNotNull('answered_at')->count();
        $blankCount = max(0, $items->count() - $answeredCount);

        $subjectStats = $items
            ->groupBy(fn ($item) => optional($item->question->subject)->name ?: 'Sem disciplina')
            ->map(function ($group) {
                $total = $group->count();
                $correct = $group->where('is_correct', true)->count();
                $answered = $group->whereNotNull('answered_at')->count();

                return [
                    'total' => $total,
                    'answered' => $answered,
                    'blank' => max(0, $total - $answered),
                    'correct' => $correct,
                    'accuracy' => $total > 0 ? round(($correct / $total) * 100, 2) : 0,
                ];
            });

        return view('student.simulated.result', [
            'simulatedExam' => $simulatedExam->fresh(['corporation', 'exam', 'subject']),
            'items' => $items,
            'answeredCount' => $answeredCount,
            'blankCount' => $blankCount,
            'subjectStats' => $subjectStats,
        ]);
    }

    private function finishIfExpired(SimulatedExam $simulatedExam): bool
    {
        if (!is_null($simulatedExam->finished_at)) {
            return false;
        }

        if ($simulatedExam->ends_at && now()->greaterThanOrEqualTo($simulatedExam->ends_at)) {
            $this->finishExam($simulatedExam, true);
            return true;
        }

        return false;
    }

    private function finishExam(SimulatedExam $simulatedExam, bool $expired = false): void
    {
        if (!is_null($simulatedExam->finished_at)) {
            return;
        }

        $items = SimulatedExamQuestion::query()
            ->where('simulated_exam_id', $simulatedExam->id)
            ->get();

        $total = $items->count();
        $correct = $items->where('is_correct', true)->count();
        $accuracy = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

        $finishTime = $expired && $simulatedExam->ends_at ? $simulatedExam->ends_at : now();

        $simulatedExam->update([
            'correct_answers' => $correct,
            'accuracy' => $accuracy,
            'finished_at' => $finishTime,
        ]);
    }
}
