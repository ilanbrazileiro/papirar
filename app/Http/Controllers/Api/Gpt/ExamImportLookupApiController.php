<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamImportLookupApiController extends Controller
{
    public function searchExams(Request $request): JsonResponse
    {
        $data = $request->validate([
            'corporation_id' => ['nullable', 'integer', 'exists:corporations,id'],
            'q' => ['nullable', 'string', 'max:180'],
            'status' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $query = Exam::query()->with('corporation:id,name')
            ->withCount([
                'questions',
                'questions as questions_draft_count' => fn ($q) => $q->where('status', 'draft'),
                'questions as questions_published_count' => fn ($q) => $q->where('status', 'published'),
                'questions as questions_reviewed_count' => fn ($q) => $q->where('status', 'reviewed'),
                'questions as questions_archived_count' => fn ($q) => $q->where('status', 'archived'),
            ])->orderByDesc('id');

        if (isset($data['corporation_id'])) {
            $query->where('corporation_id', $data['corporation_id']);
        }
        if (! empty($data['status'])) {
            $query->where('status', $data['status']);
        }
        if (! empty($data['q'])) {
            $term = $data['q'];
            $query->where(fn ($q) => $q->where('title', 'like', '%'.$term.'%')
                ->orWhere('description', 'like', '%'.$term.'%'));
        }

        return response()->json([
            'data' => $query->limit($data['per_page'] ?? 20)->get()->map(fn (Exam $e) => [
                'id' => $e->id,
                'corporation_id' => $e->corporation_id,
                'corporation_name' => $e->corporation?->name,
                'name' => $e->title, // compatibilidade com consumidores anteriores
                'title' => $e->title,
                'slug' => $e->slug ?? null,
                'year' => $e->year,
                'exam_type' => $e->exam_type,
                'status' => $e->status,
                'active' => $e->active,
                'questions_total' => (int) $e->questions_count,
                'questions_by_status' => $this->counts($e),
            ])->values(),
        ]);
    }

    public function check(Request $request): JsonResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'between:1900,2100'],
            'board' => ['required', 'string', 'min:2', 'max:100'],
            'organization' => ['required', 'string', 'min:2', 'max:150'],
            'position' => ['required', 'string', 'min:2', 'max:150'],
        ]);
        $year = (int) $data['year'];
        $board = $data['board'];
        $org = $data['organization'];
        $position = $data['position'];

        // Há concursos cujos títulos não incluem a banca ou cuja sigla é usada
        // sem hífen. Evidências são apresentadas, sem declarar ausência definitiva.
        $exams = Exam::query()->with('corporation:id,name')
            ->withCount([
                'questions',
                'questions as questions_draft_count' => fn ($q) => $q->where('status', 'draft'),
                'questions as questions_published_count' => fn ($q) => $q->where('status', 'published'),
                'questions as questions_reviewed_count' => fn ($q) => $q->where('status', 'reviewed'),
                'questions as questions_archived_count' => fn ($q) => $q->where('status', 'archived'),
            ])
            ->where('year', $year)->get()
            ->map(function (Exam $e) use ($org, $position) {
                $orgMatch = $this->matches($e->corporation?->name ?? '', $org)
                    || $this->matches($e->title, $org);
                $roleMatch = $this->matches($e->title, $position);
                if (! $orgMatch) {
                    return null;
                }
                return [
                    'exam_id' => $e->id,
                    'title' => $e->title,
                    'year' => $e->year,
                    'corporation_name' => $e->corporation?->name,
                    'role_match' => $roleMatch,
                    'questions_total' => (int) $e->questions_count,
                    'questions_by_status' => $this->counts($e),
                    'note' => 'Coincidência por órgão e ano; conferir cargo e banca no caderno original.',
                ];
            })->filter()->values();

        $references = Question::query()
            ->select(['id', 'exam_id', 'source_reference', 'status'])
            ->whereNotNull('source_reference')
            ->where('source_reference', 'like', '%'.$year.'%')
            ->orderBy('id')
            ->cursor();

        $matchedCount = 0;
        $matchedStatuses = ['draft' => 0, 'published' => 0, 'reviewed' => 0, 'archived' => 0];
        $examples = [];
        foreach ($references as $q) {
            $ref = (string) $q->source_reference;
            if (! $this->matches($ref, $board) || ! $this->matches($ref, $org)
                || ! $this->matches($ref, $position)) {
                continue;
            }
            $matchedCount++;
            if (array_key_exists($q->status, $matchedStatuses)) {
                $matchedStatuses[$q->status]++;
            }
            if (count($examples) < 30) {
                $examples[] = ['question_id' => $q->id, 'exam_id' => $q->exam_id, 'reference' => $ref];
            }
        }

        return response()->json(['data' => [
            'criteria' => $data,
            'classification' => ($exams->isNotEmpty() || $matchedCount > 0) ? 'found_evidence' : 'not_confirmed',
            'can_skip_download' => false,
            'reason' => 'A existência de um concurso ou de referências não comprova a importação integral da prova. Resultado negativo também não garante ausência.',
            'exams' => $exams,
            'reference_matches' => [
                'total' => $matchedCount,
                'questions_by_status' => $matchedStatuses,
                'examples' => $examples,
            ],
        ]]);
    }

    private function counts(Exam $e): array
    {
        return [
            'draft' => (int) $e->questions_draft_count,
            'published' => (int) $e->questions_published_count,
            'reviewed' => (int) $e->questions_reviewed_count,
            'archived' => (int) $e->questions_archived_count,
        ];
    }

    private function matches(?string $text, string $search): bool
    {
        $normalize = static fn (string $s): string => preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii($s))) ?? '';
        $value = $normalize((string) $text);
        $parts = preg_split('/[^a-z0-9]+/', Str::lower(Str::ascii($search))) ?: [];
        $parts = array_values(array_filter($parts, static fn ($part) => strlen($part) >= 2));
        if (! $parts || $value === '') {
            return false;
        }
        // Permite, por exemplo, TJ-MT e TJMT.
        return str_contains($value, implode('', $parts)) || collect($parts)->every(fn ($part) => str_contains($value, $part));
    }
}
