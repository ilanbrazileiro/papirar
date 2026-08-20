<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QuestionReviewerApiController extends Controller
{
    public function reviewAndPublish(Request $request, Question $question): JsonResponse
    {
        $validated = $request->validate([
            'commented_answer' => ['required', 'string', 'min:80'],
        ]);

        if ($question->status !== Question::STATUS_DRAFT) {
            return response()->json([
                'message' => 'Somente questões em draft podem ser finalizadas pelo GPT Revisor.',
                'question_id' => $question->id,
                'status' => $question->status,
            ], 409);
        }

        $question->update([
            'commented_answer' => trim($validated['commented_answer']),
            'status' => Question::STATUS_PUBLISHED,
        ]);

        Log::info('GPT Revisor publicou questão', [
            'question_id' => $question->id,
            'status' => $question->status,
            'comment_length' => mb_strlen($question->commented_answer),
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 300),
        ]);

        return response()->json([
            'message' => 'Questão revisada e publicada com sucesso.',
            'data' => [
                'id' => $question->id,
                'status' => $question->status,
                'commented_answer' => $question->commented_answer,
                'updated_at' => optional($question->updated_at)->toDateTimeString(),
            ],
        ]);
    }
}
