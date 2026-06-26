<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CourseTrialController extends Controller
{
    public function start(Course $course): RedirectResponse
    {
        $user = Auth::user();

        if (! $course->active || ! $course->is_public) {
            return redirect()
                ->route('student.courses.index')
                ->with('error', 'Este curso não está disponível para teste gratuito.');
        }

        if (! $course->is_trial_available || (int) $course->trial_days <= 0) {
            return redirect()
                ->route('student.courses.index')
                ->with('error', 'Este curso não possui teste gratuito disponível.');
        }

        $hasActiveAccess = CourseAccess::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->exists();

        if ($hasActiveAccess) {
            return redirect()
                ->route('student.courses.show', $course)
                ->with('info', 'Você já possui acesso ativo a este curso.');
        }

        $alreadyUsedTrial = CourseAccess::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('access_type', CourseAccess::TYPE_TRIAL)
            ->exists();

        if ($alreadyUsedTrial) {
            return redirect()
                ->route('student.courses.index')
                ->with('error', 'Você já utilizou o teste gratuito deste curso.');
        }

        $trialDays = $course->trialDaysForAccess();

        CourseAccess::query()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'subscription_id' => null,
            'status' => CourseAccess::STATUS_ACTIVE,
            'access_type' => CourseAccess::TYPE_TRIAL,
            'starts_at' => now(),
            'ends_at' => now()->addDays($trialDays)->endOfDay(),
            'cancel_at_period_end' => false,
            'bonus_days' => 0,
        ]);

        return redirect()
            ->route('student.courses.show', $course)
            ->with('success', "Teste gratuito liberado por {$trialDays} dias.");
    }
}
