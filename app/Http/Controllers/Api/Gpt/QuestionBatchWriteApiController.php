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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuestionBatchWriteApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $envelope = $request->validate([
            'questions' => ['required', 'array', 'min:1', 'max:20'],
        ]);

        $results = [];
        $createdIds = [];
        $duplicateIds = [];
        $errorIndexes = [];

        foreach ($envelope['questions'] as $index => $rawQuestion) {
            try {
                $validated = $this->validateQuestion((array) $rawQuestion);
                $this->validateRelations($validated);
                $this->validateCorrectLetter($validated);

                $duplicate = $this->findDuplicate($validated['statement']);

                if ($duplicate && !($validated['allow_duplicate'] ?? false)) {
                    $duplicateIds[] = $duplicate->id;
                    $results[] = [
                        'index' => $index,
                        'status' => 'duplicate',
                        'created' => false,
                        'existing_question_id' => $duplicate->id,
                        'message' => 'Questão duplicada. Cadastro ignorado.',
                    ];
                    continue;
                }

                $question = $this->createQuestion($validated);
                $createdIds[] = $question->id;

                $results[] = [
                    'index' => $index,
                    'status' => 'created',
                    'created' => true,
                    'question_id' => $question->id,
                    'question_status' => $question->status,
                    'subject_id' => $question->subject_id,
                    'topic_id' => $question->topic_id,
                    'source_type' => $question->source_type,
                    'message' => 'Questão criada como draft.',
                ];
            } catch (ValidationException $e) {
                $errorIndexes[] = $index;
                $results[] = [
                    'index' => $index,
                    'status' => 'validation_error',
                    'created' => false,
                    'message' => 'Questão inválida.',
                    'errors' => $e->errors(),
                ];
            } catch (\Throwable $e) {
                $errorIndexes[] = $index;

                Log::error('Erro no cadastro em lote via GPT API', [
                    'index' => $index,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'ip' => $request->ip(),
                ]);

                $results[] = [
                    'index' => $index,
                    'status' => 'error',
                    'created' => false,
                    'message' => 'Erro interno ao processar esta questão.',
                ];
            }
        }

        Log::info('GPT API processou lote de questões', [
            'received' => count($envelope['questions']),
            'created' => count($createdIds),
            'duplicates' => count($duplicateIds),
            'errors' => count($errorIndexes),
            'created_ids' => $createdIds,
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 300),
        ]);

        return response()->json([
            'message' => 'Lote processado.',
            'summary' => [
                'received' => count($envelope['questions']),
                'created' => count($createdIds),
                'duplicates' => count($duplicateIds),
                'errors' => count($errorIndexes),
            ],
            'created_ids' => $createdIds,
            'duplicate_question_ids' => array_values(array_unique($duplicateIds)),
            'error_indexes' => $errorIndexes,
            'results' => $results,
        ]);
    }

    private function validateQuestion(array $data): array
    {
        $validator = Validator::make($data, [
            'corporation_id' => ['nullable', 'integer', 'exists:corporations,id'],
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'exam_board_id' => ['nullable', 'integer', 'exists:exam_boards,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'topic_id' => ['required', 'integer', 'exists:topics,id'],
            'source_material_id' => ['nullable', 'integer', 'exists:source_materials,id'],
            'statement' => ['required', 'string', 'min:5'],
            'question_type' => ['required', 'string', 'max:50'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'source_type' => ['required', Rule::in(['exam', 'authored', 'adapted'])],
            'source_reference' => ['nullable', 'string'],
            'commented_answer' => ['nullable', 'string'],
            'correct_letter' => ['required', Rule::in(['A', 'B', 'C', 'D', 'E'])],
            'alternatives' => ['required', 'array', 'min:2', 'max:5'],
            'alternatives.*.letter' => ['required', Rule::in(['A', 'B', 'C', 'D', 'E']), 'distinct'],
            'alternatives.*.text' => ['required', 'string', 'min:1'],
            'allow_duplicate' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    private function validateCorrectLetter(array $data): void
    {
        $letters = collect($data['alternatives'])
            ->pluck('letter')
            ->map(fn ($value) => strtoupper((string) $value));

        if (! $letters->contains(strtoupper($data['correct_letter']))) {
            throw ValidationException::withMessages([
                'correct_letter' => ['A alternativa correta precisa existir em alternatives.'],
            ]);
        }
    }

    private function validateRelations(array $data): void
    {
        $topic = Topic::findOrFail($data['topic_id']);

        if ((int) $topic->subject_id !== (int) $data['subject_id']) {
            throw ValidationException::withMessages([
                'topic_id' => ['O tópico não pertence à disciplina informada.'],
            ]);
        }

        if (!empty($data['exam_id']) && !empty($data['corporation_id'])) {
            $exam = Exam::findOrFail($data['exam_id']);
            if ($exam->corporation_id !== null &&
                (int) $exam->corporation_id !== (int) $data['corporation_id']) {
                throw ValidationException::withMessages([
                    'exam_id' => ['A prova não pertence à corporação informada.'],
                ]);
            }
        }

        if (!empty($data['source_material_id'])) {
            $source = SourceMaterial::findOrFail($data['source_material_id']);

            if ($source->subject_id !== null &&
                (int) $source->subject_id !== (int) $data['subject_id']) {
                throw ValidationException::withMessages([
                    'source_material_id' => ['A fonte não pertence à disciplina informada.'],
                ]);
            }

            if (!empty($data['corporation_id']) &&
                $source->corporation_id !== null &&
                (int) $source->corporation_id !== (int) $data['corporation_id']) {
                throw ValidationException::withMessages([
                    'source_material_id' => ['A fonte não pertence à corporação informada.'],
                ]);
            }
        }
    }

    private function findDuplicate(string $statement): ?Question
    {
        $normalized = $this->normalize($statement);

        return Question::query()
            ->get(['id', 'statement'])
            ->first(fn (Question $question) => $this->normalize($question->statement) === $normalized);
    }

    private function createQuestion(array $data): Question
    {
        return DB::transaction(function () use ($data) {
            $question = Question::create([
                'corporation_id' => $data['corporation_id'] ?? null,
                'exam_id' => $data['exam_id'] ?? null,
                'exam_board_id' => $data['exam_board_id'] ?? null,
                'subject_id' => $data['subject_id'],
                'topic_id' => $data['topic_id'],
                'source_material_id' => $data['source_material_id'] ?? null,
                'statement' => $data['statement'],
                'question_type' => $data['question_type'],
                'difficulty' => $data['difficulty'],
                'source_type' => $data['source_type'],
                'source_reference' => $data['source_reference'] ?? null,
                'commented_answer' => $data['commented_answer'] ?? null,
                'status' => Question::STATUS_DRAFT,
                'created_by' => null,
            ]);

            foreach ($data['alternatives'] as $alternative) {
                Alternative::create([
                    'question_id' => $question->id,
                    'letter' => strtoupper($alternative['letter']),
                    'text' => $alternative['text'],
                    'is_correct' => strtoupper($alternative['letter']) === strtoupper($data['correct_letter']),
                ]);
            }

            return $question;
        });
    }

    private function normalize(string $statement): string
    {
        $text = html_entity_decode(strip_tags($statement));
        $text = Str::lower($text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        return trim($text);
    }
}
