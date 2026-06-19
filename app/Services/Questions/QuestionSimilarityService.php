<?php

namespace App\Services\Questions;

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class QuestionSimilarityService
{
    private array $stopWords = [
        'que', 'qual', 'quais', 'quando', 'onde', 'como', 'para', 'pela', 'pelo', 'pelas', 'pelos',
        'uma', 'umas', 'uns', 'dos', 'das', 'com', 'sem', 'por', 'sobre', 'entre', 'este', 'esta',
        'esse', 'essa', 'isso', 'isto', 'aquele', 'aquela', 'deve', 'ser', 'sao', 'estao', 'correta',
        'correto', 'incorreta', 'incorreto', 'assinale', 'alternativa', 'afirmativa', 'segundo',
        'acerca', 'respectivamente', 'apenas', 'exceto', 'mais', 'menos', 'nao', 'sim', 'nas', 'nos',
        'aos', 'aas', 'aos', 'a', 'o', 'e', 'do', 'da', 'de', 'em', 'no', 'na', 'as', 'os', 'um'
    ];

    public function findSimilarToQuestion(Question $question, array $filters = []): array
    {
        $baseText = $this->questionComparableText($question);

        $candidates = $this->candidateQuery($baseText, $filters)
            ->where('id', '!=', $question->id)
            ->when(($filters['scope'] ?? 'same_subject') === 'same_topic' && $question->topic_id, function ($query) use ($question) {
                $query->where('topic_id', $question->topic_id);
            })
            ->when(($filters['scope'] ?? 'same_subject') === 'same_subject' && $question->subject_id, function ($query) use ($question) {
                $query->where('subject_id', $question->subject_id);
            })
            ->get();

        return $this->scoreCandidates($baseText, $candidates, (int) ($filters['min_score'] ?? 65));
    }

    public function findSimilarText(string $text, array $filters = []): array
    {
        $candidates = $this->candidateQuery($text, $filters)->get();

        return $this->scoreCandidates($text, $candidates, (int) ($filters['min_score'] ?? 65));
    }

    private function candidateQuery(string $baseText, array $filters)
    {
        $tokens = array_slice($this->tokens($baseText), 0, 8);

        return Question::query()
            ->with(['subject', 'topic'])
            ->when(!empty($filters['subject_id']), fn ($query) => $query->where('subject_id', $filters['subject_id']))
            ->when(!empty($filters['topic_id']), fn ($query) => $query->where('topic_id', $filters['topic_id']))
            ->when(!empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(!empty($tokens), function ($query) use ($tokens) {
                $query->where(function ($subQuery) use ($tokens) {
                    foreach ($tokens as $token) {
                        $subQuery->orWhere('statement', 'like', '%' . $token . '%');
                    }
                });
            })
            ->orderByDesc('id')
            ->limit(500);
    }

    private function scoreCandidates(string $baseText, Collection $candidates, int $minScore): array
    {
        $baseNormalized = $this->normalize($baseText);
        $baseTokens = $this->tokens($baseText);

        $results = [];

        foreach ($candidates as $candidate) {
            $candidateText = $this->questionComparableText($candidate);
            $candidateNormalized = $this->normalize($candidateText);
            $candidateTokens = $this->tokens($candidateText);

            similar_text($baseNormalized, $candidateNormalized, $textSimilarity);
            $jaccard = $this->jaccard($baseTokens, $candidateTokens) * 100;
            $score = (int) round(($jaccard * 0.65) + ($textSimilarity * 0.35));

            if ($score >= $minScore) {
                $results[] = [
                    'question' => $candidate,
                    'score' => $score,
                    'jaccard' => (int) round($jaccard),
                    'text_similarity' => (int) round($textSimilarity),
                    'common_terms' => array_slice(array_values(array_intersect($baseTokens, $candidateTokens)), 0, 12),
                ];
            }
        }

        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($results, 0, 50);
    }

    private function questionComparableText(Question $question): string
    {
        return trim((string) $question->statement);
    }

    private function normalize(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = Str::lower($text);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = preg_replace('/[^a-z0-9\s]/i', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }

    private function tokens(string $text): array
    {
        $normalized = $this->normalize($text);
        $tokens = preg_split('/\s+/', $normalized) ?: [];

        $tokens = array_filter($tokens, function ($token) {
            return mb_strlen($token) >= 3 && !in_array($token, $this->stopWords, true);
        });

        return array_values(array_unique($tokens));
    }

    private function jaccard(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0;
        }

        $intersection = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));

        return $union > 0 ? $intersection / $union : 0;
    }
}
