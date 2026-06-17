<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Question;
use App\Models\SimulatedExam;
use App\Models\SimulatedExamQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CourseSimulatedController extends Controller
{
    public function index(Course $course): View
    {
        $this->authorizeCourseAccess($course);
        $scope = $this->resolveCourseScope($course);

        return view('student.courses.simulated.index', [
            'course' => $course,
            'subjects' => $this->subjectsForScope($scope),
            'simulatedExams' => SimulatedExam::query()
                ->where('user_id', Auth::id())
                ->where('course_id', $course->id)
                ->latest()
                ->paginate(15),
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourseAccess($course);
        $scope = $this->resolveCourseScope($course);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
            'difficulty' => ['nullable', 'in:easy,medium,hard'],
            'quantity' => ['required', 'integer', 'min:1', 'max:120'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:300'],
        ], [
            'subject_ids.required' => 'Selecione pelo menos uma disciplina.',
            'subject_ids.min' => 'Selecione pelo menos uma disciplina.',
            'duration_minutes.required' => 'Informe o tempo do simulado.',
        ]);

        $selectedSubjectIds = array_values(array_intersect(array_map('intval', $data['subject_ids']), $scope['subject_ids']));

        if (empty($selectedSubjectIds)) {
            return back()->withErrors(['subject_ids' => 'As disciplinas selecionadas não pertencem a este curso.'])->withInput();
        }

        $questionsQuery = Question::query()
            ->where('questions.status', 'published')
            ->whereIn('questions.subject_id', $selectedSubjectIds)
            ->when(!empty($scope['topic_ids']), fn ($q) => $q->whereIn('questions.topic_id', $scope['topic_ids']))
            ->when(!empty($scope['source_material_ids']), fn ($q) => $q->whereIn('questions.source_material_id', $scope['source_material_ids']))
            ->when(!empty($data['difficulty']), fn ($q) => $q->where('questions.difficulty', $data['difficulty']));

        if ($course->corporation_id) {
            $questionsQuery->where(function ($query) use ($course) {
                $query->whereNull('questions.corporation_id')->orWhere('questions.corporation_id', $course->corporation_id);
            });
        }

        if ($course->exam_id) {
            $questionsQuery->where(function ($query) use ($course) {
                $query->whereNull('questions.exam_id')->orWhere('questions.exam_id', $course->exam_id);
            });
        }

        $questions = $questionsQuery->inRandomOrder()->limit((int) $data['quantity'])->get();

        if ($questions->isEmpty()) {
            return back()->with('error', 'Nenhuma questão publicada foi encontrada para este curso e disciplinas.')->withInput();
        }

        $title = trim((string) ($data['title'] ?? '')) ?: 'Simulado ' . now()->format('d/m/Y H:i');
        $startedAt = now();
        $endsAt = $startedAt->copy()->addMinutes((int) $data['duration_minutes']);

        $simulatedExam = DB::transaction(function () use ($course, $data, $questions, $title, $startedAt, $endsAt, $selectedSubjectIds) {
            $simulatedExam = SimulatedExam::query()->create([
                'user_id' => Auth::id(),
                'course_id' => $course->id,
                'title' => $title,
                'corporation_id' => $course->corporation_id,
                'exam_id' => $course->exam_id,
                'subject_id' => count($selectedSubjectIds) === 1 ? $selectedSubjectIds[0] : null,
                'topic_id' => null,
                'source_material_id' => null,
                'difficulty' => $data['difficulty'] ?? null,
                'source_type' => null,
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

            return $simulatedExam;
        });

        return redirect()->route('student.course-simulated.show', $simulatedExam);
    }

    public function show(Request $request, SimulatedExam $simulatedExam): View|RedirectResponse
    {
        $this->authorizeSimulatedExam($simulatedExam);

        if ($this->finishIfExpired($simulatedExam)) {
            return redirect()->route('student.course-simulated.result', $simulatedExam)
                ->with('error', 'O tempo do simulado acabou. As questões não respondidas ficaram em branco.');
        }

        if (!is_null($simulatedExam->finished_at)) {
            return redirect()->route('student.course-simulated.result', $simulatedExam);
        }

        $items = SimulatedExamQuestion::query()
            ->with(['question.corporation', 'question.exam', 'question.subject', 'question.topic', 'question.alternatives'])
            ->where('simulated_exam_id', $simulatedExam->id)
            ->orderBy('position')
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('student.course-simulated.index', $simulatedExam->course_id)->with('error', 'Simulado sem questões.');
        }

        $requestedPosition = max(1, (int) $request->get('question', 1));
        $currentItem = $items->firstWhere('position', $requestedPosition) ?? $items->first();
        $currentPosition = (int) $currentItem->position;

        return view('student.courses.simulated.show', [
            'simulatedExam' => $simulatedExam->load('course'),
            'items' => $items,
            'currentItem' => $currentItem,
            'question' => $currentItem->question,
            'currentPosition' => $currentPosition,
            'totalQuestions' => $items->count(),
            'answeredCount' => $items->whereNotNull('answered_at')->count(),
            'previousItem' => $items->firstWhere('position', $currentPosition - 1),
            'nextItem' => $items->firstWhere('position', $currentPosition + 1),
            'remainingSeconds' => max(0, now()->diffInSeconds($simulatedExam->ends_at, false)),
        ]);
    }

    public function saveAnswer(Request $request, SimulatedExam $simulatedExam): RedirectResponse
    {
        $this->authorizeSimulatedExam($simulatedExam);

        if (!is_null($simulatedExam->finished_at) || $this->finishIfExpired($simulatedExam)) {
            return redirect()->route('student.course-simulated.result', $simulatedExam)->with('error', 'Este simulado já foi finalizado.');
        }

        $data = $request->validate([
            'simulated_exam_question_id' => ['required', 'integer', 'exists:simulated_exam_questions,id'],
            'selected_alternative_id' => ['required', 'integer', 'exists:alternatives,id'],
            'next_position' => ['nullable', 'integer', 'min:1'],
        ]);

        $item = SimulatedExamQuestion::query()->with('question.alternatives')
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

        return redirect()->route('student.course-simulated.show', [
            'simulatedExam' => $simulatedExam->id,
            'question' => $targetPosition,
        ])->with('success', 'Resposta salva.');
    }

    public function finish(SimulatedExam $simulatedExam): RedirectResponse
    {
        $this->authorizeSimulatedExam($simulatedExam);
        $this->finishExam($simulatedExam);
        return redirect()->route('student.course-simulated.result', $simulatedExam);
    }

    public function result(SimulatedExam $simulatedExam): View
    {
        $this->authorizeSimulatedExam($simulatedExam);
        $this->finishIfExpired($simulatedExam);

        $items = SimulatedExamQuestion::query()
            ->with(['question.subject', 'question.topic', 'question.exam', 'question.alternatives', 'selectedAlternative'])
            ->where('simulated_exam_id', $simulatedExam->id)
            ->orderBy('position')
            ->get();

        $answeredCount = $items->whereNotNull('answered_at')->count();
        $blankCount = max(0, $items->count() - $answeredCount);

        $subjectStats = $items->groupBy(fn ($item) => optional($item->question->subject)->name ?: 'Sem disciplina')
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

        return view('student.courses.simulated.result', [
            'simulatedExam' => $simulatedExam->fresh(['course', 'corporation', 'exam', 'subject']),
            'items' => $items,
            'answeredCount' => $answeredCount,
            'blankCount' => $blankCount,
            'subjectStats' => $subjectStats,
        ]);
    }

    private function authorizeCourseAccess(Course $course): void
    {
        $hasAccess = CourseAccess::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->exists();

        abort_unless($hasAccess, 403);
    }

    private function authorizeSimulatedExam(SimulatedExam $simulatedExam): void
    {
        abort_unless($simulatedExam->user_id === Auth::id(), 403);
        abort_unless($simulatedExam->course_id, 403);
        $this->authorizeCourseAccess($simulatedExam->course);
    }

    private function resolveCourseScope(Course $course): array
    {
        if ($course->inherit_exam_scope && $course->exam_id) {
            return [
                'subject_ids' => DB::table('exam_subjects')->where('exam_id', $course->exam_id)->where('is_active', true)->pluck('subject_id')->map(fn ($id) => (int) $id)->values()->all(),
                'topic_ids' => DB::table('exam_subject_topics')->join('exam_subjects', 'exam_subject_topics.exam_subject_id', '=', 'exam_subjects.id')->where('exam_subjects.exam_id', $course->exam_id)->where('exam_subjects.is_active', true)->where('exam_subject_topics.is_active', true)->pluck('exam_subject_topics.topic_id')->map(fn ($id) => (int) $id)->unique()->values()->all(),
                'source_material_ids' => DB::table('exam_subject_source_materials')
                    ->join('exam_subjects', 'exam_subject_source_materials.exam_subject_id', '=', 'exam_subjects.id')
                    ->where('exam_subjects.exam_id', $course->exam_id)
                    ->where('exam_subjects.is_active', true)
                    ->where('exam_subject_source_materials.is_active', true)
                    ->pluck('exam_subject_source_materials.source_material_id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all(),
            ];
        }

        return [
            'subject_ids' => DB::table('course_subjects')->where('course_id', $course->id)->where('is_active', true)->pluck('subject_id')->map(fn ($id) => (int) $id)->values()->all(),
            'topic_ids' => DB::table('course_topics')->where('course_id', $course->id)->where('is_active', true)->pluck('topic_id')->map(fn ($id) => (int) $id)->values()->all(),
            'source_material_ids' => DB::table('course_source_materials')->where('course_id', $course->id)->where('is_active', true)->pluck('source_material_id')->map(fn ($id) => (int) $id)->values()->all(),
        ];
    }

    private function subjectsForScope(array $scope)
    {
        if (empty($scope['subject_ids'])) return collect();
        return DB::table('subjects')->whereIn('id', $scope['subject_ids'])->where('active', true)->orderBy('name')->get(['id', 'name']);
    }

    private function finishIfExpired(SimulatedExam $simulatedExam): bool
    {
        if (!is_null($simulatedExam->finished_at)) return false;
        if ($simulatedExam->ends_at && now()->greaterThanOrEqualTo($simulatedExam->ends_at)) {
            $this->finishExam($simulatedExam, true);
            return true;
        }
        return false;
    }

    private function finishExam(SimulatedExam $simulatedExam, bool $expired = false): void
    {
        if (!is_null($simulatedExam->finished_at)) return;

        $items = SimulatedExamQuestion::query()->where('simulated_exam_id', $simulatedExam->id)->get();
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
