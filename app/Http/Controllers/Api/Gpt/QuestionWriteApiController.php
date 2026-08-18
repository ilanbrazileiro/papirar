<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Alternative;
use App\Models\Exam;
use App\Models\Question;
use App\Models\SourceMaterial;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuestionWriteApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'corporation_id' => ['nullable', 'integer', 'exists:corporations,id'],
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'exam_board_id' => ['nullable', 'integer', 'exists:exam_boards,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'topic_id' => ['required', 'integer', 'exists:topics,id'],
            'source_material_id' => ['nullable', 'integer', 'exists:source_materials,id'],
            'statement' => ['required', 'string', 'min:5'],
            'question_type' => ['required', 'string', 'max:50'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'source_type' => ['nullable', 'string', 'max:100'],
            'source_reference' => ['nullable', 'string'],
            'commented_answer' => ['nullable', 'string'],
            'correct_letter' => ['required', Rule::in(['A', 'B', 'C', 'D', 'E'])],
            'alternatives' => ['required', 'array', 'min:2', 'max:5'],
            'alternatives.*.letter' => ['required', Rule::in(['A', 'B', 'C', 'D', 'E']), 'distinct'],
            'alternatives.*.text' => ['required', 'string', 'min:1'],
            'allow_duplicate' => ['sometimes', 'boolean'],
        ]);

        $this->validateRelations($validated);

        $letters = collect($validated['alternatives'])->pluck('letter')->map(fn ($v) => strtoupper((string) $v));
        if (! $letters->contains($validated['correct_letter'])) {
            throw ValidationException::withMessages([
                'correct_letter' => ['A alternativa correta precisa existir em alternatives.'],
            ]);
        }

        $normalized = $this->normalize($validated['statement']);
        $duplicate = Question::query()->get(['id', 'statement'])
            ->first(fn (Question $q) => $this->normalize($q->statement) === $normalized);

        if ($duplicate && !($validated['allow_duplicate'] ?? false)) {
            return response()->json([
                'message' => 'Questão duplicada. Cadastro bloqueado.',
                'duplicate' => true,
                'existing_question_id' => $duplicate->id,
            ], 409);
        }

        $question = DB::transaction(function () use ($validated) {
            $question = Question::create([
                'corporation_id' => $validated['corporation_id'] ?? null,
                'exam_id' => $validated['exam_id'] ?? null,
                'exam_board_id' => $validated['exam_board_id'] ?? null,
                'subject_id' => $validated['subject_id'],
                'topic_id' => $validated['topic_id'],
                'source_material_id' => $validated['source_material_id'] ?? null,
                'statement' => $validated['statement'],
                'question_type' => $validated['question_type'],
                'difficulty' => $validated['difficulty'],
                'source_type' => $validated['source_type'] ?? null,
                'source_reference' => $validated['source_reference'] ?? null,
                'commented_answer' => $validated['commented_answer'] ?? null,
                'status' => Question::STATUS_DRAFT,
                'created_by' => null,
            ]);

            foreach ($validated['alternatives'] as $alternative) {
                Alternative::create([
                    'question_id' => $question->id,
                    'letter' => strtoupper($alternative['letter']),
                    'text' => $alternative['text'],
                    'is_correct' => strtoupper($alternative['letter']) === $validated['correct_letter'],
                ]);
            }

            return $question;
        });

        Log::info('GPT API criou questão em draft', [
            'question_id' => $question->id,
            'corporation_id' => $question->corporation_id,
            'exam_id' => $question->exam_id,
            'exam_board_id' => $question->exam_board_id,
            'subject_id' => $question->subject_id,
            'topic_id' => $question->topic_id,
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 300),
        ]);

        $question->load(['corporation', 'exam', 'examBoard', 'subject', 'topic', 'sourceMaterial', 'alternatives']);

        return response()->json([
            'message' => 'Questão criada como draft.',
            'data' => [
                'id' => $question->id,
                'status' => $question->status,
                'corporation_id' => $question->corporation_id,
                'exam_id' => $question->exam_id,
                'exam_board_id' => $question->exam_board_id,
                'subject_id' => $question->subject_id,
                'topic_id' => $question->topic_id,
                'difficulty' => $question->difficulty,
                'statement' => $question->statement,
                'commented_answer' => $question->commented_answer,
                'correct_letter' => $question->alternatives->firstWhere('is_correct', true)?->letter,
                'alternatives' => $question->alternatives->map(fn ($a) => [
                    'id' => $a->id,
                    'letter' => $a->letter,
                    'text' => $a->text,
                    'is_correct' => (bool) $a->is_correct,
                ])->values(),
            ],
        ], 201);
    }

    private function validateRelations(array $data): void
    {
        $topic = Topic::findOrFail($data['topic_id']);
        if ((int) $topic->subject_id !== (int) $data['subject_id']) {
            throw ValidationException::withMessages(['topic_id' => ['O tópico não pertence à disciplina informada.']]);
        }

        if (!empty($data['exam_id']) && !empty($data['corporation_id'])) {
            $exam = Exam::findOrFail($data['exam_id']);
            if ($exam->corporation_id !== null && (int) $exam->corporation_id !== (int) $data['corporation_id']) {
                throw ValidationException::withMessages(['exam_id' => ['A prova não pertence à corporação informada.']]);
            }
        }

        if (!empty($data['source_material_id'])) {
            $source = SourceMaterial::findOrFail($data['source_material_id']);
            if ($source->subject_id !== null && (int) $source->subject_id !== (int) $data['subject_id']) {
                throw ValidationException::withMessages(['source_material_id' => ['A fonte não pertence à disciplina informada.']]);
            }
            if (!empty($data['corporation_id']) && $source->corporation_id !== null && (int) $source->corporation_id !== (int) $data['corporation_id']) {
                throw ValidationException::withMessages(['source_material_id' => ['A fonte não pertence à corporação informada.']]);
            }
        }
    }

    private function normalize(string $statement): string
    {
        $text = html_entity_decode(strip_tags($statement));
        $text = Str::lower($text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        return trim($text);
    }
}
