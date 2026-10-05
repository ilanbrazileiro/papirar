<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSourceMaterial;
use App\Models\CourseSubject;
use App\Models\CourseTopic;
use App\Models\SourceMaterial;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CoursePreparationApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'corporation_id' => ['nullable', 'integer', 'exists:corporations,id'],
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', 'unique:courses,slug'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'course_type' => ['required', Rule::in(array_keys(Course::typeOptions()))],
            'inherit_exam_scope' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);

        $data['slug'] = trim((string) ($data['slug'] ?? '')) ?: Str::slug($data['title']);
        $data['inherit_exam_scope'] = (bool) ($data['inherit_exam_scope'] ?? true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['price'] = 0;
        $data['active'] = false;
        $data['is_public'] = false;
        $data['landing_enabled'] = false;
        $data['is_trial_available'] = true;
        $data['trial_days'] = 7;

        if ($data['inherit_exam_scope'] && empty($data['exam_id'])) {
            throw ValidationException::withMessages(['exam_id' => ['Curso com escopo herdado precisa estar vinculado a um concurso.']]);
        }

        return response()->json(['data' => Course::create($data)], 201);
    }

    public function updateMetadata(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'corporation_id' => ['sometimes', 'nullable', 'integer', 'exists:corporations,id'],
            'exam_id' => ['sometimes', 'nullable', 'integer', 'exists:exams,id'],
            'title' => ['sometimes', 'string', 'max:180'],
            'slug' => ['sometimes', 'string', 'max:200', Rule::unique('courses', 'slug')->ignore($course->id)],
            'short_description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'course_type' => ['sometimes', Rule::in(array_keys(Course::typeOptions()))],
            'inherit_exam_scope' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:999999'],
        ]);

        $examId = array_key_exists('exam_id', $data) ? $data['exam_id'] : $course->exam_id;
        $inherit = array_key_exists('inherit_exam_scope', $data) ? (bool) $data['inherit_exam_scope'] : (bool) $course->inherit_exam_scope;
        if ($inherit && ! $examId) {
            throw ValidationException::withMessages(['exam_id' => ['Curso com escopo herdado precisa estar vinculado a um concurso.']]);
        }

        $course->update($data);
        return response()->json(['data' => $course->fresh()]);
    }

    public function replaceScope(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'confirm' => ['required', 'accepted'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'subjects.*.topic_ids' => ['nullable', 'array'],
            'subjects.*.topic_ids.*' => ['integer', 'exists:topics,id'],
            'source_material_ids' => ['nullable', 'array'],
            'source_material_ids.*' => ['integer', 'exists:source_materials,id'],
        ]);

        if ($course->inherit_exam_scope) {
            throw ValidationException::withMessages(['inherit_exam_scope' => ['Desative a herança do concurso antes de definir escopo próprio do curso.']]);
        }

        DB::transaction(function () use ($course, $data) {
            CourseTopic::query()->where('course_id', $course->id)->delete();
            CourseSubject::query()->where('course_id', $course->id)->delete();
            CourseSourceMaterial::query()->where('course_id', $course->id)->delete();

            foreach ($data['subjects'] as $subjectIndex => $item) {
                $subjectId = (int) $item['subject_id'];
                CourseSubject::create([
                    'course_id' => $course->id,
                    'subject_id' => $subjectId,
                    'sort_order' => $subjectIndex + 1,
                    'is_active' => true,
                ]);

                $topicIds = collect($item['topic_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
                $validTopicIds = Topic::query()->where('subject_id', $subjectId)->whereIn('id', $topicIds)->pluck('id')->map(fn ($id) => (int) $id)->all();
                if (count($validTopicIds) !== $topicIds->count()) {
                    throw ValidationException::withMessages(['subjects' => ['Há tópico que não pertence à disciplina informada.']]);
                }
                foreach ($validTopicIds as $topicIndex => $topicId) {
                    CourseTopic::create([
                        'course_id' => $course->id,
                        'topic_id' => $topicId,
                        'sort_order' => $topicIndex + 1,
                        'is_active' => true,
                    ]);
                }
            }

            $selectedSubjectIds = collect($data['subjects'])->pluck('subject_id')->map(fn ($id) => (int) $id)->unique();
            $materials = SourceMaterial::query()->whereIn('id', $data['source_material_ids'] ?? [])->get(['id', 'subject_id']);
            foreach ($materials as $index => $material) {
                if (! $selectedSubjectIds->contains((int) $material->subject_id)) {
                    throw ValidationException::withMessages(['source_material_ids' => ['Material-fonte selecionado pertence a disciplina fora do escopo do curso.']]);
                }
                CourseSourceMaterial::create([
                    'course_id' => $course->id,
                    'source_material_id' => $material->id,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        });

        return response()->json(['message' => 'Escopo do curso substituído com confirmação explícita.', 'data' => ['course_id' => $course->id]]);
    }

    public function replaceBundle(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'confirm' => ['required', 'accepted'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'included_course_ids' => ['required', 'array'],
            'included_course_ids.*' => ['integer', 'exists:courses,id'],
        ]);
        if ($course->course_type !== Course::TYPE_COMBO) {
            throw ValidationException::withMessages(['course_type' => ['Somente cursos do tipo combo podem conter outros cursos.']]);
        }
        $ids = collect($data['included_course_ids'])->map(fn ($id) => (int) $id)->unique()->reject(fn ($id) => $id === $course->id)->values()->all();
        $course->includedCourses()->sync($ids);
        return response()->json(['message' => 'Composição do combo substituída com confirmação explícita.', 'data' => ['course_id' => $course->id, 'included_course_ids' => $ids]]);
    }

    public function updatePricing(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'confirm' => ['required', 'accepted'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'price' => ['sometimes', 'numeric', 'min:0', 'max:999999.99'],
            'quarterly_price' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999.99'],
            'semiannual_price' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999.99'],
            'is_trial_available' => ['sometimes', 'boolean'],
            'trial_days' => ['sometimes', 'integer', 'min:1', 'max:30'],
        ]);
        unset($data['confirm'], $data['reason']);
        $course->update($data);
        return response()->json(['data' => $course->fresh()]);
    }

    public function updateLanding(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'sales_headline' => ['sometimes', 'nullable', 'string', 'max:180'],
            'sales_badge' => ['sometimes', 'nullable', 'string', 'max:80'],
            'sales_bullets' => ['sometimes', 'array', 'max:8'],
            'sales_bullets.*' => ['string', 'max:300'],
            'target_audience' => ['sometimes', 'nullable', 'string', 'max:180'],
            'workload_label' => ['sometimes', 'nullable', 'string', 'max:80'],
            'guarantee_text' => ['sometimes', 'nullable', 'string', 'max:180'],
            'landing_headline' => ['sometimes', 'nullable', 'string', 'max:180'],
            'landing_subheadline' => ['sometimes', 'nullable', 'string', 'max:500'],
            'landing_problem_title' => ['sometimes', 'nullable', 'string', 'max:180'],
            'landing_problem_text' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'landing_cta_text' => ['sometimes', 'nullable', 'string', 'max:80'],
            'landing_final_title' => ['sometimes', 'nullable', 'string', 'max:180'],
            'landing_final_text' => ['sometimes', 'nullable', 'string', 'max:500'],
            'landing_final_cta_text' => ['sometimes', 'nullable', 'string', 'max:80'],
            'landing_seo_title' => ['sometimes', 'nullable', 'string', 'max:70'],
            'landing_seo_description' => ['sometimes', 'nullable', 'string', 'max:170'],
            'landing_question_id' => ['sometimes', 'nullable', 'integer', 'exists:questions,id'],
            'landing_show_performance' => ['sometimes', 'boolean'],
            'landing_show_error_review' => ['sometimes', 'boolean'],
            'landing_show_next_study' => ['sometimes', 'boolean'],
            'landing_show_schedule' => ['sometimes', 'boolean'],
            'landing_show_goals' => ['sometimes', 'boolean'],
            'landing_show_simulations' => ['sometimes', 'boolean'],
        ]);
        $course->update($data);
        return response()->json(['data' => $course->fresh()]);
    }

    public function setPublication(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'confirm' => ['required', 'accepted'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'active' => ['required', 'boolean'],
            'is_public' => ['required', 'boolean'],
            'landing_enabled' => ['required', 'boolean'],
        ]);
        $course->update([
            'active' => $data['active'],
            'is_public' => $data['is_public'],
            'landing_enabled' => $data['landing_enabled'],
        ]);
        return response()->json(['message' => 'Estado de publicação do curso atualizado com confirmação explícita.', 'data' => $course->fresh()]);
    }

    public function destroy(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'confirm' => ['required', 'accepted'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);
        if ($course->accesses()->exists()) {
            throw ValidationException::withMessages(['course' => ['Curso possui acessos vinculados; desative-o em vez de excluir.']]);
        }
        if ($course->subscriptions()->exists()) {
            throw ValidationException::withMessages(['course' => ['Curso possui assinaturas vinculadas; desative-o em vez de excluir.']]);
        }
        $id = $course->id;
        $course->delete();
        return response()->json(['message' => 'Curso excluído com confirmação explícita.', 'data' => ['course_id' => $id]]);
    }
}
