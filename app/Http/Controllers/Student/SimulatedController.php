<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Question;
use App\Models\SavedFilter;
use App\Models\SimulatedExam;
use App\Models\SimulatedExamQuestion;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SimulatedController extends Controller
{
    public function index()
    {
        $savedFilter = SavedFilter::query()->firstOrCreate(
            ['user_id' => auth()->id()],
            ['mode' => 'exam', 'quantity' => 20]
        );

        $simulatedExams = SimulatedExam::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('student.simulated.index', [
            'savedFilter' => $savedFilter,
            'simulatedExams' => $simulatedExams,
            'corporations' => Corporation::query()->where('active', true)->orderBy('name')->get(),
            'subjects' => Subject::query()->where('active', true)->orderBy('name')->get(),
            'topics' => Topic::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'corporation_id' => ['nullable', 'integer', 'exists:corporations,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'difficulty' => ['nullable', 'in:easy,medium,hard'],
            'source_type' => ['nullable', 'in:official_exam,authored,adapted'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $questions = Question::query()
            ->where('status', 'published')
            ->when(!empty($data['corporation_id']), fn ($q) => $q->where('corporation_id', $data['corporation_id']))
            ->when(!empty($data['subject_id']), fn ($q) => $q->where('subject_id', $data['subject_id']))
            ->when(!empty($data['topic_id']), fn ($q) => $q->where('topic_id', $data['topic_id']))
            ->when(!empty($data['difficulty']), fn ($q) => $q->where('difficulty', $data['difficulty']))
            ->when(!empty($data['source_type']), fn ($q) => $q->where('source_type', $data['source_type']))
            ->inRandomOrder()
            ->limit($data['quantity'])
            ->get();

        if ($questions->isEmpty()) {
            return back()->with('error', 'Nenhuma questão encontrada com os filtros informados.');
        }

        $title = trim((string) ($data['title'] ?? '')) ?: 'Simulado ' . now()->format('d/m/Y H:i');

        $simulatedExam = DB::transaction(function () use ($data, $questions, $title) {
            $simulatedExam = SimulatedExam::query()->create([
                'user_id' => auth()->id(),
                'title' => $title,
                'total_questions' => $questions->count(),
                'correct_answers' => 0,
                'accuracy' => 0,
                'started_at' => now(),
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
                    'corporation_id' => $data['corporation_id'] ?? null,
                    'subject_id' => $data['subject_id'] ?? null,
                    'topic_id' => $data['topic_id'] ?? null,
                    'difficulty' => $data['difficulty'] ?? null,
                    'source_type' => $data['source_type'] ?? null,
                    'quantity' => $data['quantity'],
                    'mode' => 'exam',
                ]
            );

            return $simulatedExam;
        });

        return redirect()->route('student.simulated.show', $simulatedExam);
    }

    public function show(Request $request, SimulatedExam $simulatedExam)
    {
        abort_unless($simulatedExam->user_id === auth()->id(), 403);

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
        ]);
    }

    public function saveAnswer(Request $request, SimulatedExam $simulatedExam): RedirectResponse
    {
        abort_unless($simulatedExam->user_id === auth()->id(), 403);
        abort_if(!is_null($simulatedExam->finished_at), 422, 'Este simulado já foi finalizado.');

        $data = $request->validate([
            'simulated_exam_question_id' => ['required', 'integer', 'exists:simulated_exam_questions,id'],
            'selected_alternative_id' => ['required', 'integer', 'exists:alternatives,id'],
            'next_position' => ['nullable', 'integer', 'min:1'],
        ]);

        $item = SimulatedExamQuestion::query()
            ->with('question.alternatives')
            ->where('simulated_exam_id', $simulatedExam->id)
            ->findOrFail($data['simulated_exam_question_id']);

        $selectedAlternative = $item->question->alternatives->firstWhere('id', $data['selected_alternative_id']);
        abort_if(!$selectedAlternative, 422, 'Alternativa inválida para esta questão.');

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

        $items = SimulatedExamQuestion::query()
            ->where('simulated_exam_id', $simulatedExam->id)
            ->get();

        $total = $items->count();
        $correct = $items->where('is_correct', true)->count();
        $accuracy = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

        $simulatedExam->update([
            'correct_answers' => $correct,
            'accuracy' => $accuracy,
            'finished_at' => now(),
        ]);

        return redirect()->route('student.simulated.result', $simulatedExam);
    }

    public function result(SimulatedExam $simulatedExam)
    {
        abort_unless($simulatedExam->user_id === auth()->id(), 403);

        $items = SimulatedExamQuestion::query()
            ->with(['question.subject', 'question.topic', 'question.exam'])
            ->where('simulated_exam_id', $simulatedExam->id)
            ->orderBy('position')
            ->get();

        return view('student.simulated.result', [
            'simulatedExam' => $simulatedExam,
            'items' => $items,
        ]);
    }
}
