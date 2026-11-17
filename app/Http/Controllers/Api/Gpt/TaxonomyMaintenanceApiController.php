<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Alterações individuais e conservadoras na taxonomia via MCP.
 * Renomear não muda o slug já publicado; mudança de disciplina de um tópico
 * continua no fluxo dedicado moveTopic, com confirmação explícita.
 */
class TaxonomyMaintenanceApiController extends Controller
{
    public function updateSubject(Request $request, Subject $subject): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string'],
            'scope' => ['sometimes', Rule::in([Subject::SCOPE_GENERAL, Subject::SCOPE_CORPORATION_SPECIFIC])],
            'active' => ['sometimes', 'boolean'],
            'confirm' => ['sometimes', 'boolean'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);
            if ($data['name'] === '' || Subject::query()->whereKeyNot($subject->getKey())
                ->whereRaw('LOWER(TRIM(name)) = ?', [Str::lower($data['name'])])->exists()) {
                throw ValidationException::withMessages(['name' => ['Nome inválido ou disciplina já existente.']]);
            }
        }

        $this->requireConfirmationForDeactivation($request, $data);
        $changes = collect($data)->except(['reason', 'confirm'])->all();
        if ($changes === []) {
            throw ValidationException::withMessages(['fields' => ['Informe pelo menos um campo para atualizar.']]);
        }

        $before = $subject->only(array_keys($changes));
        $subject->update($changes);
        Log::info('MCP atualizou disciplina', [
            'subject_id' => $subject->getKey(),
            'before' => $before,
            'changes' => $changes,
            'reason' => $data['reason'],
        ]);

        return response()->json(['data' => $subject->fresh()]);
    }

    public function updateTopic(Request $request, Topic $topic): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
            'confirm' => ['sometimes', 'boolean'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);
            if ($data['name'] === '' || Topic::query()->where('subject_id', $topic->subject_id)
                ->whereKeyNot($topic->getKey())
                ->whereRaw('LOWER(TRIM(name)) = ?', [Str::lower($data['name'])])->exists()) {
                throw ValidationException::withMessages(['name' => ['Nome inválido ou tópico já existente nesta disciplina.']]);
            }
        }

        $this->requireConfirmationForDeactivation($request, $data);
        $changes = collect($data)->except(['reason', 'confirm'])->all();
        if ($changes === []) {
            throw ValidationException::withMessages(['fields' => ['Informe pelo menos um campo para atualizar.']]);
        }

        $before = $topic->only(array_keys($changes));
        $topic->update($changes);
        Log::info('MCP atualizou tópico', [
            'topic_id' => $topic->getKey(),
            'subject_id' => $topic->subject_id,
            'before' => $before,
            'changes' => $changes,
            'reason' => $data['reason'],
        ]);

        return response()->json(['data' => $topic->fresh()]);
    }

    private function requireConfirmationForDeactivation(Request $request, array $data): void
    {
        if (array_key_exists('active', $data) && $data['active'] === false) {
            $request->validate(['confirm' => ['required', 'accepted']]);
        }
    }
}
