<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Exam;
use App\Models\Question;
use App\Models\StudySession;
use App\Models\StudySessionQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamStudyController extends Controller
{
    public function index(): View
    {
        return $this->create();
    }

    public function create(): View
    {
        $corporations = Corporation::query()
            ->where('active', true)
            ->whereHas('exams', function ($query) {
                $query->where('active', true);
            })
            ->orderBy('name')
            ->get();

        return view('student.study.exam-filter', compact('corporations'));
    }

    public function examsByCorporation(int $corporationId): JsonResponse
    {
        $exams = Exam::query()
            ->where('corporation_id', $corporationId)
            ->where('active', true)
            ->whereIn('status', [Exam::STATUS_PLANNED, Exam::STATUS_PUBLISHED])
            ->orderByRaw("CASE WHEN status = 'planned' THEN 0 ELSE 1 END")
            ->orderByDesc('year')
            ->orderBy('title')
            ->get(['id', 'title', 'year', 'status']);

        return response()->json($exams->map(fn (Exam $exam) => [
            'id' => $exam->id,
            'title' => $exam->title,
            'year' => $exam->year,
            'status' => $exam->status,
            'status_label' => $exam->status === Exam::STATUS_PLANNED ? 'Previsto' : 'Publicado',
        ])->values());
    }

    public function subjectsByExam(int $examId): JsonResponse
    {
        $exam = Exam::query()
            ->with('plannedSubjects')
            ->where('active', true)
            ->findOrFail($examId);

        return response()->json(
            $exam->plannedSubjects->map(fn ($subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
                'scope' => $subject->scope ?? 'general',
                'scope_label' => ($subject->scope ?? 'general') === 'corporation_specific'
                    ? 'Específica da corporação'
                    : 'Geral / reaproveitável',
            ])->values()
        );
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'corporation_id' => ['required', 'integer', 'exists:corporations,id'],
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'mode' => ['required', 'in:train,exam,review'],
        ], [
            'corporation_id.required' => 'Selecione a corporação.',
            'exam_id.required' => 'Selecione o concurso.',
            'subject_ids.required' => 'Selecione pelo menos uma disciplina para estudar.',
            'subject_ids.min' => 'Selecione pelo menos uma disciplina para estudar.',
        ]);

        $exam = Exam::query()
            ->with('plannedSubjects')
            ->where('corporation_id', $data['corporation_id'])
            ->where('active', true)
            ->findOrFail($data['exam_id']);

        $allowedSubjectIds = $exam->plannedSubjects->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        $selectedSubjectIds = array_values(array_intersect(
            array_map('intval', $data['subject_ids']),
            $allowedSubjectIds
        ));

        if (empty($selectedSubjectIds)) {
            return back()
                ->withErrors(['subject_ids' => 'As disciplinas selecionadas não pertencem ao concurso escolhido.'])
                ->withInput();
        }

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

        if ($data['mode'] === 'review') {
            $questionsQuery->whereHas('answers', function ($answer) {
                $answer->where('user_id', auth()->id())
                    ->where('is_correct', false);
            });
        }

        $questions = $questionsQuery
            ->with(['subject', 'topic'])
            ->inRandomOrder()
            ->limit((int) $data['quantity'])
            ->get();

        if ($questions->isEmpty()) {
            return back()
                ->with('error', 'Nenhuma questão publicada foi encontrada para esse concurso e disciplinas selecionadas.')
                ->withInput();
        }

        $session = StudySession::query()->create([
            'user_id' => auth()->id(),
            'corporation_id' => (int) $data['corporation_id'],
            'exam_id' => (int) $data['exam_id'],
            'subject_id' => count($selectedSubjectIds) === 1 ? $selectedSubjectIds[0] : null,
            'topic_id' => null,
            'mode' => $data['mode'] === 'exam' ? 'exam' : 'train',
            'started_at' => now(),
        ]);

        foreach ($questions->values() as $index => $question) {
            StudySessionQuestion::query()->create([
                'study_session_id' => $session->id,
                'question_id' => $question->id,
                'position' => $index + 1,
            ]);
        }

        return redirect()
            ->route('student.study.question', $session)
            ->with('success', 'Sessão criada com base no concurso selecionado.');
    }
}
