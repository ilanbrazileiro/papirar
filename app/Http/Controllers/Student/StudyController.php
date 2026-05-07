<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Question;
use App\Models\SavedFilter;
use App\Models\StudySession;
use App\Models\StudySessionQuestion;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\UserAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudyController extends Controller
{
    public function index()
    {
        return view('student.study.choose');
    }

    public function filter()
    {
        $savedFilter = SavedFilter::query()->firstOrCreate(
            ['user_id' => auth()->id()],
            ['mode' => 'train', 'quantity' => 10]
        );

        return view('student.study.index', [
            'savedFilter' => $savedFilter,
            'corporations' => Corporation::query()->where('active', true)->orderBy('name')->get(),
            'subjects' => Subject::query()->where('active', true)->orderBy('name')->get(),
            'topics' => Topic::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'corporation_id' => ['nullable', 'integer', 'exists:corporations,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'difficulty' => ['nullable', 'in:easy,medium,hard'],
            'source_type' => ['nullable', 'in:exam,authored,adapted'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'mode' => ['required', 'in:train,exam,review'],
        ]);

        $savedFilter = SavedFilter::query()->updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'corporation_id' => $data['corporation_id'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
                'topic_id' => $data['topic_id'] ?? null,
                'difficulty' => $data['difficulty'] ?? null,
                'source_type' => $data['source_type'] ?? null,
                'quantity' => $data['quantity'],
                'mode' => $data['mode'],
            ]
        );

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

        $session = StudySession::query()->create([
            'user_id' => auth()->id(),
            'corporation_id' => $data['corporation_id'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'mode' => $data['mode'],
            'started_at' => now(),
        ]);

        foreach ($questions->values() as $index => $question) {
            StudySessionQuestion::query()->create([
                'study_session_id' => $session->id,
                'question_id' => $question->id,
                'position' => $index + 1,
            ]);
        }

        return redirect()->route('student.study.question', $session);
    }

    public function showQuestion(StudySession $session)
    {
        abort_unless($session->user_id === auth()->id(), 403);

        $currentItem = StudySessionQuestion::query()
            ->where('study_session_id', $session->id)
            ->whereNull('answered_at')
            ->orderBy('position')
            ->first();

        if (!$currentItem) {
            return redirect()->route('student.study.result', $session);
        }

        $question = Question::query()
            ->with([
                'corporation',
                'exam',
                'subject',
                'topic',
                'alternatives',
                'comments' => fn ($q) => $q->where('status', 'approved')->latest(),
                'comments.user',
                'difficultyVotes',
            ])
            ->findOrFail($currentItem->question_id);

        return view('student.study.question', [
            'session' => $session,
            'question' => $question,
            'currentItem' => $currentItem,
            'currentPosition' => $currentItem->position,
            'totalQuestions' => StudySessionQuestion::query()->where('study_session_id', $session->id)->count(),
            'userAnswer' => null,
        ]);
    }

    public function answer(Request $request, StudySession $session)
    {
        abort_unless($session->user_id === auth()->id(), 403);

        $data = $request->validate([
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'selected_alternative_id' => ['required', 'integer', 'exists:alternatives,id'],
        ]);

        $currentItem = StudySessionQuestion::query()
            ->where('study_session_id', $session->id)
            ->where('question_id', $data['question_id'])
            ->firstOrFail();

        $question = Question::query()
            ->with([
                'corporation',
                'exam',
                'subject',
                'topic',
                'alternatives',
                'comments' => fn ($q) => $q->where('status', 'approved')->latest(),
                'comments.user',
                'difficultyVotes',
            ])
            ->findOrFail($data['question_id']);

        $selectedAlternative = $question->alternatives->firstWhere('id', $data['selected_alternative_id']);
        abort_if(!$selectedAlternative, 422, 'Alternativa inválida para esta questão.');

        UserAnswer::query()->updateOrCreate(
            [
                'user_id' => auth()->id(),
                'question_id' => $question->id,
                'study_session_id' => $session->id,
            ],
            [
                'selected_alternative_id' => $selectedAlternative->id,
                'is_correct' => (bool) $selectedAlternative->is_correct,
                'answered_at' => now(),
            ]
        );

        $currentItem->update(['answered_at' => now()]);

        return redirect()->route('student.study.review', [
            'session' => $session->id,
            'question' => $question->id,
        ]);
    }

    public function review(StudySession $session, Question $question)
    {
        abort_unless($session->user_id === auth()->id(), 403);

        $currentItem = StudySessionQuestion::query()
            ->where('study_session_id', $session->id)
            ->where('question_id', $question->id)
            ->firstOrFail();

        $question->load([
            'corporation',
            'exam',
            'subject',
            'topic',
            'alternatives',
            'comments' => fn ($q) => $q->where('status', 'approved')->latest(),
            'comments.user',
            'difficultyVotes',
        ]);

        $userAnswer = UserAnswer::query()
            ->where('study_session_id', $session->id)
            ->where('question_id', $question->id)
            ->where('user_id', auth()->id())
            ->first();

        return view('student.study.question', [
            'session' => $session,
            'question' => $question,
            'currentItem' => $currentItem,
            'currentPosition' => $currentItem->position,
            'totalQuestions' => StudySessionQuestion::query()
                ->where('study_session_id', $session->id)
                ->count(),
            'userAnswer' => $userAnswer,
        ]);
    }

    public function next(StudySession $session): RedirectResponse
    {
        abort_unless($session->user_id === auth()->id(), 403);

        return redirect()->route('student.study.question', $session);
    }

    public function result(StudySession $session)
    {
        abort_unless($session->user_id === auth()->id(), 403);

        $answers = UserAnswer::query()
            ->with(['question.subject', 'question.topic'])
            ->where('study_session_id', $session->id)
            ->where('user_id', auth()->id())
            ->get();

        $total = StudySessionQuestion::query()->where('study_session_id', $session->id)->count();
        $correct = $answers->where('is_correct', true)->count();
        $incorrect = $answers->where('is_correct', false)->count();
        $accuracy = $total > 0 ? ($correct / $total) * 100 : 0;

        return view('student.study.result', compact(
            'session',
            'answers',
            'total',
            'correct',
            'incorrect',
            'accuracy'
        ));
    }
}
