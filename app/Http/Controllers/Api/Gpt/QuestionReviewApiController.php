<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Exam;
use App\Models\Question;
use App\Models\SourceMaterial;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class QuestionReviewApiController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'service' => 'Papirar GPT Review API',
            'version' => '1.0.0',
        ]);
    }

    public function corporations(Request $request): JsonResponse
    {
        $query = Corporation::query()->orderBy('name');

        if ($request->filled('active') && Schema::hasColumn('corporations', 'active')) {
            $query->where('active', $request->boolean('active'));
        }

        return response()->json([
            'data' => $query->get()->map(fn ($corporation) => [
                'id' => $corporation->id,
                'name' => $corporation->name,
                'slug' => $corporation->slug ?? null,
                'active' => $corporation->active ?? null,
            ]),
        ]);
    }

    public function exams(Request $request): JsonResponse
    {
        $query = Exam::query()->orderByDesc('id');

        if ($request->filled('corporation_id')) {
            $query->where('corporation_id', $request->integer('corporation_id'));
        }

        if ($request->filled('status') && Schema::hasColumn('exams', 'status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->string('q')->toString() . '%';
            $query->where(function (Builder $builder) use ($term) {
                $builder->where('name', 'like', $term);
                if (Schema::hasColumn('exams', 'description')) {
                    $builder->orWhere('description', 'like', $term);
                }
            });
        }

        return response()->json([
            'data' => $query->limit($this->limit($request, 100))->get()->map(fn ($exam) => [
                'id' => $exam->id,
                'corporation_id' => $exam->corporation_id ?? null,
                'name' => $exam->name,
                'slug' => $exam->slug ?? null,
                'year' => $exam->year ?? null,
                'status' => $exam->status ?? null,
                'active' => $exam->active ?? null,
            ]),
        ]);
    }

    public function subjects(Request $request): JsonResponse
    {
        $query = Subject::query()->orderBy('name');

        if ($request->filled('active') && Schema::hasColumn('subjects', 'active')) {
            $query->where('active', $request->boolean('active'));
        }

        return response()->json([
            'data' => $query->get()->map(fn ($subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
                'slug' => $subject->slug ?? null,
                'active' => $subject->active ?? null,
            ]),
        ]);
    }

    public function topics(Request $request): JsonResponse
    {
        $query = Topic::query()->with('subject')->orderBy('name');

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->integer('subject_id'));
        }

        if ($request->filled('active') && Schema::hasColumn('topics', 'active')) {
            $query->where('active', $request->boolean('active'));
        }

        return response()->json([
            'data' => $query->limit($this->limit($request, 300))->get()->map(fn ($topic) => [
                'id' => $topic->id,
                'subject_id' => $topic->subject_id,
                'subject_name' => $topic->subject?->name,
                'name' => $topic->name,
                'slug' => $topic->slug ?? null,
                'active' => $topic->active ?? null,
            ]),
        ]);
    }

    public function sourceMaterials(Request $request): JsonResponse
    {
        $query = SourceMaterial::query()->with(['corporation', 'subject'])->orderBy('title');

        if ($request->filled('corporation_id')) {
            $query->where('corporation_id', $request->integer('corporation_id'));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->integer('subject_id'));
        }

        if ($request->filled('active') && Schema::hasColumn('source_materials', 'active')) {
            $query->where('active', $request->boolean('active'));
        }

        return response()->json([
            'data' => $query->limit($this->limit($request, 300))->get()->map(fn ($material) => [
                'id' => $material->id,
                'corporation_id' => $material->corporation_id,
                'corporation_name' => $material->corporation?->name,
                'subject_id' => $material->subject_id,
                'subject_name' => $material->subject?->name,
                'title' => $material->title,
                'slug' => $material->slug,
                'material_type' => $material->material_type ?? null,
                'year' => $material->year ?? null,
                'reference_code' => $material->reference_code ?? null,
                'active' => $material->active ?? null,
            ]),
        ]);
    }

    public function questions(Request $request): JsonResponse
    {
        $query = Question::query()
            ->with(['corporation', 'exam', 'subject', 'topic', 'sourceMaterial', 'alternatives'])
            ->orderByDesc('id');

        $this->applyQuestionFilters($query, $request);

        $paginator = $query->paginate($this->limit($request, 50));

        return response()->json([
            'data' => collect($paginator->items())->map(fn (Question $question) => $this->questionPayload($question, false))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function question(Question $question): JsonResponse
    {
        $question->load(['corporation', 'exam', 'subject', 'topic', 'sourceMaterial', 'alternatives']);

        return response()->json([
            'data' => $this->questionPayload($question, true),
        ]);
    }

    public function duplicateCheck(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'statement' => ['required', 'string', 'min:3'],
            'ignore_question_id' => ['nullable', 'integer'],
        ]);

        $normalized = $this->normalizeStatement($validated['statement']);

        $matches = Question::query()
            ->with(['subject', 'topic'])
            ->when(! empty($validated['ignore_question_id']), fn ($query) => $query->where('id', '!=', (int) $validated['ignore_question_id']))
            ->get()
            ->filter(fn (Question $question) => $this->normalizeStatement($question->statement) === $normalized)
            ->take(20)
            ->values()
            ->map(fn (Question $question) => [
                'id' => $question->id,
                'status' => $question->status,
                'subject_id' => $question->subject_id,
                'subject_name' => $question->subject?->name,
                'topic_id' => $question->topic_id,
                'topic_name' => $question->topic?->name,
                'statement' => Str::limit(strip_tags($question->statement), 300),
            ]);

        return response()->json([
            'normalized_statement' => $normalized,
            'duplicate' => $matches->isNotEmpty(),
            'matches' => $matches,
        ]);
    }

    private function applyQuestionFilters(Builder $query, Request $request): void
    {
        foreach (['corporation_id', 'exam_id', 'subject_id', 'topic_id', 'source_material_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->integer($field));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->string('difficulty'));
        }

        if ($request->filled('source_type')) {
            $query->where('source_type', $request->string('source_type'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->string('q')->toString() . '%';
            $query->where(function (Builder $builder) use ($term) {
                $builder->where('statement', 'like', $term)
                    ->orWhere('commented_answer', 'like', $term)
                    ->orWhere('source_reference', 'like', $term);
            });
        }

        if ($request->boolean('without_comment')) {
            $query->where(function (Builder $builder) {
                $builder->whereNull('commented_answer')->orWhere('commented_answer', '');
            });
        }
    }

    private function questionPayload(Question $question, bool $includeFullText): array
    {
        return [
            'id' => $question->id,
            'corporation' => $this->relationPayload($question->corporation),
            'exam' => $this->relationPayload($question->exam),
            'subject' => $this->relationPayload($question->subject),
            'topic' => $this->relationPayload($question->topic),
            'source_material' => $question->sourceMaterial ? [
                'id' => $question->sourceMaterial->id,
                'title' => $question->sourceMaterial->title,
                'slug' => $question->sourceMaterial->slug,
                'year' => $question->sourceMaterial->year ?? null,
                'reference_code' => $question->sourceMaterial->reference_code ?? null,
            ] : null,
            'question_type' => $question->question_type,
            'difficulty' => $question->difficulty,
            'source_type' => $question->source_type,
            'source_reference' => $question->source_reference,
            'status' => $question->status,
            'correct_letter' => $question->correct_letter,
            'statement' => $includeFullText ? $question->statement : Str::limit(strip_tags($question->statement), 500),
            'commented_answer' => $includeFullText ? $question->commented_answer : Str::limit(strip_tags((string) $question->commented_answer), 500),
            'alternatives' => $question->alternatives->map(fn ($alternative) => [
                'id' => $alternative->id,
                'letter' => $alternative->letter,
                'text' => $alternative->text,
                'is_correct' => (bool) $alternative->is_correct,
            ])->values(),
            'created_at' => optional($question->created_at)->toDateTimeString(),
            'updated_at' => optional($question->updated_at)->toDateTimeString(),
        ];
    }

    private function relationPayload($model): ?array
    {
        if (! $model) {
            return null;
        }

        return [
            'id' => $model->id,
            'name' => $model->name ?? $model->title ?? null,
            'slug' => $model->slug ?? null,
        ];
    }

    private function normalizeStatement(string $statement): string
    {
        $text = html_entity_decode(strip_tags($statement));
        $text = Str::lower($text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        return trim($text);
    }

    private function limit(Request $request, int $default): int
    {
        return min(max((int) $request->integer('per_page', $default), 1), 200);
    }
}
