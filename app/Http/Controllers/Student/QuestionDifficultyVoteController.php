<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionDifficultyVote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionDifficultyVoteController extends Controller
{
    public function store(Request $request, Question $question): RedirectResponse
    {
        $data = $request->validate([
            'difficulty_vote' => ['required', 'in:easy,medium,hard'],
        ]);

        QuestionDifficultyVote::query()->updateOrCreate(
            [
                'question_id' => $question->id,
                'user_id' => Auth::id(),
            ],
            [
                'difficulty_vote' => $data['difficulty_vote'],
            ]
        );

        return back()->with('status', 'Voto de dificuldade registrado com sucesso.');
    }
}
