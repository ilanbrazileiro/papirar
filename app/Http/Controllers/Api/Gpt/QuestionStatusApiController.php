<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionStatusApiController extends Controller
{
    public function returnToDraft(Request $request, int $question): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $item = Question::query()->findOrFail($question);
        if (! in_array($item->status, [Question::STATUS_PUBLISHED, Question::STATUS_REVIEWED], true)) {
            return response()->json([
                'message' => 'Somente questões publicadas ou revisadas podem voltar ao rascunho.',
                'data' => ['id' => $item->id, 'status' => $item->status],
            ], 409);
        }

        $previousStatus = $item->status;
        $item->update(['status' => Question::STATUS_DRAFT]);
        Log::warning('MCP Revisor devolveu questão ao rascunho', [
            'question_id' => $item->id,
            'previous_status' => $previousStatus,
            'new_status' => $item->status,
            'reason' => $data['reason'],
        ]);

        return response()->json([
            'message' => 'Questão devolvida ao rascunho.',
            'data' => ['id' => $item->id, 'previous_status' => $previousStatus, 'status' => $item->status],
        ]);
    }
}
