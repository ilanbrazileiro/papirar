<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Support\PublicQuestionUrl;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    private const QUESTIONS_PER_SITEMAP = 10000;

    public function index(): Response
    {
        $questionCount = Question::query()->visibleToStudent()->count();
        $questionPages = max(
            1,
            (int) ceil($questionCount / self::QUESTIONS_PER_SITEMAP)
        );

        return response()
            ->view('site.sitemaps.index', [
                'questionPages' => $questionPages,
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function pages(): Response
    {
        $urls = collect([
            [
                'loc' => route('site.home'),
                'lastmod' => null,
            ],
            [
                'loc' => route('site.questions.index'),
                'lastmod' => null,
            ],
            [
                'loc' => route('site.privacy-policy'),
                'lastmod' => null,
            ],
        ]);

        return $this->urlset($urls);
    }

    public function subjects(): Response
    {
        $urls = Subject::query()
            ->where('active', true)
            ->whereHas('questions', fn ($query) => $query->visibleToStudent())
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->map(fn (Subject $subject) => [
                'loc' => route('site.questions.subject', [
                    'subjectSlug' => $subject->slug,
                ]),
                'lastmod' => optional($subject->updated_at)?->toAtomString(),
            ]);

        return $this->urlset($urls);
    }

    public function topics(): Response
    {
        $topics = Topic::query()
            ->where('topics.active', true)
            ->whereHas('questions', fn ($query) => $query->visibleToStudent())
            ->with('subject:id,slug,active')
            ->orderBy('topics.id')
            ->get(['id', 'subject_id', 'slug', 'updated_at']);

        $urls = $topics
            ->filter(fn (Topic $topic) => $topic->subject && $topic->subject->active)
            ->map(fn (Topic $topic) => [
                'loc' => route('site.questions.topic', [
                    'subjectSlug' => $topic->subject->slug,
                    'topicSlug' => $topic->slug,
                ]),
                'lastmod' => optional($topic->updated_at)?->toAtomString(),
            ]);

        return $this->urlset($urls);
    }

    public function questions(int $page): Response
    {
        abort_if($page < 1, 404);

        $questions = Question::query()
            ->visibleToStudent()
            ->with('subject:id,name,slug')
            ->orderBy('id')
            ->forPage($page, self::QUESTIONS_PER_SITEMAP)
            ->get(['id', 'subject_id', 'statement', 'updated_at']);

        abort_if($questions->isEmpty(), 404);

        $urls = $questions->map(fn (Question $question) => [
            'loc' => PublicQuestionUrl::url($question),
            'lastmod' => optional($question->updated_at)?->toAtomString(),
        ]);

        return $this->urlset($urls);
    }

    private function urlset($urls): Response
    {
        return response()
            ->view('site.sitemaps.urlset', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
