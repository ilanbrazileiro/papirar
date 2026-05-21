<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;

class QuestionPreviewController extends Controller
{
    public function __invoke(Question $question)
    {
        $question->load([
            'corporation',
            'exam',
            'subject',
            'topic',
            'alternatives' => fn ($query) => $query->orderBy('letter'),
        ]);

        return view('admin.questions.preview', compact('question'));
    }
}
