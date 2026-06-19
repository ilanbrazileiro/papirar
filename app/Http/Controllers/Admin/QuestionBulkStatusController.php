<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class QuestionBulkStatusController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
            'status' => ['required', Rule::in([
                Question::STATUS_DRAFT,
                Question::STATUS_PUBLISHED,
                Question::STATUS_REVIEWED,
                Question::STATUS_ARCHIVED,
            ])],
        ], [
            'question_ids.required' => 'Selecione pelo menos uma questão.',
            'question_ids.min' => 'Selecione pelo menos uma questão.',
            'status.required' => 'Selecione o status desejado.',
        ]);

        $payload = ['status' => $data['status']];

        if (Schema::hasColumn('questions', 'updated_by')) {
            $payload['updated_by'] = auth()->id();
        }

        $total = Question::query()
            ->whereIn('id', $data['question_ids'])
            ->update($payload);

        $message = match ($data['status']) {
            Question::STATUS_DRAFT => "{$total} questão(ões) retornaram para rascunho. Elas não aparecem para o aluno.",
            Question::STATUS_PUBLISHED => "{$total} questão(ões) foram publicadas. Elas aparecem para o aluno e ficam pendentes de revisão editorial.",
            Question::STATUS_REVIEWED => "{$total} questão(ões) foram marcadas como revisadas. Elas aparecem para o aluno e foram validadas editorialmente.",
            Question::STATUS_ARCHIVED => "{$total} questão(ões) foram arquivadas. Elas não aparecem para o aluno.",
            default => "{$total} questão(ões) atualizada(s).",
        };

        return back()->with('success', $message);
    }
}
