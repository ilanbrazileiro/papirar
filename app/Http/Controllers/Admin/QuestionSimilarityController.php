<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\Questions\QuestionSimilarityService;
use Illuminate\Http\Request;

class QuestionSimilarityController extends Controller
{
    public function index(Request $request, QuestionSimilarityService $similarityService)
    {
        $filters = $request->validate([
            'question_id' => ['nullable', 'integer', 'exists:questions,id'],
            'text' => ['nullable', 'string', 'max:10000'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'status' => ['nullable', 'in:draft,published,reviewed,archived'],
            'scope' => ['nullable', 'in:same_topic,same_subject,all'],
            'min_score' => ['nullable', 'integer', 'min:20', 'max:100'],
        ]);

        $baseQuestion = null;
        $results = [];
        $searched = $request->hasAny(['question_id', 'text']);

        $filters['scope'] = $filters['scope'] ?? 'same_subject';
        $filters['min_score'] = $filters['min_score'] ?? 65;

        if (!empty($filters['question_id'])) {
            $baseQuestion = Question::with(['subject', 'topic'])->findOrFail($filters['question_id']);
            $results = $similarityService->findSimilarToQuestion($baseQuestion, $filters);
        } elseif (!empty($filters['text'])) {
            $results = $similarityService->findSimilarText($filters['text'], $filters);
        }

        return view('admin.questions.similar.index', [
            'subjects' => Subject::orderBy('name')->get(['id', 'name']),
            'topics' => Topic::orderBy('name')->get(['id', 'subject_id', 'name']),
            'baseQuestion' => $baseQuestion,
            'results' => $results,
            'searched' => $searched,
            'filters' => $filters,
        ]);
    }
}
