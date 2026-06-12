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
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
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

        $label = match ($data['status']) {
            'draft' => 'rascunho',
            'published' => 'publicada',
            'archived' => 'arquivada',
        };

        return back()->with('success', "{$total} questão(ões) marcada(s) como {$label}.");
    }
}
