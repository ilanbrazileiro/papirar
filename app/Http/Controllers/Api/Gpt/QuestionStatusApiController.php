<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionStatusApiController extends Controller
{
    public function returnToDraft(Request $request, Question $question): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        if (! in_array($question->status, [Question::STATUS_PUBLISHED, Question::STATUS_REVIEWED], true)) {
            return response()->json([
                'message' => 'Somente questões publicadas ou revisadas podem voltar ao rascunho.',
                'data' => ['id' => $question->id, 'status' => $question->status],
            ], 409);
        }

        $previousStatus = $question->status;
        $question->update(['status' => Question::STATUS_DRAFT]);

        Log::warning('MCP Revisor devolveu questão ao rascunho', [
            'question_id' => $question->id,
            'previous_status' => $previousStatus,
            'new_status' => $question->status,
            'reason' => $data['reason'],
        ]);

        return response()->json([
            'message' => 'Questão devolvida ao rascunho.',
            'data' => ['id' => $question->id, 'previous_status' => $previousStatus, 'status' => $question->status],
        ]);
    }
}
