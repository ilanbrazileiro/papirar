<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Billing\AccessGrantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = User::query()
            ->where('role', 'student')
            ->with(['subscriptions' => function ($query) {
                $query->with('plan')->latest('expires_at');
            }])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('access_status'), function ($query) use ($request) {
                match ($request->string('access_status')->toString()) {
                    'active' => $query->whereHas('subscriptions', function ($q) {
                        $q->where('status', Subscription::STATUS_ACTIVE)
                            ->where(function ($inner) {
                                $inner->whereNull('expires_at')
                                    ->orWhere('expires_at', '>=', now());
                            });
                    }),
                    'inactive' => $query->whereDoesntHave('subscriptions', function ($q) {
                        $q->where('status', Subscription::STATUS_ACTIVE)
                            ->where(function ($inner) {
                                $inner->whereNull('expires_at')
                                    ->orWhere('expires_at', '>=', now());
                            });
                    }),
                    default => null,
                };
            })
            ->when($request->filled('account_status'), function ($query) use ($request) {
                if ($request->string('account_status')->toString() === 'enabled') {
                    $query->where('is_active', 1);
                }

                if ($request->string('account_status')->toString() === 'disabled') {
                    $query->where('is_active', 0);
                }
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(Request $request, User $customer)
    {
        abort_unless($customer->role === 'student', 404);

        $customer->load([
            'subscriptions' => fn ($query) => $query->with('plan')->latest('id'),
            'address',
            'supportTickets',
        ]);

        $plans = SubscriptionPlan::query()
            ->where('active', true)
            ->orderBy('price')
            ->orderBy('name')
            ->get();

        $usagePeriod = $request->string('period')->toString();
        if (! in_array($usagePeriod, ['7', '30', '90', 'all'], true)) {
            $usagePeriod = '30';
        }

        $usageFrom = match ($usagePeriod) {
            '7' => now()->subDays(6)->startOfDay(),
            '30' => now()->subDays(29)->startOfDay(),
            '90' => now()->subDays(89)->startOfDay(),
            default => null,
        };

        $answersBase = DB::table('user_answers')
            ->where('user_id', $customer->id)
            ->when($usageFrom, fn ($query) => $query->where('answered_at', '>=', $usageFrom));

        $answerStats = (clone $answersBase)
            ->selectRaw('COUNT(*) as answered')
            ->selectRaw('COUNT(DISTINCT question_id) as distinct_questions')
            ->selectRaw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct')
            ->selectRaw('SUM(CASE WHEN is_correct = 0 THEN 1 ELSE 0 END) as wrong')
            ->selectRaw('MAX(answered_at) as last_answered_at')
            ->first();

        $answered = (int) ($answerStats->answered ?? 0);
        $correct = (int) ($answerStats->correct ?? 0);
        $wrong = (int) ($answerStats->wrong ?? 0);
        $distinctQuestions = (int) ($answerStats->distinct_questions ?? 0);
        $accuracy = $answered > 0 ? round(($correct / $answered) * 100, 1) : 0.0;

        $sessionsBase = DB::table('study_sessions')
            ->where('user_id', $customer->id)
            ->when($usageFrom, fn ($query) => $query->where('started_at', '>=', $usageFrom));

        $sessionStats = (clone $sessionsBase)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN finished_at IS NOT NULL THEN 1 ELSE 0 END) as finished')
            ->selectRaw('MAX(COALESCE(finished_at, started_at)) as last_activity_at')
            ->first();

        $simulatedBase = DB::table('simulated_exams')
            ->where('user_id', $customer->id)
            ->when($usageFrom, fn ($query) => $query->where('started_at', '>=', $usageFrom));

        $simulatedStats = (clone $simulatedBase)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN finished_at IS NOT NULL THEN 1 ELSE 0 END) as finished')
            ->selectRaw('AVG(CASE WHEN finished_at IS NOT NULL THEN accuracy ELSE NULL END) as avg_accuracy')
            ->selectRaw('MAX(COALESCE(finished_at, started_at)) as last_activity_at')
            ->first();

        $answerDays = (clone $answersBase)
            ->whereNotNull('answered_at')
            ->selectRaw('DATE(answered_at) as activity_date')
            ->distinct()
            ->pluck('activity_date');

        $sessionDays = (clone $sessionsBase)
            ->whereNotNull('started_at')
            ->selectRaw('DATE(started_at) as activity_date')
            ->distinct()
            ->pluck('activity_date');

        $simulatedDays = (clone $simulatedBase)
            ->whereNotNull('started_at')
            ->selectRaw('DATE(started_at) as activity_date')
            ->distinct()
            ->pluck('activity_date');

        $activeDays = $answerDays->merge($sessionDays)->merge($simulatedDays)->filter()->unique()->count();

        $lastStudyActivityAt = collect([
            $answerStats->last_answered_at ?? null,
            $sessionStats->last_activity_at ?? null,
            $simulatedStats->last_activity_at ?? null,
        ])->filter()->sortDesc()->first();

        $questionsPerActiveDay = $activeDays > 0 ? round($answered / $activeDays, 1) : 0.0;

        $courseAnswerRows = DB::table('user_answers as ua')
            ->join('study_sessions as ss', 'ss.id', '=', 'ua.study_session_id')
            ->leftJoin('courses as c', 'c.id', '=', 'ss.course_id')
            ->where('ua.user_id', $customer->id)
            ->when($usageFrom, fn ($query) => $query->where('ua.answered_at', '>=', $usageFrom))
            ->groupBy('ss.course_id', 'c.title')
            ->selectRaw('ss.course_id as course_id')
            ->selectRaw('COALESCE(c.title, "Curso não identificado") as course_title')
            ->selectRaw('COUNT(*) as answered')
            ->selectRaw('COUNT(DISTINCT ua.question_id) as distinct_questions')
            ->selectRaw('SUM(CASE WHEN ua.is_correct = 1 THEN 1 ELSE 0 END) as correct')
            ->selectRaw('MAX(ua.answered_at) as last_activity_at')
            ->get();

        $courseSessionRows = DB::table('study_sessions as ss')
            ->leftJoin('courses as c', 'c.id', '=', 'ss.course_id')
            ->where('ss.user_id', $customer->id)
            ->when($usageFrom, fn ($query) => $query->where('ss.started_at', '>=', $usageFrom))
            ->groupBy('ss.course_id', 'c.title')
            ->selectRaw('ss.course_id as course_id')
            ->selectRaw('COALESCE(c.title, "Curso não identificado") as course_title')
            ->selectRaw('COUNT(*) as sessions')
            ->selectRaw('MAX(COALESCE(ss.finished_at, ss.started_at)) as last_activity_at')
            ->get();

        $courseSimulatedRows = DB::table('simulated_exams as se')
            ->leftJoin('courses as c', 'c.id', '=', 'se.course_id')
            ->where('se.user_id', $customer->id)
            ->when($usageFrom, fn ($query) => $query->where('se.started_at', '>=', $usageFrom))
            ->groupBy('se.course_id', 'c.title')
            ->selectRaw('se.course_id as course_id')
            ->selectRaw('COALESCE(c.title, "Curso não identificado") as course_title')
            ->selectRaw('COUNT(*) as simulations')
            ->selectRaw('SUM(CASE WHEN se.finished_at IS NOT NULL THEN 1 ELSE 0 END) as simulations_finished')
            ->selectRaw('MAX(COALESCE(se.finished_at, se.started_at)) as last_activity_at')
            ->get();

        $courseUsage = collect();

        foreach ($courseSessionRows as $row) {
            $key = (string) ($row->course_id ?? 'none');
            $courseUsage[$key] = (object) [
                'course_id' => $row->course_id,
                'course_title' => $row->course_title,
                'sessions' => (int) $row->sessions,
                'answered' => 0,
                'distinct_questions' => 0,
                'correct' => 0,
                'accuracy' => 0.0,
                'simulations' => 0,
                'simulations_finished' => 0,
                'last_activity_at' => $row->last_activity_at,
            ];
        }

        foreach ($courseAnswerRows as $row) {
            $key = (string) ($row->course_id ?? 'none');
            if (! $courseUsage->has($key)) {
                $courseUsage[$key] = (object) [
                    'course_id' => $row->course_id,
                    'course_title' => $row->course_title,
                    'sessions' => 0,
                    'answered' => 0,
                    'distinct_questions' => 0,
                    'correct' => 0,
                    'accuracy' => 0.0,
                    'simulations' => 0,
                    'simulations_finished' => 0,
                    'last_activity_at' => null,
                ];
            }

            $item = $courseUsage[$key];
            $item->answered = (int) $row->answered;
            $item->distinct_questions = (int) $row->distinct_questions;
            $item->correct = (int) $row->correct;
            $item->accuracy = $item->answered > 0 ? round(($item->correct / $item->answered) * 100, 1) : 0.0;

            if (! $item->last_activity_at || ($row->last_activity_at && $row->last_activity_at > $item->last_activity_at)) {
                $item->last_activity_at = $row->last_activity_at;
            }
        }

        foreach ($courseSimulatedRows as $row) {
            $key = (string) ($row->course_id ?? 'none');
            if (! $courseUsage->has($key)) {
                $courseUsage[$key] = (object) [
                    'course_id' => $row->course_id,
                    'course_title' => $row->course_title,
                    'sessions' => 0,
                    'answered' => 0,
                    'distinct_questions' => 0,
                    'correct' => 0,
                    'accuracy' => 0.0,
                    'simulations' => 0,
                    'simulations_finished' => 0,
                    'last_activity_at' => null,
                ];
            }

            $item = $courseUsage[$key];
            $item->simulations = (int) $row->simulations;
            $item->simulations_finished = (int) $row->simulations_finished;

            if (! $item->last_activity_at || ($row->last_activity_at && $row->last_activity_at > $item->last_activity_at)) {
                $item->last_activity_at = $row->last_activity_at;
            }
        }

        $courseUsage = $courseUsage->values()->sortByDesc(fn ($row) => $row->last_activity_at ?? '')->values();

        $courseAccesses = DB::table('course_accesses as ca')
            ->leftJoin('courses as c', 'c.id', '=', 'ca.course_id')
            ->where('ca.user_id', $customer->id)
            ->orderByDesc('ca.created_at')
            ->select([
                'ca.id',
                'ca.course_id',
                'ca.status',
                'ca.access_type',
                'ca.starts_at',
                'ca.ends_at',
                'ca.canceled_at',
                'c.title as course_title',
            ])
            ->get();

        $usage = (object) [
            'period' => $usagePeriod,
            'from' => $usageFrom,
            'active_days' => $activeDays,
            'sessions' => (int) ($sessionStats->total ?? 0),
            'finished_sessions' => (int) ($sessionStats->finished ?? 0),
            'answered' => $answered,
            'distinct_questions' => $distinctQuestions,
            'correct' => $correct,
            'wrong' => $wrong,
            'accuracy' => $accuracy,
            'questions_per_active_day' => $questionsPerActiveDay,
            'simulations' => (int) ($simulatedStats->total ?? 0),
            'simulations_finished' => (int) ($simulatedStats->finished ?? 0),
            'simulated_accuracy' => round((float) ($simulatedStats->avg_accuracy ?? 0), 1),
            'last_study_activity_at' => $lastStudyActivityAt,
        ];

        return view('admin.customers.show', compact('customer', 'plans', 'usage', 'courseUsage', 'courseAccesses'));
    }

    public function edit(User $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, User $customer): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($customer->id)],
            'cpf' => ['nullable', 'string', 'max:14', Rule::unique('users', 'cpf')->ignore($customer->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'cpf' => $data['cpf'] ?? null,
            'phone' => $data['phone'] ?? null,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $customer->update($payload);

        return redirect()->route('admin.customers.edit', $customer)->with('success', 'Cliente atualizado com sucesso.');
    }

    public function grantAccess(Request $request, User $customer, AccessGrantService $accessGrantService): RedirectResponse
    {
        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
            'plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'cancel_current' => ['nullable', 'boolean'],
        ], [
            'days.required' => 'Informe a quantidade de dias para liberar.',
            'days.min' => 'A liberação mínima é de 1 dia.',
            'days.max' => 'A liberação máxima é de 365 dias por operação.',
            'plan_id.exists' => 'Plano selecionado inválido.',
        ]);

        $subscription = $accessGrantService->grantManualAccess(
            user: $customer,
            days: (int) $data['days'],
            planId: !empty($data['plan_id']) ? (int) $data['plan_id'] : null,
            cancelCurrent: (bool) ($data['cancel_current'] ?? false)
        );

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', "Acesso liberado até {$subscription->expires_at->format('d/m/Y H:i')}.");
    }
}
