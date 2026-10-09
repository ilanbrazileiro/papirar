<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\CourseAccess;
use App\Models\PaymentTransaction;
use App\Models\StudySession;
use App\Models\User;
use App\Models\UserAnswer;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingEngagementApiController extends Controller
{
    /**
     * Coorte de alunos cadastrados. A observação é feita por N dias completos
     * após cada cadastro, evitando contabilizar alunos com trial ainda em curso
     * como se já tivessem abandonado o produto.
     */
    public function cohort(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'course_id' => ['nullable', 'integer', 'min:1', 'exists:courses,id'],
            'acquisition_source' => ['nullable', 'string', 'max:100'],
            'acquisition_campaign' => ['nullable', 'string', 'max:200'],
            'observation_days' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $to = isset($data['to']) ? CarbonImmutable::parse($data['to'])->endOfDay() : CarbonImmutable::now()->endOfDay();
        $from = isset($data['from']) ? CarbonImmutable::parse($data['from'])->startOfDay() : $to->subDays(29)->startOfDay();
        abort_if($from->greaterThan($to), 422, 'Período inválido.');
        abort_if($from->diffInDays($to) > 90, 422, 'Período máximo: 91 dias.');

        $days = (int) ($data['observation_days'] ?? 7);
        $courseId = $data['course_id'] ?? null;
        $now = CarbonImmutable::now();
        $maturityCutoff = $now->subDays($days);
        $metrics = [
            'registrations' => 0,
            'mature_registrations' => 0,
            'immature_registrations' => 0,
            'started_trial' => 0,
            'started_study' => 0,
            'answered_questions_users' => 0,
            'answers_total' => 0,
            'returned_to_answer_24_48h' => 0,
            'paid_users' => 0,
            'trial_and_paid_users' => 0,
        ];

        $query = User::query()->where('role', 'student')
            ->whereBetween('created_at', [$from, $to])
            ->when(isset($data['acquisition_source']), fn ($q) => $q->where('acquisition_source', $data['acquisition_source']))
            ->when(isset($data['acquisition_campaign']), fn ($q) => $q->where('acquisition_campaign', $data['acquisition_campaign']))
            ->select(['id', 'created_at']);

        // Usa lotes para evitar carregar todos os usuários em memória.
        $query->chunkById(300, function ($users) use (&$metrics, $maturityCutoff, $days, $courseId, $now) {
            $metrics['registrations'] += $users->count();
            $eligible = $users->filter(fn ($u) => $u->created_at && $u->created_at->lessThanOrEqualTo($maturityCutoff));
            $metrics['immature_registrations'] += $users->count() - $eligible->count();
            if ($eligible->isEmpty()) {
                return;
            }

            $metrics['mature_registrations'] += $eligible->count();
            $byId = $eligible->keyBy('id');
            $ids = $byId->keys()->all();
            $trialIds = [];
            $paidIds = [];
            $studyIds = [];
            $answerIds = [];
            $returnIds = [];

            $trials = CourseAccess::query()->whereIn('user_id', $ids)
                ->where('access_type', CourseAccess::TYPE_TRIAL)
                ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
                ->where('starts_at', '<=', $now)->get(['user_id', 'starts_at']);
            foreach ($trials as $trial) {
                if ($this->insideWindow($trial->starts_at, $byId[$trial->user_id]->created_at, $days)) {
                    $trialIds[$trial->user_id] = true;
                }
            }

            $payments = PaymentTransaction::query()->whereIn('user_id', $ids)
                ->where('status', PaymentTransaction::STATUS_PAID)
                ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
                ->where('paid_at', '<=', $now)->get(['user_id', 'paid_at']);
            foreach ($payments as $payment) {
                if ($this->insideWindow($payment->paid_at, $byId[$payment->user_id]->created_at, $days)) {
                    $paidIds[$payment->user_id] = true;
                }
            }

            $sessions = StudySession::query()->whereIn('user_id', $ids)
                ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
                ->where('started_at', '<=', $now)->get(['id', 'user_id', 'started_at']);
            foreach ($sessions as $session) {
                if ($this->insideWindow($session->started_at, $byId[$session->user_id]->created_at, $days)) {
                    $studyIds[$session->user_id] = true;
                }
            }

            $answers = UserAnswer::query()->whereIn('user_answers.user_id', $ids)
                ->when($courseId, function ($q) use ($courseId) {
                    $q->join('study_sessions', 'study_sessions.id', '=', 'user_answers.study_session_id')
                        ->where('study_sessions.course_id', $courseId);
                })
                ->where('user_answers.answered_at', '<=', $now)
                ->get(['user_answers.user_id', 'user_answers.answered_at']);
            foreach ($answers as $answer) {
                $registered = $byId[$answer->user_id]->created_at;
                if (! $this->insideWindow($answer->answered_at, $registered, $days)) {
                    continue;
                }
                $metrics['answers_total']++;
                $answerIds[$answer->user_id] = true;
                $answered = CarbonImmutable::instance($answer->answered_at);
                if ($answered->greaterThanOrEqualTo(CarbonImmutable::instance($registered)->addDay())
                    && $answered->lessThan(CarbonImmutable::instance($registered)->addDays(2))) {
                    $returnIds[$answer->user_id] = true;
                }
            }

            $metrics['started_trial'] += count($trialIds);
            $metrics['started_study'] += count($studyIds);
            $metrics['answered_questions_users'] += count($answerIds);
            $metrics['returned_to_answer_24_48h'] += count($returnIds);
            $metrics['paid_users'] += count($paidIds);
            $metrics['trial_and_paid_users'] += count(array_intersect_key($trialIds, $paidIds));
        });

        $base = $metrics['mature_registrations'];
        return response()->json([
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'filters' => [
                'course_id' => $courseId,
                'acquisition_source' => $data['acquisition_source'] ?? null,
                'acquisition_campaign' => $data['acquisition_campaign'] ?? null,
                'observation_days' => $days,
            ],
            'metrics' => $metrics,
            'rates' => [
                'registration_to_trial_percent' => $this->percent($metrics['started_trial'], $base),
                'registration_to_first_answer_percent' => $this->percent($metrics['answered_questions_users'], $base),
                'registration_to_payment_percent' => $this->percent($metrics['paid_users'], $base),
                'trial_to_payment_percent' => $this->percent($metrics['trial_and_paid_users'], $metrics['started_trial']),
            ],
            'notes' => [
                'cohort' => 'Somente alunos cadastrados no período e com janela de observação completa. Taxas usam essa população, exceto trial_to_payment.',
                'window' => 'Eventos entre o instante do cadastro (inclusive) e N dias depois (exclusive).',
                'study' => 'Sessões iniciadas; não equivale a sessão concluída.',
                'return_24_48h' => 'Resposta entre 24h e 48h após cadastro; não exige uma resposta anterior.',
                'course_answers' => 'Com course_id, apenas respostas vinculadas a study_sessions do curso; respostas sem sessão não são atribuídas.',
                'payment' => 'Pagamento confirmado após cadastro na janela, não necessariamente primeiro pagamento do usuário.',
            ],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    private function insideWindow($event, $registered, int $days): bool
    {
        if (! $event || ! $registered) return false;
        $event = CarbonImmutable::instance($event);
        $start = CarbonImmutable::instance($registered);
        return $event->greaterThanOrEqualTo($start) && $event->lessThan($start->addDays($days));
    }

    private function percent(int $num, int $den): ?float
    {
        return $den ? round($num * 100 / $den, 2) : null;
    }
}
