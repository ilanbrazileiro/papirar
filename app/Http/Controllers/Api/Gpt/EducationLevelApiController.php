<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Endpoints complementares de escolaridade para a API GPT.
 * Não alteram escopo acadêmico, status, gabaritos ou assinaturas.
 */
class EducationLevelApiController extends Controller
{
    public function updateQuestion(Request $request, Question $question): JsonResponse
    {
        if ($question->status === Question::STATUS_ARCHIVED) {
            return response()->json(['message' => 'Questões arquivadas não podem ser alteradas pelo Revisor.'], 409);
        }

        $data = $this->validateEducation($request);
        $old = $question->education_level;
        $question->update(['education_level' => $data['education_level']]);
        $this->audit('question', $question->id, $old, $data);

        return response()->json(['data' => ['id' => $question->id, 'education_level' => $question->fresh()->education_level]]);
    }

    public function updateCourse(Request $request, Course $course): JsonResponse
    {
        $data = $this->validateEducation($request);
        $old = $course->education_level;
        $course->update(['education_level' => $data['education_level']]);
        $this->audit('course', $course->id, $old, $data);

        return response()->json(['data' => ['id' => $course->id, 'education_level' => $course->fresh()->education_level]]);
    }

    public function updateExam(Request $request, Exam $exam): JsonResponse
    {
        $data = $this->validateEducation($request);
        $old = $exam->education_level;
        $exam->update(['education_level' => $data['education_level']]);
        $this->audit('exam', $exam->id, $old, $data);

        return response()->json(['data' => ['id' => $exam->id, 'education_level' => $exam->fresh()->education_level]]);
    }

    private function validateEducation(Request $request): array
    {
        return $request->validate([
            'education_level' => ['required', Rule::in(['medium', 'higher'])],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);
    }

    private function audit(string $entity, int $id, ?string $old, array $data): void
    {
        Log::info('GPT alterou escolaridade', [
            'entity' => $entity,
            'id' => $id,
            'previous_level' => $old,
            'education_level' => $data['education_level'],
            'reason' => $data['reason'],
        ]);
    }
}
