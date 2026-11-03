<?php

namespace App\Services\Education;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Question;
use App\Support\EducationLevel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Centraliza a elegibilidade por escolaridade, sem herança entre curso e concurso.
 * Deve ser aplicado em cada fluxo de seleção de questões nos próximos lotes.
 */
final class QuestionEligibility
{
    public function forCourse(Builder $query, Course $course): Builder
    {
        return EducationLevel::restrictQuestions($query, $course->education_level);
    }

    public function forExam(Builder $query, Exam $exam): Builder
    {
        return EducationLevel::restrictQuestions($query, $exam->education_level);
    }

    public function forLevel(Builder $query, ?string $level): Builder
    {
        return EducationLevel::restrictQuestions($query, $level);
    }

    public function courseAllows(Course $course, Question $question): bool
    {
        return $this->levelAllows($course->education_level, $question->education_level);
    }

    public function levelAllows(?string $targetLevel, ?string $questionLevel): bool
    {
        return match ($targetLevel) {
            EducationLevel::MEDIUM => $questionLevel === EducationLevel::MEDIUM,
            EducationLevel::HIGHER => in_array($questionLevel, [EducationLevel::MEDIUM, EducationLevel::HIGHER], true),
            default => false,
        };
    }
}
