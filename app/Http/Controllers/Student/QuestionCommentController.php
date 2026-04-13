<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionCommentController extends Controller
{
    public function store(Request $request, Question $question): RedirectResponse
    {
        $data = $request->validate([
            'comment' => ['required', 'string', 'min:3', 'max:5000'],
        ]);

        QuestionComment::query()->create([
            'question_id' => $question->id,
            'user_id' => Auth::id(),
            'comment' => $data['comment'],
            'status' => 'pending',
        ]);

        return back()->with('status', 'Comentário enviado para moderação.');
    }

    public function update(Request $request, Question $question, QuestionComment $comment): RedirectResponse
    {
        abort_unless((int) $comment->question_id === (int) $question->id, 404);
        abort_unless((int) $comment->user_id === (int) Auth::id(), 403);
        abort_if($comment->status === 'approved', 422, 'Comentário aprovado não pode ser alterado.');

        $data = $request->validate([
            'comment' => ['required', 'string', 'min:3', 'max:5000'],
        ]);

        $comment->update([
            'comment' => $data['comment'],
            'status' => 'pending',
        ]);

        return back()->with('status', 'Comentário atualizado e reenviado para moderação.');
    }
}
