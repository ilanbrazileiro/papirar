<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Alternative;
use App\Models\Corporation;
use App\Models\Question;
use App\Models\StudySession;
use App\Models\StudySessionQuestion;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\UserAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudyController extends Controller
{
    public function index()
    {
        return view('student.study.index', [
            'corporations' => Corporation::where('active', true)->orderBy('name')->get(),
            'subjects' => Subject::where('active', true)->orderBy('name')->get(),
            'topics' => Topic::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'corporation_id' => ['required', 'exists:corporations,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'topic_id' => ['nullable', 'exists:topics,id'],
            'difficulty' => ['nullable', Rule::in(['easy', 'medium', 'hard'])],
            'source_type' => ['nullable', Rule::in(['official_exam', 'authored', 'adapted'])],
            'mode' => ['required', Rule::in(['train', 'exam', 'review'])],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Question::query()
            ->where('status', 'published')
            ->where('corporation_id', $validated['corporation_id']);

        if (!empty($validated['subject_id'])) {
            $query->where('subject_id', $validated['subject_id']);
        }

        if (!empty($validated['topic_id'])) {
            $query->where('topic_id', $validated['topic_id']);
        }

        if (!empty($validated['difficulty'])) {
            $query->where('difficulty', $validated['difficulty']);
        }

        if (!empty($validated['source_type'])) {
            $query->where('source_type', $validated['source_type']);
        }

        if ($validated['mode'] === 'review') {
            $wrongQuestionIds = UserAnswer::query()
                ->where('user_id', Auth::id())
                ->where('is_correct', false)
                ->pluck('question_id');

            $query->whereIn('id', $wrongQuestionIds);
        }

        $questionIds = $query
            ->inRandomOrder()
            ->limit($validated['quantity'])
            ->pluck('id');

        if ($questionIds->isEmpty()) {
            return back()->withInput()->withErrors([
                'quantity' => 'Nenhuma questão publicada foi encontrada com os filtros informados.',
            ]);
        }

        $session = null;

        DB::transaction(function () use ($validated, $questionIds, &$session) {
            $session = StudySession::create([
                'user_id' => Auth::id(),
                'corporation_id' => $validated['corporation_id'],
                'subject_id' => $validated['subject_id'] ?? null,
                'mode' => $validated['mode'],
                'started_at' => now(),
                'finished_at' => null,
            ]);

            $position = 1;

            foreach ($questionIds as $questionId) {
                StudySessionQuestion::create([
                    'study_session_id' => $session->id,
                    'question_id' => $questionId,
                    'position' => $position,
                ]);

                $position++;
            }
        });

        return redirect()->route('student.study.question', $session);
    }

    public function showQuestion(StudySession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);

        $nextItem = $session->sessionQuestions()
            ->whereNull('answered_at')
            ->with([
                'question.corporation',
                'question.subject',
                'question.topic',
                'question.exam',
                'question.alternatives',
            ])
            ->first();

        if (!$nextItem) {
            return redirect()->route('student.study.result', $session);
        }

        $current = $nextItem->position;
        $total = $session->sessionQuestions()->count();
        $progress = max(0, intval((($current - 1) / $total) * 100));

        return view('student.study.question', [
            'session' => $session,
            'sessionQuestion' => $nextItem,
            'question' => $nextItem->question,
            'current' => $current,
            'total' => $total,
            'progress' => $progress,
        ]);
    }

    public function answer(Request $request, StudySession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'session_question_id' => ['required', 'exists:study_session_questions,id'],
            'alternative_id' => ['required', 'exists:alternatives,id'],
        ]);

        $sessionQuestion = StudySessionQuestion::with('question.alternatives')
            ->where('study_session_id', $session->id)
            ->findOrFail($validated['session_question_id']);

        if ($sessionQuestion->answered_at) {
            return redirect()->route('student.study.question', $session);
        }

        $selectedAlternative = Alternative::query()
            ->where('question_id', $sessionQuestion->question_id)
            ->findOrFail($validated['alternative_id']);

        $isCorrect = (bool) $selectedAlternative->is_correct;

        DB::transaction(function () use ($session, $sessionQuestion, $selectedAlternative, $isCorrect) {
            UserAnswer::create([
                'user_id' => Auth::id(),
                'question_id' => $sessionQuestion->question_id,
                'study_session_id' => $session->id,
                'selected_alternative_id' => $selectedAlternative->id,
                'is_correct' => $isCorrect,
                'answered_at' => now(),
            ]);

            $sessionQuestion->update([
                'answered_at' => now(),
            ]);
        });

        $question = $sessionQuestion->question()->with([
            'corporation',
            'subject',
            'topic',
            'exam',
            'alternatives',
        ])->first();

        $correctAlternative = $question->alternatives->firstWhere('is_correct', true);
        $current = $sessionQuestion->position;
        $total = $session->sessionQuestions()->count();
        $progress = intval(($current / $total) * 100);

        return view('student.study.question', [
            'session' => $session,
            'sessionQuestion' => $sessionQuestion,
            'question' => $question,
            'current' => $current,
            'total' => $total,
            'progress' => $progress,
            'answered' => true,
            'selectedAlternativeId' => $selectedAlternative->id,
            'correctAlternativeId' => optional($correctAlternative)->id,
            'isCorrect' => $isCorrect,
        ]);
    }

    public function next(StudySession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);

        $hasPending = $session->sessionQuestions()->whereNull('answered_at')->exists();

        if (!$hasPending) {
            if (!$session->finished_at) {
                $session->update([
                    'finished_at' => now(),
                ]);
            }

            return redirect()->route('student.study.result', $session);
        }

        return redirect()->route('student.study.question', $session);
    }

    public function result(StudySession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);

        if (!$session->finished_at && !$session->sessionQuestions()->whereNull('answered_at')->exists()) {
            $session->update([
                'finished_at' => now(),
            ]);
        }

        $answers = $session->answers()->with('question.subject')->get();

        $total = $answers->count();
        $correct = $answers->where('is_correct', true)->count();
        $wrong = $answers->where('is_correct', false)->count();
        $accuracy = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

        $bySubject = $answers
            ->groupBy(fn ($answer) => optional($answer->question->subject)->name ?? 'Sem disciplina')
            ->map(function ($group) {
                $count = $group->count();
                $hits = $group->where('is_correct', true)->count();

                return [
                    'total' => $count,
                    'correct' => $hits,
                    'accuracy' => $count > 0 ? round(($hits / $count) * 100, 2) : 0,
                ];
            });

        return view('student.study.result', [
            'session' => $session,
            'total' => $total,
            'correct' => $correct,
            'wrong' => $wrong,
            'accuracy' => $accuracy,
            'bySubject' => $bySubject,
        ]);
    }
}