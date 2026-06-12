<?php

namespace App\Services\Questions;

use App\Models\Question;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class QuestionDuplicateChecker
{
    /**
     * Normaliza o enunciado para comparação exata.
     * Não faz comparação por similaridade: apenas identifica duplicidade real após limpeza básica.
     */
    public function normalizeStatement(?string $statement): string
    {
        $statement = (string) $statement;

        // Remove scripts/styles e tags HTML, preservando apenas texto.
        $statement = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', ' ', $statement) ?? $statement;
        $statement = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', ' ', $statement) ?? $statement;
        $statement = strip_tags($statement);
        $statement = html_entity_decode($statement, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normaliza espaços, caixa e pontuação leve.
        $statement = Str::of($statement)
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->lower()
            ->value();

        return $statement;
    }

    /**
     * Busca duplicatas exatas por enunciado normalizado.
     * Mantém a comparação no PHP para funcionar sem nova coluna no banco.
     */
    public function findExactDuplicates(string $statement, ?int $ignoreQuestionId = null, int $limit = 10): Collection
    {
        $normalized = $this->normalizeStatement($statement);

        if ($normalized === '') {
            return collect();
        }

        $query = Question::query()
            ->with(['subject', 'topic', 'corporation', 'exam'])
            ->select(['id', 'statement', 'subject_id', 'topic_id', 'corporation_id', 'exam_id', 'status', 'difficulty', 'created_at'])
            ->orderByDesc('id');

        if ($ignoreQuestionId) {
            $query->where('id', '<>', $ignoreQuestionId);
        }

        // Filtra candidatos por um trecho do texto para evitar varrer tudo em bancos maiores.
        $needle = mb_substr($normalized, 0, 80);
        if ($needle !== '') {
            $query->where('statement', 'like', '%' . mb_substr(strip_tags($statement), 0, 60) . '%');
        }

        $candidates = $query->limit(100)->get();

        return $candidates
            ->filter(fn (Question $question) => $this->normalizeStatement($question->statement) === $normalized)
            ->take($limit)
            ->values();
    }

    public function hasExactDuplicate(string $statement, ?int $ignoreQuestionId = null): bool
    {
        return $this->findExactDuplicates($statement, $ignoreQuestionId, 1)->isNotEmpty();
    }
}
