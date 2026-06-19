<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Question;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoursePerformanceController extends Controller
{
    public function show(Request $request, Course $course)
    {
        $user = $request->user();

        $availableQuestions = (clone $this->courseQuestionQuery($course))->count();

        $training = DB::table('user_answers')
            ->join('study_sessions', 'user_answers.study_session_id', '=', 'study_sessions.id')
            ->where('user_answers.user_id', $user->id)
            ->where('study_sessions.course_id', $course->id)
            ->selectRaw('COUNT(*) as answered')
            ->selectRaw('SUM(CASE WHEN user_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct')
            ->selectRaw('SUM(CASE WHEN user_answers.is_correct = 0 THEN 1 ELSE 0 END) as wrong')
            ->selectRaw('COUNT(DISTINCT user_answers.question_id) as distinct_questions')
            ->selectRaw('COUNT(DISTINCT study_sessions.id) as sessions')
            ->selectRaw('MAX(user_answers.answered_at) as last_answered_at')
            ->first();

        $trainingAnswered = (int) ($training->answered ?? 0);
        $trainingCorrect = (int) ($training->correct ?? 0);
        $trainingWrong = (int) ($training->wrong ?? 0);
        $trainingAccuracy = $this->percentage($trainingCorrect, $trainingAnswered);
        $distinctAnsweredQuestions = (int) ($training->distinct_questions ?? 0);
        $unansweredQuestions = max(0, $availableQuestions - $distinctAnsweredQuestions);

        $simulated = DB::table('simulated_exams')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN finished_at IS NOT NULL THEN 1 ELSE 0 END) as finished')
            ->selectRaw('SUM(CASE WHEN finished_at IS NULL THEN 1 ELSE 0 END) as unfinished')
            ->selectRaw('SUM(correct_answers) as correct_answers')
            ->selectRaw('SUM(total_questions) as total_questions')
            ->selectRaw('AVG(CASE WHEN finished_at IS NOT NULL THEN accuracy ELSE NULL END) as avg_accuracy')
            ->selectRaw('MAX(accuracy) as best_accuracy')
            ->selectRaw('MAX(finished_at) as last_finished_at')
            ->first();

        $simulatedTotal = (int) ($simulated->total ?? 0);
        $simulatedFinished = (int) ($simulated->finished ?? 0);
        $simulatedAccuracy = $simulatedFinished > 0 ? round((float) ($simulated->avg_accuracy ?? 0), 2) : 0.0;

        $bySubject = DB::table('user_answers')
            ->join('study_sessions', 'user_answers.study_session_id', '=', 'study_sessions.id')
            ->join('questions', 'user_answers.question_id', '=', 'questions.id')
            ->join('subjects', 'questions.subject_id', '=', 'subjects.id')
            ->where('user_answers.user_id', $user->id)
            ->where('study_sessions.course_id', $course->id)
            ->groupBy('subjects.id', 'subjects.name')
            ->select('subjects.id', 'subjects.name')
            ->selectRaw('COUNT(*) as answered')
            ->selectRaw('SUM(CASE WHEN user_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct')
            ->orderByDesc('answered')
            ->get()
            ->map(fn ($row) => $this->withAccuracy($row));

        $byTopic = DB::table('user_answers')
            ->join('study_sessions', 'user_answers.study_session_id', '=', 'study_sessions.id')
            ->join('questions', 'user_answers.question_id', '=', 'questions.id')
            ->leftJoin('topics', 'questions.topic_id', '=', 'topics.id')
            ->where('user_answers.user_id', $user->id)
            ->where('study_sessions.course_id', $course->id)
            ->groupBy('topics.id', 'topics.name')
            ->select('topics.id', DB::raw('COALESCE(topics.name, "Sem tópico") as name'))
            ->selectRaw('COUNT(*) as answered')
            ->selectRaw('SUM(CASE WHEN user_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct')
            ->orderByDesc('answered')
            ->limit(15)
            ->get()
            ->map(fn ($row) => $this->withAccuracy($row))
            ->sortBy('accuracy')
            ->values();

        $byDifficulty = DB::table('user_answers')
            ->join('study_sessions', 'user_answers.study_session_id', '=', 'study_sessions.id')
            ->join('questions', 'user_answers.question_id', '=', 'questions.id')
            ->where('user_answers.user_id', $user->id)
            ->where('study_sessions.course_id', $course->id)
            ->groupBy('questions.difficulty')
            ->select('questions.difficulty')
            ->selectRaw('COUNT(*) as answered')
            ->selectRaw('SUM(CASE WHEN user_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct')
            ->orderByRaw("FIELD(questions.difficulty, 'easy', 'medium', 'hard')")
            ->get()
            ->map(fn ($row) => $this->withAccuracy($row));

        $recentAnswers = DB::table('user_answers')
            ->join('study_sessions', 'user_answers.study_session_id', '=', 'study_sessions.id')
            ->join('questions', 'user_answers.question_id', '=', 'questions.id')
            ->join('subjects', 'questions.subject_id', '=', 'subjects.id')
            ->leftJoin('topics', 'questions.topic_id', '=', 'topics.id')
            ->where('user_answers.user_id', $user->id)
            ->where('study_sessions.course_id', $course->id)
            ->orderByDesc('user_answers.answered_at')
            ->limit(10)
            ->select(['questions.id as question_id', 'questions.statement', 'subjects.name as subject_name', 'topics.name as topic_name', 'user_answers.is_correct', 'user_answers.answered_at'])
            ->get()
            ->map(function ($row) {
                $row->short_statement = Str::limit(strip_tags((string) $row->statement), 120);
                return $row;
            });

        $reviewQuestions = DB::table('user_answers')
            ->join('study_sessions', 'user_answers.study_session_id', '=', 'study_sessions.id')
            ->join('questions', 'user_answers.question_id', '=', 'questions.id')
            ->join('subjects', 'questions.subject_id', '=', 'subjects.id')
            ->leftJoin('topics', 'questions.topic_id', '=', 'topics.id')
            ->where('user_answers.user_id', $user->id)
            ->where('study_sessions.course_id', $course->id)
            ->where('user_answers.is_correct', false)
            ->groupBy('questions.id', 'questions.statement', 'subjects.name', 'topics.name')
            ->orderByDesc('wrong_count')
            ->orderByDesc('last_wrong_at')
            ->limit(10)
            ->select(['questions.id as question_id', 'questions.statement', 'subjects.name as subject_name', 'topics.name as topic_name'])
            ->selectRaw('COUNT(*) as wrong_count')
            ->selectRaw('MAX(user_answers.answered_at) as last_wrong_at')
            ->get()
            ->map(function ($row) {
                $row->short_statement = Str::limit(strip_tags((string) $row->statement), 120);
                return $row;
            });

        $latestSimulated = DB::table('simulated_exams')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('student.courses.performance', compact('course', 'availableQuestions', 'trainingAnswered', 'trainingCorrect', 'trainingWrong', 'trainingAccuracy', 'distinctAnsweredQuestions', 'unansweredQuestions', 'training', 'simulated', 'simulatedTotal', 'simulatedFinished', 'simulatedAccuracy', 'bySubject', 'byTopic', 'byDifficulty', 'recentAnswers', 'reviewQuestions', 'latestSimulated'));
    }

    private function withAccuracy(object $row): object
    {
        $row->answered = (int) ($row->answered ?? 0);
        $row->correct = (int) ($row->correct ?? 0);
        $row->wrong = max(0, $row->answered - $row->correct);
        $row->accuracy = $this->percentage($row->correct, $row->answered);
        return $row;
    }

    private function percentage(int $part, int $total): float
    {
        return $total <= 0 ? 0.0 : round(($part / $total) * 100, 2);
    }

    private function courseQuestionQuery(Course $course): QueryBuilder
    {
        $query = DB::table('questions')->whereIn('questions.status', Question::STUDENT_VISIBLE_STATUSES);

        if ($course->corporation_id) {
            $query->where(fn ($q) => $q->where('questions.corporation_id', $course->corporation_id)->orWhereNull('questions.corporation_id'));
        }

        if ($course->inherit_exam_scope && $course->exam_id) {
            $query->where(fn ($q) => $q->where('questions.exam_id', $course->exam_id)->orWhereNull('questions.exam_id'));

            $subjectIds = DB::table('exam_subjects')->where('exam_id', $course->exam_id)->where('is_active', true)->pluck('subject_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
            if (!empty($subjectIds)) $query->whereIn('questions.subject_id', $subjectIds);

            $topicIds = DB::table('exam_subject_topics')->join('exam_subjects', 'exam_subject_topics.exam_subject_id', '=', 'exam_subjects.id')->where('exam_subjects.exam_id', $course->exam_id)->where('exam_subjects.is_active', true)->where('exam_subject_topics.is_active', true)->pluck('exam_subject_topics.topic_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
            if (!empty($topicIds)) $query->where(fn ($q) => $q->whereIn('questions.topic_id', $topicIds)->orWhereNull('questions.topic_id'));

            $sourceMaterialIds = DB::table('exam_subject_source_materials')->join('exam_subjects', 'exam_subject_source_materials.exam_subject_id', '=', 'exam_subjects.id')->where('exam_subjects.exam_id', $course->exam_id)->where('exam_subjects.is_active', true)->where('exam_subject_source_materials.is_active', true)->pluck('exam_subject_source_materials.source_material_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
            if (!empty($sourceMaterialIds)) $query->where(fn ($q) => $q->whereIn('questions.source_material_id', $sourceMaterialIds)->orWhereNull('questions.source_material_id'));

            return $query;
        }

        $subjectIds = DB::table('course_subjects')->where('course_id', $course->id)->where('is_active', true)->pluck('subject_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        if (!empty($subjectIds)) $query->whereIn('questions.subject_id', $subjectIds);

        $topicIds = DB::table('course_topics')->where('course_id', $course->id)->where('is_active', true)->pluck('topic_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        if (!empty($topicIds)) $query->whereIn('questions.topic_id', $topicIds);

        $sourceMaterialIds = DB::table('course_source_materials')->where('course_id', $course->id)->where('is_active', true)->pluck('source_material_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        if (!empty($sourceMaterialIds)) $query->whereIn('questions.source_material_id', $sourceMaterialIds);

        return $query;
    }
}
