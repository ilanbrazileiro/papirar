<?php

namespace App\Services\Education;

use App\Models\Course;
use App\Support\EducationLevel;
use Illuminate\Support\Facades\DB;

/**
 * Impede que sessões criadas antes da classificação exponham questões incompatíveis.
 * Não modifica ou exclui respostas, tentativas nem histórico.
 */
final class CourseQuestionAccess
{
    public function assertStudySessionEligible(int $sessionId, Course $course): void
    {
        $this->assertCompatible('study_session_questions', 'study_session_id', $sessionId, $course);
    }

    public function assertSimulatedExamEligible(int $simulatedExamId, Course $course): void
    {
        $this->assertCompatible('simulated_exam_questions', 'simulated_exam_id', $simulatedExamId, $course);
    }

    private function assertCompatible(string $table, string $sessionColumn, int $sessionId, Course $course): void
    {
        $allowedLevels = match ($course->education_level) {
            EducationLevel::MEDIUM => [EducationLevel::MEDIUM],
            EducationLevel::HIGHER => [EducationLevel::MEDIUM, EducationLevel::HIGHER],
            default => [],
        };

        abort_if($allowedLevels === [], 403, 'Classifique a escolaridade do curso para acessar esta sessão.');

        $hasIncompatibleQuestion = DB::table($table)
            ->leftJoin('questions', 'questions.id', '=', $table . '.question_id')
            ->where($table . '.' . $sessionColumn, $sessionId)
            ->where(function ($query) use ($allowedLevels) {
                $query->whereNull('questions.education_level')
                    ->orWhereNotIn('questions.education_level', $allowedLevels);
            })
            ->exists();

        abort_if(
            $hasIncompatibleQuestion,
            403,
            'Esta sessão contém questão incompatível ou não classificada. Os dados foram preservados; solicite a revisão da escolaridade.'
        );
    }
}
