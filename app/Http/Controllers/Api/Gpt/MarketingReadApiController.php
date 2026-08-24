<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketingReadApiController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'service' => 'papirar-marketing-api',
            'mode' => 'read-only',
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function funnel(Request $request): JsonResponse
    {
        [$from, $to] = $this->period($request);
        $courseId = $this->courseId($request);

        $registrations = User::query()
            ->where('role', 'student')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $trialUsers = CourseAccess::query()
            ->where('access_type', CourseAccess::TYPE_TRIAL)
            ->whereBetween('starts_at', [$from, $to])
            ->when($courseId, fn (Builder $q) => $q->where('course_id', $courseId))
            ->distinct('user_id')
            ->count('user_id');

        $paidUsers = PaymentTransaction::query()
            ->where('status', PaymentTransaction::STATUS_PAID)
            ->whereBetween('paid_at', [$from, $to])
            ->when($courseId, fn (Builder $q) => $q->where('course_id', $courseId))
            ->distinct('user_id')
            ->count('user_id');

        $activeSubscriptions = Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where(function (Builder $q) use ($to) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $to->startOfDay());
            })
            ->where('starts_at', '<=', $to)
            ->when($courseId, fn (Builder $q) => $q->where('course_id', $courseId))
            ->count();

        $renewals = PaymentTransaction::query()
            ->where('status', PaymentTransaction::STATUS_PAID)
            ->whereBetween('paid_at', [$from, $to])
            ->when($courseId, fn (Builder $q) => $q->where('course_id', $courseId))
            ->whereIn('user_id', function ($query) use ($from, $courseId) {
                $query->select('user_id')
                    ->from('payment_transactions')
                    ->where('status', PaymentTransaction::STATUS_PAID)
                    ->where('paid_at', '<', $from);

                if ($courseId) {
                    $query->where('course_id', $courseId);
                }
            })
            ->distinct('user_id')
            ->count('user_id');

        return response()->json([
            'period' => $this->periodPayload($from, $to),
            'filters' => ['course_id' => $courseId],
            'funnel' => [
                'registrations' => $registrations,
                'trial_users' => $trialUsers,
                'paid_users' => $paidUsers,
                'active_subscriptions' => $activeSubscriptions,
                'renewed_users' => $renewals,
                'registration_to_trial_rate' => $this->rate($trialUsers, $registrations),
                'trial_to_paid_rate' => $this->rate($paidUsers, $trialUsers),
            ],
            'notes' => [
                'registrations' => 'Alunos cadastrados no período.',
                'trial_users' => 'Usuários distintos com acesso do tipo trial iniciado no período.',
                'paid_users' => 'Usuários distintos com pagamento confirmado no período.',
                'active_subscriptions' => 'Assinaturas ativas até o fim do período consultado.',
                'renewed_users' => 'Usuários pagos no período que já possuíam pagamento anterior.',
            ],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function acquisition(Request $request): JsonResponse
    {
        [$from, $to] = $this->period($request);
        $limit = min(max((int) $request->integer('limit', 25), 1), 100);

        $base = User::query()
            ->where('role', 'student')
            ->whereBetween('created_at', [$from, $to]);

        $sources = (clone $base)
            ->selectRaw("COALESCE(NULLIF(acquisition_source, ''), 'unknown') AS acquisition_source")
            ->selectRaw('COUNT(*) AS registrations')
            ->groupBy('acquisition_source')
            ->orderByDesc('registrations')
            ->limit($limit)
            ->get();

        $campaigns = (clone $base)
            ->selectRaw("COALESCE(NULLIF(acquisition_source, ''), 'unknown') AS acquisition_source")
            ->selectRaw("COALESCE(NULLIF(acquisition_medium, ''), 'unknown') AS acquisition_medium")
            ->selectRaw("COALESCE(NULLIF(acquisition_campaign, ''), 'none') AS acquisition_campaign")
            ->selectRaw('COUNT(*) AS registrations')
            ->groupBy('acquisition_source', 'acquisition_medium', 'acquisition_campaign')
            ->orderByDesc('registrations')
            ->limit($limit)
            ->get();

        $landingPages = (clone $base)
            ->selectRaw("COALESCE(NULLIF(acquisition_landing_path, ''), 'unknown') AS landing_path")
            ->selectRaw('COUNT(*) AS registrations')
            ->groupBy('landing_path')
            ->orderByDesc('registrations')
            ->limit($limit)
            ->get();

        return response()->json([
            'period' => $this->periodPayload($from, $to),
            'sources' => $sources,
            'campaigns' => $campaigns,
            'landing_pages' => $landingPages,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function courses(Request $request): JsonResponse
    {
        [$from, $to] = $this->period($request);

        $courses = Course::query()
            ->select([
                'id', 'title', 'slug', 'course_type', 'price',
                'quarterly_price', 'semiannual_price',
                'active', 'is_public', 'is_trial_available', 'trial_days',
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(function (Course $course) use ($from, $to) {
                $trialUsers = CourseAccess::query()
                    ->where('course_id', $course->id)
                    ->where('access_type', CourseAccess::TYPE_TRIAL)
                    ->whereBetween('starts_at', [$from, $to])
                    ->distinct('user_id')
                    ->count('user_id');

                $paidUsers = PaymentTransaction::query()
                    ->where('course_id', $course->id)
                    ->where('status', PaymentTransaction::STATUS_PAID)
                    ->whereBetween('paid_at', [$from, $to])
                    ->distinct('user_id')
                    ->count('user_id');

                $revenue = PaymentTransaction::query()
                    ->where('course_id', $course->id)
                    ->where('status', PaymentTransaction::STATUS_PAID)
                    ->whereBetween('paid_at', [$from, $to])
                    ->sum('amount');

                $activeAccesses = CourseAccess::query()
                    ->where('course_id', $course->id)
                    ->where('status', CourseAccess::STATUS_ACTIVE)
                    ->where('starts_at', '<=', $to)
                    ->where(function (Builder $q) use ($to) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', $to->startOfDay());
                    })
                    ->distinct('user_id')
                    ->count('user_id');

                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'course_type' => $course->course_type,
                    'prices' => [
                        'monthly' => $course->price,
                        'quarterly' => $course->quarterly_price,
                        'semiannual' => $course->semiannual_price,
                    ],
                    'active' => $course->active,
                    'is_public' => $course->is_public,
                    'trial' => [
                        'available' => $course->is_trial_available,
                        'days' => $course->trial_days,
                    ],
                    'metrics' => [
                        'trial_users' => $trialUsers,
                        'paid_users' => $paidUsers,
                        'active_users' => $activeAccesses,
                        'revenue' => number_format((float) $revenue, 2, '.', ''),
                        'trial_to_paid_rate' => $this->rate($paidUsers, $trialUsers),
                    ],
                ];
            });

        return response()->json([
            'period' => $this->periodPayload($from, $to),
            'courses' => $courses,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function revenue(Request $request): JsonResponse
    {
        [$from, $to] = $this->period($request);
        $courseId = $this->courseId($request);

        $base = PaymentTransaction::query()
            ->where('status', PaymentTransaction::STATUS_PAID)
            ->whereBetween('paid_at', [$from, $to])
            ->when($courseId, fn (Builder $q) => $q->where('course_id', $courseId));

        $summary = [
            'gross_revenue' => number_format((float) (clone $base)->sum('amount'), 2, '.', ''),
            'paid_transactions' => (clone $base)->count(),
            'paying_users' => (clone $base)->distinct('user_id')->count('user_id'),
            'average_ticket' => '0.00',
        ];

        if ($summary['paid_transactions'] > 0) {
            $summary['average_ticket'] = number_format(
                (float) $summary['gross_revenue'] / $summary['paid_transactions'],
                2,
                '.',
                ''
            );
        }

        $byCourse = PaymentTransaction::query()
            ->leftJoin('courses', 'courses.id', '=', 'payment_transactions.course_id')
            ->where('payment_transactions.status', PaymentTransaction::STATUS_PAID)
            ->whereBetween('payment_transactions.paid_at', [$from, $to])
            ->when($courseId, fn ($q) => $q->where('payment_transactions.course_id', $courseId))
            ->selectRaw('payment_transactions.course_id')
            ->selectRaw("COALESCE(courses.title, 'Sem curso identificado') AS course_title")
            ->selectRaw('COUNT(*) AS paid_transactions')
            ->selectRaw('COUNT(DISTINCT payment_transactions.user_id) AS paying_users')
            ->selectRaw('SUM(payment_transactions.amount) AS revenue')
            ->groupBy('payment_transactions.course_id', 'courses.title')
            ->orderByDesc('revenue')
            ->get();

        return response()->json([
            'period' => $this->periodPayload($from, $to),
            'filters' => ['course_id' => $courseId],
            'summary' => $summary,
            'by_course' => $byCourse,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    private function period(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'course_id' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $to = isset($validated['to'])
            ? CarbonImmutable::createFromFormat('Y-m-d', $validated['to'])->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $from = isset($validated['from'])
            ? CarbonImmutable::createFromFormat('Y-m-d', $validated['from'])->startOfDay()
            : $to->subDays(29)->startOfDay();

        abort_if($from->greaterThan($to), 422, 'A data inicial não pode ser posterior à data final.');
        abort_if($from->diffInDays($to) > 366, 422, 'O período máximo permitido é de 367 dias.');

        return [$from, $to];
    }

    private function courseId(Request $request): ?int
    {
        $value = $request->integer('course_id');

        return $value > 0 ? $value : null;
    }

    private function rate(int $numerator, int $denominator): ?float
    {
        if ($denominator <= 0) {
            return null;
        }

        return round(($numerator / $denominator) * 100, 2);
    }

    private function periodPayload(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'days' => $from->diffInDays($to) + 1,
        ];
    }
}
