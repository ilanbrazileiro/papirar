<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamBoard;
use App\Models\ExamSubject;
use App\Models\ExamSubjectTopic;
use App\Models\SourceMaterial;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CatalogCourseApiController extends Controller
{
    public function courses(Request $request): JsonResponse
    {
        $data = $request->validate([
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
            $query->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('slug', 'like', "%{$term}%"));
        }
        foreach (['active', 'is_public', 'corporation_id', 'exam_id'] as $field) {
            if (array_key_exists($field, $data)) {
                $query->where($field, $data[$field]);
            }
        }

        return response()->json($query->orderBy('title')->paginate($data['per_page'] ?? 25));
    }

    public function course(Course $course): JsonResponse
    {
        $course->load(['corporation:id,name', 'exam:id,title,status', 'subjects:id,name', 'topics:id,subject_id,name', 'sourceMaterials:id,subject_id,title', 'includedCourses:id,title']);

        return response()->json(['data' => $course]);
    }

    public function courseCoverage(Course $course): JsonResponse
    {
        $scope = $this->courseScope($course);
        $subjects = Subject::query()->whereIn('id', $scope['subject_ids'])->orderBy('name')->get(['id', 'name']);

        $rows = $subjects->map(function (Subject $subject) use ($scope) {
            $query = DB::table('questions')->where('subject_id', $subject->id);
            if ($scope['topic_ids']) {
                $query->whereIn('topic_id', $scope['topic_ids']);
            }
            if ($scope['source_material_ids']) {
                $query->whereIn('source_material_id', $scope['source_material_ids']);
            }

            $counts = (clone $query)
                ->selectRaw('COUNT(*) total')
                ->selectRaw("SUM(CASE WHEN status='published' THEN 1 ELSE 0 END) published")
                ->selectRaw("SUM(CASE WHEN status='reviewed' THEN 1 ELSE 0 END) reviewed")
                ->selectRaw("SUM(CASE WHEN status='draft' THEN 1 ELSE 0 END) draft")
                ->selectRaw("SUM(CASE WHEN status='archived' THEN 1 ELSE 0 END) archived")
                ->first();

            return [
                'subject_id' => $subject->id,
                'subject' => $subject->name,
                'available' => (int) ($counts->published ?? 0) + (int) ($counts->reviewed ?? 0),
                'published' => (int) ($counts->published ?? 0),
                'reviewed' => (int) ($counts->reviewed ?? 0),
                'draft' => (int) ($counts->draft ?? 0),
                'archived' => (int) ($counts->archived ?? 0),
                'total' => (int) ($counts->total ?? 0),
            ];
        })->values();

        return response()->json([
            'data' => [
                'course' => ['id' => $course->id, 'title' => $course->title],
                'scope' => $scope,
                'subjects' => $rows,
                'totals' => [
                    'available' => $rows->sum('available'),
                    'published' => $rows->sum('published'),
                    'reviewed' => $rows->sum('reviewed'),
                    'draft' => $rows->sum('draft'),
                    'archived' => $rows->sum('archived'),
                    'empty_subjects' => $rows->where('total', 0)->count(),
                ],
            ],
        ]);
    }

    public function storeCorporation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:corporations,name'],
            'slug' => ['nullable', 'string', 'max:160', 'unique:corporations,slug'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = trim((string) ($data['slug'] ?? '')) ?: Str::slug($data['name']);
        $data['active'] = $data['active'] ?? true;

        return response()->json(['data' => Corporation::create($data)], 201);
    }

    public function updateCorporation(Request $request, Corporation $corporation): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:150', Rule::unique('corporations', 'name')->ignore($corporation->id)],
            'slug' => ['sometimes', 'string', 'max:160', Rule::unique('corporations', 'slug')->ignore($corporation->id)],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);
        $corporation->update($data);

        return response()->json(['data' => $corporation->fresh()]);
    }

    public function storeExamBoard(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:exam_boards,name'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:exam_boards,slug'],
            'description' => ['nullable', 'string', 'max:5000'],
            'active' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = trim((string) ($data['slug'] ?? '')) ?: Str::slug($data['name']);
        $data['active'] = $data['active'] ?? true;

        return response()->json(['data' => ExamBoard::create($data)], 201);
    }

    public function updateExamBoard(Request $request, ExamBoard $examBoard): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('exam_boards', 'name')->ignore($examBoard->id)],
            'slug' => ['sometimes', 'string', 'max:120', Rule::unique('exam_boards', 'slug')->ignore($examBoard->id)],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);
        $examBoard->update($data);

        return response()->json(['data' => $examBoard->fresh()]);
    }

    public function storeSourceMaterial(Request $request): JsonResponse
    {
        $data = $this->sourceMaterialData($request);
        return response()->json(['data' => SourceMaterial::create($data)], 201);
    }

    public function updateSourceMaterial(Request $request, SourceMaterial $sourceMaterial): JsonResponse
    {
        $data = $this->sourceMaterialData($request, $sourceMaterial->id, true);
        $sourceMaterial->update($data);
        return response()->json(['data' => $sourceMaterial->fresh()]);
    }

    public function storeExam(Request $request): JsonResponse
    {
        $data = $this->examData($request);
        $exam = DB::transaction(function () use ($data) {
            $scope = $data['scope'];
            unset($data['scope']);
            $exam = Exam::create($data);
            $this->replaceExamScope($exam, $scope);
            return $exam;
        });

        return response()->json(['data' => $exam->fresh()], 201);
    }

    public function updateExam(Request $request, Exam $exam): JsonResponse
    {
        $data = $request->validate([
            'corporation_id' => ['sometimes', 'integer', 'exists:corporations,id'],
            'title' => ['sometimes', 'string', 'max:180'],
            'year' => ['sometimes', 'integer', 'between:1900,2100'],
            'exam_type' => ['sometimes', 'string', 'max:50'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);
        $exam->update($data);
        return response()->json(['data' => $exam->fresh()]);
    }

    public function replaceExamScopeApi(Request $request, Exam $exam): JsonResponse
    {
        $data = $request->validate([
            'confirm' => ['required', 'accepted'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'subjects.*.topic_ids' => ['nullable', 'array'],
            'subjects.*.topic_ids.*' => ['integer', 'exists:topics,id'],
        ]);

        DB::transaction(fn () => $this->replaceExamScope($exam, $data['subjects']));

        return response()->json(['message' => 'Escopo do concurso substituído com confirmação explícita.', 'data' => ['exam_id' => $exam->id]]);
    }

    private function sourceMaterialData(Request $request, ?int $ignoreId = null, bool $partial = false): array
    {
        $sometimes = $partial ? 'sometimes' : 'required';
        $data = $request->validate([
            'corporation_id' => ['sometimes', 'nullable', 'integer', 'exists:corporations,id'],
            'subject_id' => [$sometimes, 'integer', 'exists:subjects,id'],
            'title' => [$sometimes, 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('source_materials', 'slug')->ignore($ignoreId)],
            'description' => ['sometimes', 'nullable', 'string'],
            'material_type' => [$sometimes, 'string', 'max:50'],
            'year' => ['sometimes', 'nullable', 'integer', 'min:1800', 'max:'.((int) date('Y') + 5)],
            'reference_code' => ['sometimes', 'nullable', 'string', 'max:100'],
            'url' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);
        if (! $partial && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        return $data;
    }

    private function examData(Request $request): array
    {
        $data = $request->validate([
            'corporation_id' => ['required', 'integer', 'exists:corporations,id'],
            'title' => ['required', 'string', 'max:180'],
            'year' => ['required', 'integer', 'between:1900,2100'],
            'exam_type' => ['required', 'string', 'max:50'],
            'status' => ['required', Rule::in([Exam::STATUS_PLANNED, Exam::STATUS_PUBLISHED])],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
            'scope' => ['required', 'array', 'min:1'],
            'scope.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'scope.*.topic_ids' => ['nullable', 'array'],
            'scope.*.topic_ids.*' => ['integer', 'exists:topics,id'],
        ]);
        $exists = Exam::query()->where('corporation_id', $data['corporation_id'])->where('title', $data['title'])->where('year', $data['year'])->exists();
        if ($exists) {
            throw ValidationException::withMessages(['title' => ['Já existe concurso com esse título, corporação e ano.']]);
        }
        $data['active'] = $data['active'] ?? true;
        return $data;
    }

    private function replaceExamScope(Exam $exam, array $subjects): void
    {
        $subjectIds = collect($subjects)->pluck('subject_id')->map(fn ($id) => (int) $id)->unique()->values();
        ExamSubject::query()->where('exam_id', $exam->id)->whereNotIn('subject_id', $subjectIds)->delete();

        foreach ($subjects as $index => $item) {
            $subjectId = (int) $item['subject_id'];
            $examSubject = ExamSubject::query()->updateOrCreate(
                ['exam_id' => $exam->id, 'subject_id' => $subjectId],
                ['sort_order' => $index + 1, 'is_active' => true]
            );
            $topicIds = collect($item['topic_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
            $valid = Topic::query()->where('subject_id', $subjectId)->whereIn('id', $topicIds)->pluck('id')->map(fn ($id) => (int) $id)->all();
            ExamSubjectTopic::query()->where('exam_subject_id', $examSubject->id)->whereNotIn('topic_id', $valid ?: [0])->delete();
            foreach ($valid as $topicIndex => $topicId) {
                ExamSubjectTopic::query()->updateOrCreate(
                    ['exam_subject_id' => $examSubject->id, 'topic_id' => $topicId],
                    ['sort_order' => $topicIndex + 1, 'is_active' => true]
                );
            }
        }
    }

    private function courseScope(Course $course): array
    {
        if ($course->inherit_exam_scope && $course->exam_id) {
            return [
                'source' => 'exam',
                'subject_ids' => DB::table('exam_subjects')->where('exam_id', $course->exam_id)->where('is_active', true)->pluck('subject_id')->map(fn ($id) => (int) $id)->all(),
                'topic_ids' => DB::table('exam_subject_topics')->join('exam_subjects', 'exam_subject_topics.exam_subject_id', '=', 'exam_subjects.id')->where('exam_subjects.exam_id', $course->exam_id)->where('exam_subject_topics.is_active', true)->pluck('exam_subject_topics.topic_id')->map(fn ($id) => (int) $id)->all(),
                'source_material_ids' => DB::table('exam_subject_source_materials')->join('exam_subjects', 'exam_subject_source_materials.exam_subject_id', '=', 'exam_subjects.id')->where('exam_subjects.exam_id', $course->exam_id)->where('exam_subject_source_materials.is_active', true)->pluck('exam_subject_source_materials.source_material_id')->filter()->map(fn ($id) => (int) $id)->all(),
            ];
        }

        return [
            'source' => 'course',
            'subject_ids' => DB::table('course_subjects')->where('course_id', $course->id)->where('is_active', true)->pluck('subject_id')->map(fn ($id) => (int) $id)->all(),
            'topic_ids' => DB::table('course_topics')->where('course_id', $course->id)->where('is_active', true)->pluck('topic_id')->map(fn ($id) => (int) $id)->all(),
            'source_material_ids' => DB::table('course_source_materials')->where('course_id', $course->id)->where('is_active', true)->pluck('source_material_id')->filter()->map(fn ($id) => (int) $id)->all(),
        ];
    }
}
