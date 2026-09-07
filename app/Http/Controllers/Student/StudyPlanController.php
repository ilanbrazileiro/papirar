<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\StudyPlan;
use App\Services\Study\StudyPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudyPlanController extends Controller
{
    public function __construct(private readonly StudyPlanService $plans) {}

    public function index(): View
    {
        $courses = Course::query()->whereIn('id', $this->activeCourseIds())->orderBy('sort_order')->orderBy('title')->get();
        $subjectsByCourse = $courses->mapWithKeys(fn (Course $course) => [
            $course->id => $this->plans->availableSubjects((int) $course->id, (bool) $course->inherit_exam_scope, $course->exam_id)
                ->map(fn ($subject) => ['id' => (int) $subject->id, 'name' => $subject->name])->values(),
        ]);

        return view('student.study-plan.index', [
            'courses' => $courses,
            'subjectsByCourse' => $subjectsByCourse,
            'plan' => $this->plans->activeForUser((int) Auth::id()),
            'todaySchedule' => $this->plans->today((int) Auth::id()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
            'daily_question_target' => ['required', 'integer', 'min:5', 'max:100'],
        ]);

        abort_unless(in_array((int) $data['course_id'], $this->activeCourseIds(), true), 403);
        $course = Course::query()->findOrFail($data['course_id']);
        $allowed = $this->plans->availableSubjects((int) $course->id, (bool) $course->inherit_exam_scope, $course->exam_id)->pluck('id')->map(fn ($id) => (int) $id);
        $subjects = collect($data['subject_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        abort_if($subjects->diff($allowed)->isNotEmpty(), 422, 'Uma disciplina selecionada não pertence ao curso.');

        DB::transaction(function () use ($data, $subjects) {
            StudyPlan::query()->where('user_id', Auth::id())->update(['is_active' => false]);
            StudyPlan::query()->updateOrCreate(
                ['user_id' => Auth::id(), 'course_id' => $data['course_id']],
                ['weekdays' => collect($data['weekdays'])->map(fn ($day) => (int) $day)->unique()->sort()->values()->all(), 'subject_ids' => $subjects->all(), 'daily_question_target' => $data['daily_question_target'], 'starts_on' => today(), 'is_active' => true]
            );
        });

        return redirect()->route('student.study-plan.index')->with('success', 'Cronograma salvo. Seu plano diário já está ativo.');
    }

    public function destroy(): RedirectResponse
    {
        StudyPlan::query()->where('user_id', Auth::id())->where('is_active', true)->update(['is_active' => false]);
        return redirect()->route('student.study-plan.index')->with('success', 'Cronograma desativado. Você pode criar outro quando quiser.');
    }

    private function activeCourseIds(): array
    {
        return CourseAccess::query()->where('user_id', Auth::id())->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->pluck('course_id')->map(fn ($id) => (int) $id)->values()->all();
    }
}
