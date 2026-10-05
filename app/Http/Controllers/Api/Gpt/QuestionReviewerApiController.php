<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuestionReviewerApiController extends Controller
{
    public function updateContent(Request $request, Question $question): JsonResponse
    {
        if ($question->status === Question::STATUS_ARCHIVED) {
            return response()->json(['message' => 'Questões arquivadas não podem ser editadas pelo Revisor.'], 409);
        }

        $data = $request->validate([
            'corporation_id' => ['sometimes', 'nullable', 'integer', 'exists:corporations,id'],
            'exam_id' => ['sometimes', 'nullable', 'integer', 'exists:exams,id'],
            'exam_board_id' => ['sometimes', 'nullable', 'integer', 'exists:exam_boards,id'],
            'source_material_id' => ['sometimes', 'nullable', 'integer', 'exists:source_materials,id'],
            'statement' => ['sometimes', 'string', 'min:5'],
            'question_type' => ['sometimes', 'string', 'max:50'],
            'difficulty' => ['sometimes', Rule::in(['easy', 'medium', 'hard'])],
            'source_type' => ['sometimes', Rule::in(['exam', 'authored', 'adapted'])],
            'source_reference' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'commented_answer' => ['sometimes', 'nullable', 'string'],
            'correct_letter' => ['required_with:alternatives', Rule::in(['A', 'B', 'C', 'D', 'E'])],
            'alternatives' => ['sometimes', 'array', 'min:2', 'max:5'],
            'alternatives.*.letter' => ['required_with:alternatives', Rule::in(['A', 'B', 'C', 'D', 'E']), 'distinct'],
            'alternatives.*.text' => ['required_with:alternatives', 'string', 'min:1'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        if (array_key_exists('statement', $data)) {
            $normalized = Str::of(strip_tags($data['statement']))->squish()->lower()->toString();
            $duplicate = Question::query()
                ->whereKeyNot($question->id)
                ->get(['id', 'statement'])
                ->first(fn (Question $candidate) => Str::of(strip_tags((string) $candidate->statement))->squish()->lower()->toString() === $normalized);

            if ($duplicate) {
                return response()->json([
                    'message' => 'O enunciado revisado coincide com outra questão existente.',
                    'duplicate_question_id' => $duplicate->id,
                ], 409);
            }
        }

        if (isset($data['alternatives'])) {
            $letters = collect($data['alternatives'])->pluck('letter');
            if (! $letters->contains($data['correct_letter'])) {
                throw ValidationException::withMessages([
                    'correct_letter' => ['A alternativa correta precisa existir no conjunto de alternativas enviado.'],
                ]);
            }
        }

        $before = $this->snapshot($question);

        DB::transaction(function () use ($question, $data) {
            $fields = collect($data)->except(['alternatives', 'correct_letter', 'reason'])->all();
            foreach (['statement', 'source_reference', 'commented_answer'] as $field) {
                if (array_key_exists($field, $fields) && is_string($fields[$field])) {
                    $fields[$field] = trim($fields[$field]);
                }
            }

            if ($fields !== []) {
                $question->update($fields);
            }

            if (isset($data['alternatives'])) {
                $question->alternatives()->delete();
                foreach ($data['alternatives'] as $alternative) {
                    $question->alternatives()->create([
                        'letter' => $alternative['letter'],
                        'text' => trim($alternative['text']),
                        'is_correct' => $alternative['letter'] === $data['correct_letter'],
                    ]);
                }
            }
        });

        $question->refresh()->load('alternatives');

        Log::info('GPT Revisor atualizou conteúdo da questão', [
            'question_id' => $question->id,
            'before' => $before,
            'after' => $this->snapshot($question),
            'reason' => $data['reason'],
        ]);

        return response()->json([
            'message' => 'Conteúdo da questão atualizado com sucesso.',
            'data' => $this->snapshot($question),
        ]);
    }

    public function finalizeReview(Request $request, Question $question): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        if (! in_array($question->status, [Question::STATUS_DRAFT, Question::STATUS_PUBLISHED], true)) {
            return response()->json([
                'message' => 'Somente questões em draft ou published podem ser finalizadas como revisadas.',
                'question_id' => $question->id,
                'status' => $question->status,
            ], 409);
        }

        $question->load('alternatives');
        if (blank($question->commented_answer) || mb_strlen(trim(strip_tags((string) $question->commented_answer))) < 80) {
            return response()->json(['message' => 'A revisão exige comentário didático com pelo menos 80 caracteres.'], 422);
        }

        if ($question->alternatives->count() < 2 || $question->alternatives->where('is_correct', true)->count() !== 1) {
            return response()->json(['message' => 'A questão precisa possuir ao menos duas alternativas e exatamente uma correta.'], 422);
        }

        if (! $question->subject_id || ! $question->topic_id) {
            return response()->json(['message' => 'A questão precisa estar classificada em disciplina e tópico antes da revisão final.'], 422);
        }

        $topic = Topic::find($question->topic_id);
        if (! $topic || (int) $topic->subject_id !== (int) $question->subject_id) {
            return response()->json(['message' => 'O tópico atual não pertence à disciplina da questão. Corrija a classificação antes de finalizar.'], 422);
        }

        $question->update(['status' => Question::STATUS_REVIEWED]);

        Log::info('GPT Revisor finalizou revisão', [
            'question_id' => $question->id,
            'status' => $question->status,
            'reason' => $data['reason'],
        ]);

        return response()->json([
            'message' => 'Questão revisada e marcada como reviewed.',
            'data' => ['id' => $question->id, 'status' => $question->status],
        ]);
    }

    public function archive(Request $request, Question $question): JsonResponse
    {
        $data = $request->validate([
            'confirm' => ['required', 'accepted'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        if ($question->status === Question::STATUS_ARCHIVED) {
            return response()->json(['message' => 'A questão já está arquivada.', 'data' => ['id' => $question->id, 'status' => $question->status]]);
        }

        $before = $question->status;
        $question->update(['status' => Question::STATUS_ARCHIVED]);

        Log::warning('GPT Revisor arquivou questão', [
            'question_id' => $question->id,
            'before_status' => $before,
            'reason' => $data['reason'],
        ]);

        return response()->json([
            'message' => 'Questão arquivada.',
            'data' => ['id' => $question->id, 'status' => $question->status],
        ]);
    }

    /** @deprecated Compatibilidade temporária com a API anterior. */
    public function reviewAndPublish(Request $request, Question $question): JsonResponse
    {
        $validated = $request->validate([
            'statement' => ['sometimes', 'string', 'min:5'],
            'commented_answer' => ['required', 'string', 'min:80'],
        ]);

        if ($question->status !== Question::STATUS_DRAFT) {
            return response()->json([
                'message' => 'Somente questões em draft podem ser finalizadas pelo endpoint legado.',
                'question_id' => $question->id,
                'status' => $question->status,
            ], 409);
        }

        $update = [
            'commented_answer' => trim($validated['commented_answer']),
            'status' => Question::STATUS_PUBLISHED,
        ];

        if (array_key_exists('statement', $validated)) {
            $update['statement'] = trim($validated['statement']);
        }

        $question->update($update);

        Log::info('GPT Revisor publicou questão via endpoint legado', [
            'question_id' => $question->id,
            'status' => $question->status,
        ]);

        return response()->json([
            'message' => 'Questão revisada e publicada pelo endpoint legado.',
            'data' => [
                'id' => $question->id,
                'status' => $question->status,
                'statement' => $question->statement,
                'commented_answer' => $question->commented_answer,
                'updated_at' => optional($question->updated_at)->toDateTimeString(),
            ],
        ]);
    }

    private function snapshot(Question $question): array
    {
        $question->loadMissing('alternatives');
        $correct = $question->alternatives->firstWhere('is_correct', true);

        return [
            'id' => $question->id,
            'status' => $question->status,
            'corporation_id' => $question->corporation_id,
            'exam_id' => $question->exam_id,
            'exam_board_id' => $question->exam_board_id,
            'subject_id' => $question->subject_id,
            'topic_id' => $question->topic_id,
            'source_material_id' => $question->source_material_id,
            'statement' => $question->statement,
            'question_type' => $question->question_type,
            'difficulty' => $question->difficulty,
            'source_type' => $question->source_type,
            'source_reference' => $question->source_reference,
            'commented_answer' => $question->commented_answer,
            'correct_letter' => $correct?->letter,
            'alternatives' => $question->alternatives->map(fn ($alternative) => [
                'letter' => $alternative->letter,
                'text' => $alternative->text,
                'is_correct' => (bool) $alternative->is_correct,
            ])->values(),
        ];
    }
}
