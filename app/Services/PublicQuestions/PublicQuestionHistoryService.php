<?php

namespace App\Services\PublicQuestions;

use App\Models\Alternative;
use App\Models\Question;
use App\Models\User;
use App\Models\UserAnswer;
use Illuminate\Http\Request;

class PublicQuestionHistoryService
{
    public function record(
        Request $request,
        Question $question,
        Alternative $alternative,
        ?User $user = null
    ): void {
        $history = $this->history($request);
        $questionKey = (string) $question->id;

        if (! array_key_exists($questionKey, $history)) {
            $history[$questionKey] = [
                'question_id' => (int) $question->id,
                'selected_alternative_id' => (int) $alternative->id,
                'is_correct' => (bool) $alternative->is_correct,
                'answered_at' => now()->toDateTimeString(),
            ];

            $request->session()->put('public_question_history', $history);

            $results = $request->session()->get('public_question_results', []);
            $results = is_array($results) ? $results : [];
            $results[$questionKey] = (bool) $alternative->is_correct;
            $request->session()->put('public_question_results', $results);
        }

        if ($user) {
            $this->persist($user, $history[$questionKey]);
        }
    }

    public function sync(Request $request, User $user): int
    {
        $synced = 0;

        foreach ($this->history($request) as $answer) {
            if ($this->persist($user, $answer)) {
                $synced++;
            }
        }

        return $synced;
    }

    /**
     * @return array<string, array{question_id:int, selected_alternative_id:int, is_correct:bool, answered_at:string}>
     */
    private function history(Request $request): array
    {
        $history = $request->session()->get('public_question_history', []);

        if (! is_array($history)) {
            return [];
        }

        $normalized = [];

        foreach ($history as $answer) {
            if (! is_array($answer)) {
                continue;
            }

            $questionId = (int) ($answer['question_id'] ?? 0);
            $alternativeId = (int) ($answer['selected_alternative_id'] ?? 0);

            if ($questionId <= 0 || $alternativeId <= 0) {
                continue;
            }

            $normalized[(string) $questionId] = [
                'question_id' => $questionId,
                'selected_alternative_id' => $alternativeId,
                'is_correct' => (bool) ($answer['is_correct'] ?? false),
                'answered_at' => (string) ($answer['answered_at'] ?? now()->toDateTimeString()),
            ];
        }

        return $normalized;
    }

    private function persist(User $user, array $answer): bool
    {
        $alternativeExists = Alternative::query()
            ->whereKey($answer['selected_alternative_id'])
            ->where('question_id', $answer['question_id'])
            ->exists();

        if (! $alternativeExists) {
            return false;
        }

        $record = UserAnswer::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'question_id' => $answer['question_id'],
                'study_session_id' => null,
            ],
            [
                'selected_alternative_id' => $answer['selected_alternative_id'],
                'is_correct' => $answer['is_correct'],
                'answered_at' => $answer['answered_at'],
            ]
        );

        return $record->wasRecentlyCreated;
    }
}
