<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class QuestionBulkStatusController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['required', 'integer', 'distinct', 'exists:questions,id'],
            'status' => ['required', Rule::in([
                Question::STATUS_DRAFT,
                Question::STATUS_PUBLISHED,
                Question::STATUS_REVIEWED,
                Question::STATUS_ARCHIVED,
            ])],
            'redirect_to' => ['nullable', 'string', 'max:2000'],
        ], [
            'question_ids.required' => 'Selecione pelo menos uma questão.',
            'question_ids.array' => 'Selecione pelo menos uma questão válida.',
            'question_ids.min' => 'Selecione pelo menos uma questão.',
            'question_ids.*.exists' => 'Uma ou mais questões selecionadas não foram encontradas.',
            'status.required' => 'Selecione o status desejado.',
            'status.in' => 'Status inválido para ação em massa.',
        ]);

        if ($validator->fails()) {
            return $this->redirectBackToList($request->input('redirect_to'))
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        $questionIds = collect($data['question_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $payload = ['status' => $data['status']];

        if (Schema::hasColumn('questions', 'updated_by')) {
            $payload['updated_by'] = auth()->id();
        }

        $total = Question::query()
            ->whereIn('id', $questionIds)
            ->update($payload);

        if ($total === 0) {
            return $this->redirectBackToList($data['redirect_to'] ?? null)
                ->with('error', 'Nenhuma questão foi atualizada. Recarregue a página e tente novamente.');
        }

        $message = match ($data['status']) {
            Question::STATUS_DRAFT => "{$total} questão(ões) retornaram para rascunho. Elas não aparecem para o aluno.",
            Question::STATUS_PUBLISHED => "{$total} questão(ões) foram publicadas. Elas aparecem para o aluno e ficam pendentes de revisão editorial.",
            Question::STATUS_REVIEWED => "{$total} questão(ões) foram marcadas como revisadas. Elas aparecem para o aluno e foram validadas editorialmente.",
            Question::STATUS_ARCHIVED => "{$total} questão(ões) foram arquivadas. Elas não aparecem para o aluno.",
            default => "{$total} questão(ões) atualizada(s).",
        };

        return $this->redirectBackToList($data['redirect_to'] ?? null)
            ->with('success', $message);
    }

    private function redirectBackToList(?string $redirectTo): RedirectResponse
    {
        $fallback = route('admin.questions.index');

        if (!$redirectTo) {
            return redirect()->to($fallback);
        }

        $path = parse_url($redirectTo, PHP_URL_PATH);

        if (!$path) {
            return redirect()->to($fallback);
        }

        if ($path === '/admin/questions/bulk-status') {
            return redirect()->to($fallback);
        }

        if ($path === '/admin/questions') {
            return redirect()->to($redirectTo);
        }

        return redirect()->to($fallback);
    }
}
