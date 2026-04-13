<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionComment;
use Illuminate\Http\RedirectResponse;

class CommentModerationController extends Controller
{
    public function index()
    {
        $comments = QuestionComment::query()
            ->with(['question', 'user'])
            ->whereIn('status', ['pending', 'rejected'])
            ->latest()
            ->paginate(20);

        return view('admin.comments.index', compact('comments'));
    }

    public function approve(QuestionComment $comment): RedirectResponse
    {
        $comment->update(['status' => 'approved']);

        return back()->with('success', 'Comentário aprovado com sucesso.');
    }

    public function reject(QuestionComment $comment): RedirectResponse
    {
        $comment->update(['status' => 'rejected']);

        return back()->with('success', 'Comentário rejeitado com sucesso.');
    }
}
