<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $activeCourseAccesses = CourseAccess::query()
            ->with('course')
            ->where('user_id', $user->id)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->latest('ends_at')
            ->get();

        $activeCourseIds = $activeCourseAccesses
            ->pluck('course_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $availableCourses = Course::query()
            ->active()
            ->public()
            ->whereNotIn('id', $activeCourseIds ?: [0])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $allCoursesForRenewal = Course::query()
            ->active()
            ->public()
            ->whereIn('id', $activeCourseIds ?: [0])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->keyBy('id');

        $pendingTransactions = PaymentTransaction::query()
            ->with('course')
            ->where('user_id', $user->id)
            ->whereNotNull('course_id')
            ->where('status', PaymentTransaction::STATUS_PENDING)
            ->latest('id')
            ->limit(5)
            ->get();

        $paymentStatus = $request->query('payment');

        return view('student.subscriptions.index', [
            'activeCourseAccesses' => $activeCourseAccesses,
            'availableCourses' => $availableCourses,
            'allCoursesForRenewal' => $allCoursesForRenewal,
            'pendingTransactions' => $pendingTransactions,
            'paymentStatus' => $paymentStatus,
            'needsEmailVerification' => ! $user->hasVerifiedEmail(),
        ]);
    }

    /**
     * Rota antiga de checkout de assinatura geral.
     *
     * O modelo atual do Papirar vende acesso por curso. Os botões da tela de assinatura
     * devem usar student.courses.checkout, que recebe o curso e o ciclo de cobrança.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'billing_cycle' => ['required', 'string'],
        ]);

        return redirect()
            ->route('student.courses.index')
            ->with('info', 'Escolha o curso e o período diretamente nos cards de assinatura.');
    }

    public function history(): View
    {
        $subscriptions = Subscription::query()
            ->with(['course', 'transactions' => function ($query) {
                $query->latest('id');
            }])
            ->where('user_id', Auth::id())
            ->whereNotNull('course_id')
            ->latest('id')
            ->paginate(15);

        return view('student.subscriptions.history', compact('subscriptions'));
    }

    public function retry(Subscription $subscription): RedirectResponse
    {
        $user = Auth::user();

        abort_unless((int) $subscription->user_id === (int) $user->id, 403);

        if ($subscription->course_id) {
            return redirect()
                ->route('student.subscriptions.index')
                ->with('info', 'Para renovar ou ampliar, escolha novamente o curso e o período desejado.');
        }

        return redirect()
            ->route('student.subscriptions.index')
            ->with('error', 'Essa assinatura pertence ao modelo antigo e não pode ser renovada por aqui.');
    }
}
