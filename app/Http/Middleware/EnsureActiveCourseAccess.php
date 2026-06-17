<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\SimulatedExam;
use App\Models\StudySession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveCourseAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            abort(403);
        }

        $courseId = $this->resolveCourseId($request);

        if (!$courseId) {
            abort(403);
        }

        $hasAccess = CourseAccess::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->exists();

        if (!$hasAccess) {
            if ($request->expectsJson()) {
                abort(403, 'Você não possui acesso ativo a este curso.');
            }

            return redirect()
                ->route('student.courses.index')
                ->with('error', 'Você não possui acesso ativo a este curso.');
        }

        return $next($request);
    }

    private function resolveCourseId(Request $request): ?int
    {
        $course = $request->route('course');

        if ($course instanceof Course) return (int) $course->id;
        if (is_numeric($course)) return (int) $course;

        $session = $request->route('session');

        if ($session instanceof StudySession) return $session->course_id ? (int) $session->course_id : null;
        if (is_numeric($session)) {
            $studySession = StudySession::query()->find((int) $session);
            return $studySession && $studySession->course_id ? (int) $studySession->course_id : null;
        }

        $simulatedExam = $request->route('simulatedExam');

        if ($simulatedExam instanceof SimulatedExam) return $simulatedExam->course_id ? (int) $simulatedExam->course_id : null;
        if (is_numeric($simulatedExam)) {
            $exam = SimulatedExam::query()->find((int) $simulatedExam);
            return $exam && $exam->course_id ? (int) $exam->course_id : null;
        }

        return null;
    }
}
