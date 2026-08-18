<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TaxonomyWriteApiController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'topics' => ['nullable', 'array', 'max:50'],
            'topics.*' => ['required', 'string', 'max:150'],
        ]);

        $subjectName = trim($validated['subject']);
        $subject = $this->findSubjectByNormalizedName($subjectName);

        $subjectSuggestions = Subject::query()
            ->where('name', 'like', '%' . $subjectName . '%')
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name'])
            ->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])
            ->values();

        $topicResults = collect($validated['topics'] ?? [])->map(function (string $topicName) use ($subject) {
            $topicName = trim($topicName);

            $exact = null;
            $suggestions = collect();

            if ($subject) {
                $exact = $this->findTopicByNormalizedName($subject->id, $topicName);

                $suggestions = Topic::query()
                    ->where('subject_id', $subject->id)
                    ->where('name', 'like', '%' . $topicName . '%')
                    ->orderBy('name')
                    ->limit(10)
                    ->get(['id', 'subject_id', 'name'])
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'subject_id' => $item->subject_id,
                        'name' => $item->name,
                    ])
                    ->values();
            }

            return [
                'name' => $topicName,
                'exists' => (bool) $exact,
                'existing_id' => $exact?->id,
                'suggestions' => $suggestions,
            ];
        })->values();

        return response()->json([
            'subject' => [
                'name' => $subjectName,
                'exists' => (bool) $subject,
                'existing_id' => $subject?->id,
                'suggestions' => $subjectSuggestions,
            ],
            'topics' => $topicResults,
            'requires_creation' => ! $subject || $topicResults->contains(fn ($item) => ! $item['exists']),
        ]);
    }

    public function storeSubject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'scope' => ['nullable', Rule::in(['general', 'corporation_specific'])],
            'active' => ['nullable', 'boolean'],
        ]);

        $name = trim($validated['name']);
        $existing = $this->findSubjectByNormalizedName($name);

        if ($existing) {
            return response()->json([
                'message' => 'Disciplina já cadastrada.',
                'duplicate' => true,
                'data' => [
                    'id' => $existing->id,
                    'name' => $existing->name,
                    'slug' => $existing->slug,
                ],
            ], 409);
        }

        $data = [
            'name' => $name,
            'slug' => $this->uniqueSubjectSlug(Str::slug($name)),
            'description' => $validated['description'] ?? null,
            'active' => $validated['active'] ?? true,
        ];

        if (Schema::hasColumn('subjects', 'scope')) {
            $data['scope'] = $validated['scope'] ?? Subject::SCOPE_GENERAL;
        }

        $subject = Subject::create($data);

        Log::info('GPT API criou disciplina', [
            'subject_id' => $subject->id,
            'name' => $subject->name,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Disciplina criada com sucesso.',
            'data' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'slug' => $subject->slug,
                'active' => (bool) $subject->active,
                'scope' => $subject->scope ?? null,
            ],
        ], 201);
    }

    public function storeTopic(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);

        $name = trim($validated['name']);
        $subjectId = (int) $validated['subject_id'];
        $existing = $this->findTopicByNormalizedName($subjectId, $name);

        if ($existing) {
            return response()->json([
                'message' => 'Tópico já cadastrado nesta disciplina.',
                'duplicate' => true,
                'data' => [
                    'id' => $existing->id,
                    'subject_id' => $existing->subject_id,
                    'name' => $existing->name,
                    'slug' => $existing->slug,
                ],
            ], 409);
        }

        $topic = Topic::create([
            'subject_id' => $subjectId,
            'name' => $name,
            'slug' => $this->uniqueTopicSlug($subjectId, Str::slug($name)),
            'description' => $validated['description'] ?? null,
            'active' => $validated['active'] ?? true,
        ]);

        Log::info('GPT API criou tópico', [
            'topic_id' => $topic->id,
            'subject_id' => $topic->subject_id,
            'name' => $topic->name,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Tópico criado com sucesso.',
            'data' => [
                'id' => $topic->id,
                'subject_id' => $topic->subject_id,
                'name' => $topic->name,
                'slug' => $topic->slug,
                'active' => (bool) $topic->active,
            ],
        ], 201);
    }

    private function findSubjectByNormalizedName(string $name): ?Subject
    {
        $normalized = Str::lower(trim($name));

        return Subject::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
            ->first();
    }

    private function findTopicByNormalizedName(int $subjectId, string $name): ?Topic
    {
        $normalized = Str::lower(trim($name));

        return Topic::query()
            ->where('subject_id', $subjectId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
            ->first();
    }

    private function uniqueSubjectSlug(string $base): string
    {
        $base = $base !== '' ? $base : 'disciplina';
        $slug = $base;
        $counter = 2;

        while (Subject::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    private function uniqueTopicSlug(int $subjectId, string $base): string
    {
        $base = $base !== '' ? $base : 'topico';
        $slug = $base;
        $counter = 2;

        while (Topic::query()->where('subject_id', $subjectId)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
