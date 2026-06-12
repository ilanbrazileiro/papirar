<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Questions\QuestionDuplicateChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionDuplicateController extends Controller
{
    public function __invoke(Request $request, QuestionDuplicateChecker $checker): JsonResponse
    {
        $validated = $request->validate([
            'statement' => ['required', 'string'],
            'question_id' => ['nullable', 'integer', 'exists:questions,id'],
        ]);

        $duplicates = $checker->findExactDuplicates(
            $validated['statement'],
            $validated['question_id'] ?? null,
            10
        );

        return response()->json([
            'has_duplicates' => $duplicates->isNotEmpty(),
            'duplicates' => $duplicates->map(function ($question) {
                return [
                    'id' => $question->id,
                    'status' => $question->status,
                    'difficulty' => $question->difficulty,
                    'subject' => optional($question->subject)->name,
                    'topic' => optional($question->topic)->name,
                    'corporation' => optional($question->corporation)->name,
                    'exam' => optional($question->exam)->title,
                    'statement_preview' => str($question->statement)->stripTags()->limit(180)->toString(),
                    'edit_url' => route('admin.questions.edit', $question),
                    'show_url' => route('admin.questions.show', $question),
                ];
            })->values(),
        ]);
    }
}
