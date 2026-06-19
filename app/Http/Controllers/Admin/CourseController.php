<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Course;
use App\Models\CourseSourceMaterial;
use App\Models\CourseSubject;
use App\Models\CourseTopic;
use App\Models\Exam;
use App\Models\SourceMaterial;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $courseType = $request->get('course_type');
        $status = $request->get('status');

        $courses = Course::query()
            ->with(['corporation', 'exam'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->when($courseType, fn ($q) => $q->where('course_type', $courseType))
            ->when($status === 'active', fn ($q) => $q->where('active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('active', false))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        $typeOptions = Course::typeOptions();

        return view('admin.courses.index', compact('courses', 'search', 'courseType', 'status', 'typeOptions'));
    }

    public function create()
    {
        $course = new Course([
            'course_type' => Course::TYPE_INTERNAL_EXAM,
            'inherit_exam_scope' => true,
            'active' => true,
            'is_public' => true,
            'is_trial_available' => true,
            'trial_days' => 7,
            'price' => 0,
            'quarterly_price' => null,
            'semiannual_price' => null,
            'sort_order' => 0,
        ]);

        return view('admin.courses.create', $this->formData($course));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data = $this->handleCourseCoverUpload($request, $data);

        DB::transaction(function () use ($request, $data) {
            $course = Course::create($data);
            $this->syncCourseScope($course, $request);
            $this->syncBundleItems($course, $request);
        });

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Curso cadastrado com sucesso.');
    }

    public function show(Course $course)
    {
        $course->load([
            'corporation',
            'exam.corporation',
            'subjects',
            'topics.subject',
            'sourceMaterials.subject',
            'includedCourses',
        ]);

        return view('admin.courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        $course->load(['courseSubjects', 'courseTopics', 'courseSourceMaterials', 'includedCourses']);

        return view('admin.courses.edit', $this->formData($course));
    }

    public function update(Request $request, Course $course)
    {
        $data = $this->validatedData($request, $course->id);
        $data = $this->handleCourseCoverUpload($request, $data, $course);

        DB::transaction(function () use ($request, $course, $data) {
            $course->update($data);
            $this->syncCourseScope($course, $request);
            $this->syncBundleItems($course, $request);
        });

        return redirect()
            ->route('admin.courses.edit', $course)
            ->with('success', 'Curso atualizado com sucesso.');
    }

    public function destroy(Course $course)
    {
        if ($course->accesses()->exists()) {
            return redirect()
                ->route('admin.courses.index')
                ->with('error', 'Este curso possui acessos vinculados e não pode ser removido. Desative o curso se ele não deve mais ser vendido.');
        }

        if ($course->cover_image_path) {
            Storage::disk('public')->delete($course->cover_image_path);
        }

        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Curso removido com sucesso.');
    }

    private function formData(Course $course): array
    {
        $corporations = Corporation::query()->orderBy('name')->get();

        $exams = Exam::query()
            ->with('corporation')
            ->where('active', true)
            ->orderByDesc('year')
            ->orderBy('title')
            ->get();

        $subjects = Subject::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $topicsBySubject = Topic::query()
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->groupBy('subject_id');

        $sourceMaterials = SourceMaterial::query()
            ->with('subject')
            ->where('active', true)
            ->orderBy('title')
            ->get();

        $availableCourses = Course::query()
            ->when($course->exists, fn ($q) => $q->where('id', '!=', $course->id))
            ->where('active', true)
            ->orderBy('title')
            ->get();

        $selectedSubjects = [];
        $selectedTopicsBySubject = [];
        $selectedSourceMaterials = [];
        $selectedBundleCourses = [];

        if ($course->exists) {
            $selectedSubjects = $course->courseSubjects
                ->where('is_active', true)
                ->pluck('subject_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $topicIds = $course->courseTopics
                ->where('is_active', true)
                ->pluck('topic_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($topicsBySubject as $subjectId => $topics) {
                $selectedTopicsBySubject[(int) $subjectId] = $topics
                    ->whereIn('id', $topicIds)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }

            $selectedSourceMaterials = $course->courseSourceMaterials
                ->where('is_active', true)
                ->pluck('source_material_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $selectedBundleCourses = $course->includedCourses
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return compact(
            'course',
            'corporations',
            'exams',
            'subjects',
            'topicsBySubject',
            'sourceMaterials',
            'availableCourses',
            'selectedSubjects',
            'selectedTopicsBySubject',
            'selectedSourceMaterials',
            'selectedBundleCourses'
        ) + [
            'typeOptions' => Course::typeOptions(),
        ];
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'corporation_id' => ['nullable', 'integer', 'exists:corporations,id'],
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable',
                'string',
                'max:200',
                Rule::unique('courses', 'slug')->ignore($ignoreId),
            ],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'sales_headline' => ['nullable', 'string', 'max:180'],
            'sales_badge' => ['nullable', 'string', 'max:80'],
            'sales_bullets_text' => ['nullable', 'string', 'max:1500'],
            'target_audience' => ['nullable', 'string', 'max:180'],
            'workload_label' => ['nullable', 'string', 'max:80'],
            'guarantee_text' => ['nullable', 'string', 'max:180'],
            'course_type' => ['required', Rule::in(array_keys(Course::typeOptions()))],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'quarterly_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'semiannual_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'inherit_exam_scope' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
            'is_trial_available' => ['nullable', 'boolean'],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'subjects' => ['nullable', 'array'],
            'subjects.*.selected' => ['nullable', 'boolean'],
            'subjects.*.topics' => ['nullable', 'array'],
            'subjects.*.topics.*' => ['integer', 'exists:topics,id'],
            'source_materials' => ['nullable', 'array'],
            'source_materials.*' => ['integer', 'exists:source_materials,id'],
            'bundle_courses' => ['nullable', 'array'],
            'bundle_courses.*' => ['integer', 'exists:courses,id'],
        ]);

        $slug = trim((string) ($validated['slug'] ?? ''));
        if ($slug === '') {
            $slug = Str::slug($validated['title']);
        } else {
            $slug = Str::slug($slug);
        }

        if ($slug === '') {
            abort(back()->withErrors(['slug' => 'Não foi possível gerar o slug do curso.'])->withInput());
        }

        $exists = Course::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            abort(back()->withErrors(['slug' => 'Já existe um curso com este slug.'])->withInput());
        }

        $trialDays = (int) ($validated['trial_days'] ?? 7);
        $trialDays = max(1, min($trialDays, 30));

        return [
            'corporation_id' => $validated['corporation_id'] ?? null,
            'exam_id' => $validated['exam_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $slug,
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'sales_headline' => $validated['sales_headline'] ?? null,
            'sales_badge' => $validated['sales_badge'] ?? null,
            'sales_bullets' => $this->normalizeSalesBullets($validated['sales_bullets_text'] ?? null),
            'target_audience' => $validated['target_audience'] ?? null,
            'workload_label' => $validated['workload_label'] ?? null,
            'guarantee_text' => $validated['guarantee_text'] ?? null,
            'course_type' => $validated['course_type'],
            'price' => round((float) $validated['price'], 2),
            'quarterly_price' => $this->nullableMoney($validated['quarterly_price'] ?? null),
            'semiannual_price' => $this->nullableMoney($validated['semiannual_price'] ?? null),
            'inherit_exam_scope' => (bool) ($validated['inherit_exam_scope'] ?? false),
            'active' => (bool) ($validated['active'] ?? false),
            'is_public' => (bool) ($validated['is_public'] ?? false),
            'is_trial_available' => (bool) ($validated['is_trial_available'] ?? false),
            'trial_days' => $trialDays,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];
    }

    private function normalizeSalesBullets(?string $text): ?array
    {
        $bullets = collect(preg_split('/\r\n|\r|\n/', (string) $text))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->take(8)
            ->values()
            ->all();

        return empty($bullets) ? null : $bullets;
    }

    private function handleCourseCoverUpload(Request $request, array $data, ?Course $course = null): array
    {
        if ($request->boolean('remove_cover_image') && $course?->cover_image_path) {
            Storage::disk('public')->delete($course->cover_image_path);
            $data['cover_image_path'] = null;
        }

        if ($request->hasFile('cover_image')) {
            if ($course?->cover_image_path) {
                Storage::disk('public')->delete($course->cover_image_path);
            }

            $data['cover_image_path'] = $request->file('cover_image')->store('courses/covers', 'public');
        }

        return $data;
    }

    private function nullableMoney($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $amount = round((float) $value, 2);

        return $amount > 0 ? $amount : null;
    }

    private function syncCourseScope(Course $course, Request $request): void
    {
        $subjectsInput = $request->input('subjects', []);

        $selectedSubjectIds = collect($subjectsInput)
            ->filter(fn ($data) => (bool) ($data['selected'] ?? false))
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values();

        $validSubjectIds = Subject::query()
            ->whereIn('id', $selectedSubjectIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        CourseSubject::query()->where('course_id', $course->id)->delete();
        CourseTopic::query()->where('course_id', $course->id)->delete();

        foreach ($validSubjectIds as $index => $subjectId) {
            CourseSubject::create([
                'course_id' => $course->id,
                'subject_id' => $subjectId,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);

            $topicIds = collect($subjectsInput[$subjectId]['topics'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $validTopicIds = Topic::query()
                ->where('subject_id', $subjectId)
                ->whereIn('id', $topicIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($validTopicIds as $topicIndex => $topicId) {
                CourseTopic::create([
                    'course_id' => $course->id,
                    'topic_id' => $topicId,
                    'sort_order' => $topicIndex + 1,
                    'is_active' => true,
                ]);
            }
        }

        $sourceMaterialIds = collect($request->input('source_materials', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $validSourceMaterialIds = SourceMaterial::query()
            ->whereIn('id', $sourceMaterialIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        CourseSourceMaterial::query()->where('course_id', $course->id)->delete();

        foreach ($validSourceMaterialIds as $index => $sourceMaterialId) {
            CourseSourceMaterial::create([
                'course_id' => $course->id,
                'source_material_id' => $sourceMaterialId,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }

    private function syncBundleItems(Course $course, Request $request): void
    {
        $bundleCourseIds = collect($request->input('bundle_courses', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id !== (int) $course->id)
            ->unique()
            ->values()
            ->all();

        $validBundleCourseIds = Course::query()
            ->whereIn('id', $bundleCourseIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $course->includedCourses()->sync($course->course_type === Course::TYPE_COMBO ? $validBundleCourseIds : []);
    }
}
