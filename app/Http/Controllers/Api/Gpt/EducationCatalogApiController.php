<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Adiciona a escolaridade aos endpoints de catálogo GPT sem reproduzir
 * as regras de negócio dos controllers originais. O valor é independente
 * entre cursos e concursos.
 */
class EducationCatalogApiController extends Controller
{
    public function storeExam(Request $request): JsonResponse
    {
        $education = $this->validatedEducation($request);

        return DB::transaction(function () use ($request, $education): JsonResponse {
            $response = app(CatalogCourseApiController::class)->storeExam($request);
            if ($education === null) {
                return $response;
            }

            $id = $response->getData(true)['data']['id'];
            $exam = Exam::findOrFail($id);
            $exam->update(['education_level' => $education]);

            return response()->json(['data' => $exam->fresh()], 201);
        });
    }

    public function updateExam(Request $request, Exam $exam): JsonResponse
    {
        $education = $this->validatedEducation($request);

        return DB::transaction(function () use ($request, $exam, $education): JsonResponse {
            $response = app(CatalogCourseApiController::class)->updateExam($request, $exam);
            if ($education === null) {
                return $response;
            }

            $exam->update(['education_level' => $education]);

            return response()->json(['data' => $exam->fresh()]);
        });
    }

    public function storeCourse(Request $request): JsonResponse
    {
        $education = $this->validatedEducation($request);

        return DB::transaction(function () use ($request, $education): JsonResponse {
            $response = app(CoursePreparationApiController::class)->store($request);
            if ($education === null) {
                return $response;
            }

            $id = $response->getData(true)['data']['id'];
            $course = Course::findOrFail($id);
            $course->update(['education_level' => $education]);

            return response()->json(['data' => $course->fresh()], 201);
        });
    }

    public function updateCourseMetadata(Request $request, Course $course): JsonResponse
    {
        $education = $this->validatedEducation($request);

        return DB::transaction(function () use ($request, $course, $education): JsonResponse {
            $response = app(CoursePreparationApiController::class)->updateMetadata($request, $course);
            if ($education === null) {
                return $response;
            }

            $course->update(['education_level' => $education]);

            return response()->json(['data' => $course->fresh()]);
        });
    }

    public function courses(Request $request): JsonResponse
    {
        if (! $request->filled('education_level')) {
            return app(CatalogCourseApiController::class)->courses($request);
        }

        $data = $request->validate([
            'education_level' => ['required', Rule::in(['medium', 'higher', 'unclassified'])],
            'q' => ['nullable', 'string', 'max:180'],
            'active' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
            'corporation_id' => ['nullable', 'integer', 'exists:corporations,id'],
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Course::query()->with(['corporation:id,name', 'exam:id,title,status']);
        if (! empty($data['q'])) {
            $term = $data['q'];
            $query->where(fn ($q) => $q->where('title', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%"));
        }
        foreach (['active', 'is_public', 'corporation_id', 'exam_id'] as $field) {
            if (array_key_exists($field, $data)) {
                $query->where($field, $data[$field]);
            }
        }

        if ($data['education_level'] === 'unclassified') {
            $query->whereNull('education_level');
        } else {
            $query->where('education_level', $data['education_level']);
        }

        return response()->json($query->orderBy('title')->paginate($data['per_page'] ?? 25));
    }

    private function validatedEducation(Request $request): ?string
    {
        $data = $request->validate([
            'education_level' => ['sometimes', 'required', Rule::in(['medium', 'higher'])],
        ]);

        return $data['education_level'] ?? null;
    }
}
