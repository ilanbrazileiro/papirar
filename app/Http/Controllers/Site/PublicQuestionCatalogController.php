<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Support\PublicQuestionUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicQuestionCatalogController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::query()
            ->where('active', true)
            ->whereHas('questions', fn ($query) => $query->visibleToStudent())
            ->withCount([
                'questions as public_questions_count' => fn ($query) => $query->visibleToStudent(),
            ])
            ->orderByDesc('public_questions_count')
            ->orderBy('name')
            ->get();

        $latestQuestions = Question::query()
            ->visibleToStudent()
            ->with(['subject:id,name,slug', 'topic:id,name,slug'])
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(fn (Question $question) => $this->questionCard($question));

        $totalQuestions = Question::query()->visibleToStudent()->count();

        return view('site.questions.index', [
            'subjects' => $subjects,
            'latestQuestions' => $latestQuestions,
            'totalQuestions' => $totalQuestions,
            'canonicalUrl' => route('site.questions.index'),
        ]);
    }

    public function subject(string $subjectSlug): View|RedirectResponse
    {
        $subject = Subject::query()
            ->where('active', true)
            ->where('slug', $subjectSlug)
            ->firstOrFail();

        if ($subjectSlug !== $subject->slug) {
            return redirect()->route('site.questions.subject', [
                'subjectSlug' => $subject->slug,
            ], 301);
        }

        $questions = Question::query()
            ->visibleToStudent()
            ->where('subject_id', $subject->id)
            ->with(['subject:id,name,slug', 'topic:id,name,slug', 'examBoard:id,name', 'exam:id,title,year'])
            ->latest('id')
            ->paginate(20);

        if ($questions->currentPage() > 1 && $questions->isEmpty()) {
            abort(404);
        }

        $topics = Topic::query()
            ->where('subject_id', $subject->id)
            ->where('active', true)
            ->whereHas('questions', fn ($query) => $query->visibleToStudent())
            ->withCount([
                'questions as public_questions_count' => fn ($query) => $query->visibleToStudent(),
            ])
            ->orderByDesc('public_questions_count')
            ->orderBy('name')
            ->get();

        $cards = $questions->getCollection()
            ->map(fn (Question $question) => $this->questionCard($question));

        $questions->setCollection($cards);

        $canonicalUrl = route('site.questions.subject', [
            'subjectSlug' => $subject->slug,
        ]);

        return view('site.questions.catalog', [
            'subject' => $subject,
            'topic' => null,
            'topics' => $topics,
            'questions' => $questions,
            'canonicalUrl' => $canonicalUrl,
            'seoTitle' => 'Questões de ' . $subject->name . ' para Concursos | Papirar',
            'seoDescription' => Str::limit(
                'Resolva questões de ' . $subject->name .
                ' organizadas por tópico no Papirar. Treine por questões e acompanhe sua preparação para concursos.',
                158,
                ''
            ),
            'heading' => 'Questões de ' . $subject->name,
            'intro' => $subject->description
                ?: 'Pratique questões de ' . $subject->name . ' organizadas por tópico e concurso.',
        ]);
    }

    public function topic(string $subjectSlug, string $topicSlug): View|RedirectResponse
    {
        $subject = Subject::query()
            ->where('active', true)
            ->where('slug', $subjectSlug)
            ->firstOrFail();

        $topic = Topic::query()
            ->where('active', true)
            ->where('subject_id', $subject->id)
            ->where('slug', $topicSlug)
            ->firstOrFail();

        $questions = Question::query()
            ->visibleToStudent()
            ->where('subject_id', $subject->id)
            ->where('topic_id', $topic->id)
            ->with(['subject:id,name,slug', 'topic:id,name,slug', 'examBoard:id,name', 'exam:id,title,year'])
            ->latest('id')
            ->paginate(20);

        if ($questions->currentPage() > 1 && $questions->isEmpty()) {
            abort(404);
        }

        $cards = $questions->getCollection()
            ->map(fn (Question $question) => $this->questionCard($question));

        $questions->setCollection($cards);

        $canonicalUrl = route('site.questions.topic', [
            'subjectSlug' => $subject->slug,
            'topicSlug' => $topic->slug,
        ]);

        return view('site.questions.catalog', [
            'subject' => $subject,
            'topic' => $topic,
            'topics' => collect(),
            'questions' => $questions,
            'canonicalUrl' => $canonicalUrl,
            'seoTitle' => Str::limit(
                'Questões de ' . $topic->name . ' - ' . $subject->name . ' | Papirar',
                65,
                ''
            ),
            'seoDescription' => Str::limit(
                'Resolva questões sobre ' . $topic->name . ' em ' . $subject->name .
                '. Estude por questões no Papirar.',
                158,
                ''
            ),
            'heading' => 'Questões sobre ' . $topic->name,
            'intro' => $topic->description
                ?: 'Treine questões de ' . $topic->name . ' em ' . $subject->name . '.',
        ]);
    }

    private function questionCard(Question $question): array
    {
        $plain = html_entity_decode(
            (string) $question->statement,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $plain = strip_tags($plain);
        $plain = preg_replace('/\s+/u', ' ', $plain) ?: '';

        return [
            'id' => $question->id,
            'url' => PublicQuestionUrl::url($question),
            'statement_excerpt' => Str::limit(trim($plain), 220),
            'subject_name' => $question->subject?->name,
            'topic_name' => $question->topic?->name,
            'exam_board_name' => $question->examBoard?->name,
            'exam_year' => $question->exam?->year,
        ];
    }
}
