<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSubjectSourceMaterial;
use App\Models\SourceMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExamSubjectSourceMaterialController extends Controller
{
    public function edit(Exam $exam)
    {
        $exam->load(['corporation', 'subjects']);

        $examSubjects = DB::table('exam_subjects')
            ->join('subjects', 'subjects.id', '=', 'exam_subjects.subject_id')
            ->where('exam_subjects.exam_id', $exam->id)
            ->select(
                'exam_subjects.id',
                'exam_subjects.exam_id',
                'exam_subjects.subject_id',
                'subjects.name as subject_name'
            )
            ->orderBy('subjects.name')
            ->get();

        $materialsBySubject = SourceMaterial::query()
            ->where('active', true)
            ->orderBy('title')
            ->get()
            ->groupBy('subject_id');

        $selectedByExamSubject = ExamSubjectSourceMaterial::query()
            ->whereIn('exam_subject_id', $examSubjects->pluck('id'))
            ->where('is_active', true)
            ->get()
            ->groupBy('exam_subject_id')
            ->map(fn ($items) => $items->pluck('source_material_id')->map(fn ($id) => (int) $id)->all())
            ->all();

        return view('admin.exam_source_materials.edit', compact(
            'exam',
            'examSubjects',
            'materialsBySubject',
            'selectedByExamSubject'
        ));
    }

    public function update(Request $request, Exam $exam)
    {
        $examSubjectIds = DB::table('exam_subjects')
            ->where('exam_id', $exam->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $request->validate([
            'materials' => ['nullable', 'array'],
            'materials.*' => ['nullable', 'array'],
            'materials.*.*' => ['integer', 'exists:source_materials,id'],
        ]);

        $materials = $request->input('materials', []);

        DB::transaction(function () use ($examSubjectIds, $materials) {
            ExamSubjectSourceMaterial::query()
                ->whereIn('exam_subject_id', $examSubjectIds)
                ->delete();

            foreach ($examSubjectIds as $examSubjectId) {
                $selected = array_values(array_unique(array_map('intval', $materials[$examSubjectId] ?? [])));

                foreach ($selected as $index => $sourceMaterialId) {
                    ExamSubjectSourceMaterial::create([
                        'exam_subject_id' => $examSubjectId,
                        'source_material_id' => $sourceMaterialId,
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.exams.source-materials.edit', $exam)
            ->with('success', 'Fontes/bibliografias do concurso atualizadas com sucesso.');
    }
}
