<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Question;
use App\Models\Subject;

class SiteController extends Controller
{
    public function home()
    {
        $publicCourses = Course::query()
            ->active()
            ->public()
            ->with([
                'corporation:id,name',
                'exam:id,title,year',
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(6)
            ->get();

        $totalQuestions = Question::query()
            ->visibleToStudent()
            ->count();

        $totalSubjects = Subject::query()
            ->where('active', true)
            ->whereHas('questions', fn ($query) => $query->visibleToStudent())
            ->count();

        $featuredSubjects = Subject::query()
            ->where('active', true)
            ->whereHas('questions', fn ($query) => $query->visibleToStudent())
            ->withCount([
                'questions as public_questions_count' => fn ($query) => $query->visibleToStudent(),
            ])
            ->orderByDesc('public_questions_count')
            ->orderBy('name')
            ->limit(8)
            ->get();

        $trialDays = $publicCourses
            ->filter(fn (Course $course) => $course->is_trial_available)
            ->map(fn (Course $course) => $course->trialDaysForAccess())
            ->filter()
            ->min();

        return view('site.site_home', [
            'publicCourses' => $publicCourses,
            'totalQuestions' => $totalQuestions,
            'totalSubjects' => $totalSubjects,
            'featuredSubjects' => $featuredSubjects,
            'trialDays' => $trialDays,
        ]);
    }

    public function privacyPolicy()
    {
        return view('site.privacy_policy');
    }
}
